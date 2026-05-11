<?php

namespace App\Services;

use App\Models\Lecturer;
use App\Models\Student;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Symfony\Component\DomCrawler\Crawler;

/**
 * ImaalumScraperService
 *
 * Hybrid approach:
 *  - Uses the api.quddus.my wrapper API for iMaalum login + profile + schedule
 *    (returns clean JSON; avoids fragile HTML parsing of imaluum.iium.edu.my).
 *  - Resolves course lecturers to email addresses by matching against the
 *    seeded `lecturers` table. Optionally falls back to scraping the IIUM
 *    public staff directory at https://www.iium.edu.my/directory/ if a
 *    lecturer name has no match in the local registry.
 *
 * Important security notes:
 *  - Student password is used ONLY for the login call and is then discarded
 *    from memory. It is never persisted anywhere.
 *  - Scraped profile is cached for 30 minutes per student_id.
 *  - Any failure is logged and returns ['success' => false]. The caller MUST
 *    handle this gracefully and never block login.
 */
class ImaalumScraperService
{
    protected string $apiBase = 'https://api.quddus.my/api';
    protected string $directoryUrl = 'https://www.iium.edu.my/directory/';

    /**
     * Main entry point — called by ScrapeImaalumData job.
     *
     * @param  string $studentId
     * @param  string $password Plaintext password, used for login only then discarded.
     * @return array  ['success' => bool, 'student' => array|null, 'lecturers' => array]
     */
    public function syncStudent(string $studentId, string $password): array
    {
        $cacheKey = "imaalum_profile:{$studentId}";
        if ($cached = Cache::get($cacheKey)) {
            return $cached;
        }

        try {
            $token = $this->login($studentId, $password);
            // PASSWORD IS NOW UNSET — never stored
            unset($password);

            if (!$token) {
                return $this->fail('Login to iMaalum failed.');
            }

            $profile  = $this->fetchProfile($token);
            $schedule = $this->fetchSchedule($token);

            $studentRow = $this->upsertStudent($studentId, $profile);
            $lecturers  = $this->extractAndResolveLecturers($schedule);

            $result = [
                'success'   => true,
                'student'   => $studentRow ? $studentRow->only([
                    'student_id','first_name','last_name','email','kulliyyah',
                    'programme','year_of_study','mahallah','phone','imaalum_synced_at',
                ]) : null,
                'lecturers' => $lecturers,
            ];

            Cache::put($cacheKey, $result, now()->addMinutes(30));
            return $result;
        } catch (\Throwable $e) {
            Log::warning('iMaalum scrape failed', [
                'student_id' => $studentId,
                'error'      => $e->getMessage(),
            ]);
            return $this->fail($e->getMessage());
        }
    }

    protected function login(string $username, string $password): ?string
    {
        $resp = Http::timeout(20)->acceptJson()->post("{$this->apiBase}/auth/login", [
            'username' => $username,
            'password' => $password,
        ]);
        if (!$resp->successful()) return null;
        return $resp->json('data.token');
    }

    protected function fetchProfile(string $token): array
    {
        $resp = Http::timeout(20)->withToken($token)->acceptJson()->get("{$this->apiBase}/profile");
        return $resp->successful() ? (array) ($resp->json('data') ?? $resp->json()) : [];
    }

    protected function fetchSchedule(string $token): array
    {
        $resp = Http::timeout(20)->withToken($token)->acceptJson()->get("{$this->apiBase}/schedule");
        return $resp->successful() ? (array) ($resp->json('data') ?? $resp->json()) : [];
    }

    protected function upsertStudent(string $studentId, array $profile): ?Student
    {
        if (empty($profile)) return Student::find($studentId);

        // Normalize fields — quddus API returns nested data; we defensively pluck.
        $name   = (string) data_get($profile, 'name', data_get($profile, 'full_name', ''));
        $parts  = preg_split('/\s+/', trim($name), 2);
        $first  = $parts[0] ?? '';
        $last   = $parts[1] ?? '';

        $payload = array_filter([
            'first_name'        => $first ?: null,
            'last_name'         => $last ?: null,
            'email'             => data_get($profile, 'email') ?: null,
            'kulliyyah'         => data_get($profile, 'kulliyyah') ?: data_get($profile, 'faculty'),
            'programme'         => data_get($profile, 'programme') ?: data_get($profile, 'program'),
            'year_of_study'     => (string) data_get($profile, 'year', data_get($profile, 'year_of_study')) ?: null,
            'mahallah'          => data_get($profile, 'mahallah') ?: data_get($profile, 'hostel'),
            'phone'             => data_get($profile, 'phone') ?: null,
            'gender'            => data_get($profile, 'gender') ?: null,
            'nationality'       => data_get($profile, 'nationality') ?: null,
            'enrollment_status' => data_get($profile, 'status', 'Active'),
            'emergency_contact' => data_get($profile, 'emergency_contact') ?: null,
            'imaalum_synced_at' => now(),
        ], fn($v) => $v !== null && $v !== '');

        $student = Student::find($studentId);
        if ($student) {
            $student->fill($payload)->save();
            return $student;
        }
        // Don't create from scratch here — student must already exist via system registration.
        return null;
    }

    /**
     * From the schedule API extract unique lecturers, then resolve their
     * email addresses against the seeded `lecturers` table.
     * Returns the matched Lecturer rows.
     */
    protected function extractAndResolveLecturers(array $schedule): array
    {
        $names = [];
        // schedule may be a flat list of courses, each having `lecturer` or `instructor` key.
        $courses = data_get($schedule, 'courses', $schedule);
        if (is_array($courses)) {
            foreach ($courses as $c) {
                $lec = data_get($c, 'lecturer', data_get($c, 'instructor', data_get($c, 'lecturer_name')));
                if ($lec) $names[] = trim((string)$lec);
            }
        }
        $names = array_values(array_unique(array_filter($names)));

        $matched = [];
        foreach ($names as $name) {
            $lect = $this->matchLecturerByName($name);
            if ($lect) $matched[] = $lect->only(['lecturer_id','first_name','last_name','email','department']);
        }
        return $matched;
    }

    /**
     * Match a free-form lecturer name to a row in the seeded `lecturers` table.
     * Strategy:
     *   1. Try exact full-name (first + last) case-insensitive match.
     *   2. Try LIKE match on any name part.
     *   3. (Optional) Fallback to directory scrape — kept disabled by default
     *      to avoid network calls; enable by setting LECTURER_DIRECTORY_FALLBACK=true.
     */
    protected function matchLecturerByName(string $name): ?Lecturer
    {
        $clean = preg_replace('/\b(Dr\.?|Prof\.?|Assoc\.?|Asst\.?|Mr\.?|Mrs\.?|Ms\.?|Madam)\b/i', '', $name);
        $clean = trim(preg_replace('/\s+/', ' ', $clean));
        if ($clean === '') return null;

        $parts = explode(' ', $clean);
        $first = $parts[0];
        $last  = end($parts);

        $hit = Lecturer::where(function ($q) use ($first, $last) {
            $q->where(function ($qq) use ($first, $last) {
                $qq->where('first_name', $first)->where('last_name', $last);
            })->orWhere(function ($qq) use ($clean) {
                $qq->whereRaw("CONCAT(first_name,' ',last_name) LIKE ?", ["%$clean%"]);
            });
        })->first();

        if ($hit) return $hit;

        // Try a looser LIKE per part
        $hit = Lecturer::where('first_name', 'like', "%$first%")
            ->orWhere('last_name', 'like', "%$last%")
            ->first();
        if ($hit) return $hit;

        // Optional fallback to scraping iium.edu.my/directory
        if (env('LECTURER_DIRECTORY_FALLBACK', false)) {
            return $this->scrapeDirectoryForLecturer($clean);
        }
        return null;
    }

    /**
     * Optional directory fallback: scrape https://www.iium.edu.my/directory/
     * by name keyword. Inserts a new Lecturer row on first match.
     */
    protected function scrapeDirectoryForLecturer(string $name): ?Lecturer
    {
        try {
            $resp = Http::timeout(15)->get($this->directoryUrl, ['name' => $name, 'sort' => '01']);
            if (!$resp->successful()) return null;

            $crawler = new Crawler($resp->body());
            $found = null;
            $crawler->filter('.card-body')->each(function (Crawler $node) use (&$found, $name) {
                if ($found) return;
                $hName = $node->filter('h5.card-title')->count()
                    ? trim($node->filter('h5.card-title')->text()) : null;
                if (!$hName) return;
                $emailNode = $node->filter('.col-md-10');
                $email = $emailNode->count() ? trim($emailNode->first()->text()) : null;
                if (!$email) return;

                $parts = preg_split('/\s+/', $hName, 2);
                $found = Lecturer::firstOrCreate(
                    ['email' => $email],
                    [
                        'first_name' => $parts[0] ?? $hName,
                        'last_name'  => $parts[1] ?? '',
                        'department' => 'IIUM Directory',
                        'password'   => bcrypt(\Illuminate\Support\Str::random(16)),
                    ]
                );
            });
            return $found;
        } catch (\Throwable $e) {
            Log::info('Directory scrape failed', ['name' => $name, 'err' => $e->getMessage()]);
            return null;
        }
    }

    protected function fail(string $reason): array
    {
        return ['success' => false, 'reason' => $reason, 'student' => null, 'lecturers' => []];
    }
}
