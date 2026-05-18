<?php

namespace App\Services;

use App\Models\Lecturer;
use App\Models\Student;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

/**
 * ImaalumScraperService
 *
 * Wraps the GoMa'luum API (https://api.quddus.my) to:
 *   1. Authenticate a student using matric + IIUM password
 *   2. Pull their profile (name, kuliyyah, photo, gender, ...)
 *   3. Pull their schedule, take ONLY the current semester, and save:
 *        - the student row (insert on first login, update afterwards)
 *        - one student_courses row per course, linked to a lecturer
 *          from the local lecturers table (or null if unmatched)
 *
 * DESIGN NOTES
 *   - Password is used once for the API call then unset(). Never stored.
 *   - No signup form. The very first login inserts the student row
 *     automatically using data fetched from iMaalum.
 *   - Email isn't returned by the API, so we GUESS it from the name
 *     (firstnamelastname@student.iium.edu.my pattern) and set the
 *     `needs_email_confirmation` flag = true. The login flow will then
 *     send the student to a "confirm your email" page before the
 *     dashboard, ONCE — afterwards the flag stays false.
 *   - Schedule has many semesters; we take only the first one (current).
 *   - Unmatched lecturers are saved with lecturer_id = null + the raw
 *     lecturer name in `lecturer_name_raw`. They simply don't get
 *     emailed during crisis verification (no real address to send to).
 */
class ImaalumScraperService
{
    protected string $apiBase = 'https://api.quddus.my/api';

    /**
     * Full sync: login → profile → schedule → upsert student & courses.
     *
     * @return array{success: bool, reason?: string, student?: Student,
     *               lecturers?: array, semester?: ?string, courses?: array}
     */
    public function syncStudent(string $studentId, string $password): array
    {
        try {
            $token = $this->login($studentId, $password);
            unset($password); // discard credential immediately

            if (!$token) {
                return $this->fail('iMaalum login failed. Please check matric ID and password.');
            }

            $profile  = $this->fetchProfile($token);
            $schedule = $this->fetchSchedule($token);

            if (empty($profile)) {
                return $this->fail('Logged in but could not fetch your iMaalum profile.');
            }

            $student         = $this->upsertStudent($studentId, $profile);
            $currentSemester = $this->extractCurrentSemester($schedule);
            $this->persistStudentCourses($studentId, $currentSemester);

            $matchedLecturers = $this->resolveLecturers($currentSemester);

            // Cache the result for 30 minutes so a dashboard refresh
            // doesn't hammer the API.
            Cache::put('imaalum_profile:' . $studentId, [
                'student_id' => $studentId,
                'synced_at'  => now()->toIso8601String(),
            ], now()->addMinutes(30));

            return [
                'success'   => true,
                'student'   => $student,
                'lecturers' => $matchedLecturers,
                'semester'  => $currentSemester['session_name'] ?? null,
                'courses'   => $currentSemester['schedule'] ?? [],
            ];
        } catch (\Throwable $e) {
            Log::warning('iMaalum scrape failed', [
                'student_id' => $studentId,
                'error'      => $e->getMessage(),
            ]);
            return $this->fail('Sync error: ' . $e->getMessage());
        }
    }

    // ------------------------------------------------------------------
    // API CALLS
    // ------------------------------------------------------------------

    protected function login(string $username, string $password): ?string
    {
        $resp = Http::timeout(20)->acceptJson()->post($this->apiBase . '/auth/login', [
            'username' => $username,
            'password' => $password,
        ]);

        if (!$resp->successful()) {
            Log::info('quddus login non-200', [
                'username' => $username,
                'status'   => $resp->status(),
            ]);
            return null;
        }

        return $resp->json('data.token');
    }

    protected function fetchProfile(string $token): array
    {
        $resp = Http::timeout(20)->withToken($token)->acceptJson()
            ->get($this->apiBase . '/profile');
        if (!$resp->successful()) return [];
        return (array) ($resp->json('data') ?? []);
    }

    protected function fetchSchedule(string $token): array
    {
        $resp = Http::timeout(30)->withToken($token)->acceptJson()
            ->get($this->apiBase . '/schedule');
        if (!$resp->successful()) return [];
        return (array) ($resp->json('data') ?? []);
    }

    // ------------------------------------------------------------------
    // STUDENT UPSERT (auto INSERT on first login, UPDATE thereafter)
    // ------------------------------------------------------------------

    protected function upsertStudent(string $studentId, array $profile): Student
    {
        // Title-case the all-caps name from iMaalum
        $rawName   = (string) data_get($profile, 'name', '');
        $cleanName = $this->titleCase($rawName);
        $parts     = preg_split('/\s+/', trim($cleanName), 2);
        $first     = $parts[0] ?? '';
        $last      = $parts[1] ?? '';

        // "Level 4" → "4"
        $level       = (string) data_get($profile, 'level', '');
        $yearOfStudy = preg_match('/(\d+)/', $level, $m) ? $m[1] : null;

        // "20-AUG-02" → "2002-08-20"
        $dob = $this->parseImaalumDate((string) data_get($profile, 'birthday', ''));

        // Map "FEMALE"/"MALE" to your enum's "Female"/"Male"
        $genderRaw = strtoupper((string) data_get($profile, 'gender', ''));
        $gender    = $genderRaw === 'FEMALE' ? 'Female' : ($genderRaw === 'MALE' ? 'Male' : null);

        $existing = Student::find($studentId);

        // If we already have an email saved (student confirmed it before),
        // KEEP it. Don't overwrite with a re-guess.
        $email = $existing?->email ?: $this->guessStudentEmail($cleanName);

        // First-login students need to confirm the guessed email once.
        // Already-existing students don't.
        $needsConfirm = $existing
            ? (bool) ($existing->needs_email_confirmation)
            : true;

        $payload = [
            'first_name'               => $first ?: 'Unknown',
            'last_name'                => $last ?: '',
            'email'                    => $email,
            'kulliyyah'                => data_get($profile, 'kuliyyah'),   // API uses one L
            'year_of_study'            => $yearOfStudy,
            'gender'                   => $gender,
            'date_of_birth'            => $dob,
            'enrollment_status'        => 'Active',
            'image_url'                => data_get($profile, 'image_url'),
            'needs_email_confirmation' => $needsConfirm,
            'imaalum_synced_at'        => now(),
            'status'                   => 'active',
        ];

        if ($existing) {
            // UPDATE: don't touch the password (student authenticates via iMaalum)
            //
            // PRESERVE STUDENT EDITS:
            // Some fields are editable by the student in their profile page
            // (programme, year_of_study, mahallah, phone, emergency_contact).
            // iMaalum either doesn't return them, or returns them
            // inconsistently. Strategy: iMaalum only fills these fields
            // when they're currently EMPTY. Once a value exists (whether
            // student-typed or previously synced), it sticks until the
            // student edits it again. This avoids the surprise where a
            // student's freshly typed "Bachelor of Information Systems"
            // gets clobbered back to blank by the next sync.
            $studentEditableFields = [
                'year_of_study',
                'programme',
                'mahallah',
                'phone',
                'emergency_contact',
            ];
            foreach ($studentEditableFields as $f) {
                // Only carry through iMaalum's value if iMaalum has one
                // AND the existing student row is empty for that field.
                $imaalumHasValue = array_key_exists($f, $payload) && !empty($payload[$f]);
                $studentHasValue = !empty($existing->{$f});

                if ($studentHasValue) {
                    // Don't touch — student value wins.
                    unset($payload[$f]);
                } elseif (!$imaalumHasValue) {
                    // iMaalum has nothing either; drop key to avoid setting NULL.
                    unset($payload[$f]);
                }
                // else: student blank + iMaalum has value → use iMaalum.
            }

            $existing->fill($payload)->save();
            return $existing->refresh();
        }

        // INSERT: create the row with a random local password (unused — auth is via iMaalum).
        $payload['student_id'] = $studentId;
        $payload['password']   = Str::random(40); // hashed by the model cast

        return Student::create($payload);
    }

    // ------------------------------------------------------------------
    // SCHEDULE → CURRENT SEMESTER → student_courses
    // ------------------------------------------------------------------

    /**
     * The API returns an array of semesters (most recent first).
     * We always take the first one.
     */
    protected function extractCurrentSemester(array $schedule): array
    {
        if (empty($schedule)) return [];

        // The schedule may be an array of semester objects, or a single
        // object containing a "schedule" key. Handle both.
        $first = $schedule[0] ?? $schedule;
        return is_array($first) ? $first : [];
    }

    protected function persistStudentCourses(string $studentId, array $semester): void
    {
        $courses = data_get($semester, 'schedule', []);
        if (!is_array($courses) || empty($courses)) return;

        $sessionName = (string) data_get($semester, 'session_name', '');
        $seen = [];

        foreach ($courses as $course) {
            $courseCode = trim((string) data_get($course, 'course_code', ''));
            $lecturerNm = trim((string) data_get($course, 'lecturer', ''));

            if ($courseCode === '') continue;

            // De-dupe within the loop — same course on multiple days appears
            // as multiple rows in the API response.
            $dedupeKey = $courseCode . '|' . $sessionName;
            if (isset($seen[$dedupeKey])) continue;
            $seen[$dedupeKey] = true;

            $lecturer = $lecturerNm !== '' ? $this->matchLecturerByName($lecturerNm) : null;

            DB::table('student_courses')->updateOrInsert(
                [
                    'student_id'  => $studentId,
                    'course_code' => $courseCode,
                    'semester'    => $sessionName,
                ],
                [
                    'lecturer_id'       => $lecturer?->lecturer_id,
                    'course_name'       => (string) data_get($course, 'course_name', '') ?: null,
                    'section'           => (string) data_get($course, 'section', '') ?: null,
                    'lecturer_name_raw' => $lecturerNm ?: null,
                    'updated_at'        => now(),
                    'created_at'        => now(),
                ]
            );
        }
    }

    /**
     * Return the list of unique matched lecturers for the current semester
     * (for use in the dashboard / debug view).
     */
    protected function resolveLecturers(array $semester): array
    {
        $courses = data_get($semester, 'schedule', []);
        if (!is_array($courses)) return [];

        $names = [];
        foreach ($courses as $c) {
            $n = trim((string) data_get($c, 'lecturer', ''));
            if ($n !== '' && strtoupper($n) !== 'TUTOR') {
                $names[$n] = true;
            }
        }

        $matched = [];
        foreach (array_keys($names) as $name) {
            $lect = $this->matchLecturerByName($name);
            if ($lect) {
                $matched[] = $lect->only([
                    'lecturer_id', 'first_name', 'last_name', 'email', 'department',
                ]);
            }
        }
        return $matched;
    }

    /**
     * Name-matching strategy (in priority order):
     *   1. Exact full-name match (case-insensitive)
     *   2. First name + last token match
     *   3. Substring LIKE on the cleaned name
     *   4. Last-resort: first or last name fragment
     */
    protected function matchLecturerByName(string $name): ?Lecturer
    {
        // Strip honorifics: Dr., Prof., Assoc., etc.
        $clean = preg_replace(
            '/\b(Dr\.?|Prof\.?|Assoc\.?|Asst\.?|Mr\.?|Mrs\.?|Ms\.?|Madam|Ts\.?|Sir)\b/i',
            '',
            $name
        );
        $clean = trim(preg_replace('/\s+/', ' ', $clean));
        if ($clean === '' || strtoupper($clean) === 'TUTOR') {
            return null;
        }

        // Try exact match on CONCAT(first, ' ', last) first.
        $exact = Lecturer::whereRaw(
            'LOWER(CONCAT(first_name, " ", last_name)) = LOWER(?)',
            [$clean]
        )->first();
        if ($exact) return $exact;

        // Split into first + last tokens
        $tokens = preg_split('/\s+/', $clean);
        $firstToken = $tokens[0] ?? '';
        $lastToken  = end($tokens) ?: '';

        // Try first + last token match
        if ($firstToken && $lastToken && $firstToken !== $lastToken) {
            $hit = Lecturer::whereRaw('LOWER(first_name) = LOWER(?)', [$firstToken])
                ->whereRaw('LOWER(last_name) LIKE LOWER(?)', ['%' . $lastToken . '%'])
                ->first();
            if ($hit) return $hit;
        }

        // Substring match on the full concatenated name
        $hit = Lecturer::whereRaw(
            'LOWER(CONCAT(first_name, " ", last_name)) LIKE LOWER(?)',
            ['%' . $clean . '%']
        )->first();
        if ($hit) return $hit;

        // Last-resort: first-name-starts-with match (riskier)
        if ($firstToken) {
            $hit = Lecturer::whereRaw('LOWER(first_name) = LOWER(?)', [$firstToken])->first();
            if ($hit) return $hit;
        }

        return null;
    }

    // ------------------------------------------------------------------
    // HELPERS
    // ------------------------------------------------------------------

    /**
     * "NABILAH BINTI AHMAD NORDIN" → "Nabilah Binti Ahmad Nordin"
     */
    protected function titleCase(string $s): string
    {
        $s = trim($s);
        if ($s === '') return '';
        return mb_convert_case(mb_strtolower($s, 'UTF-8'), MB_CASE_TITLE, 'UTF-8');
    }

    /**
     * Guess the student email from the name.
     * Pattern for IIUM students is: firstname[middle...].lastname@student.iium.edu.my
     * For "Nabilah Binti Ahmad Nordin" we produce "nabilahahmad.nordin@student.iium.edu.my"
     * (concatenate everything before the last token, then ".", then last token).
     */
    protected function guessStudentEmail(string $cleanName): string
    {
        $cleanName = trim($cleanName);
        if ($cleanName === '') return '';

        // Remove common Malay name particles for cleaner email guess
        $stripped = preg_replace('/\b(bin|binti|bt\.?|b\.?|a\/?l|a\/?p)\b/i', '', $cleanName);
        $stripped = trim(preg_replace('/\s+/', ' ', $stripped));

        $tokens = preg_split('/\s+/', strtolower($stripped));
        if (count($tokens) === 0) return '';

        if (count($tokens) === 1) {
            return $tokens[0] . '@student.iium.edu.my';
        }

        $last  = array_pop($tokens);
        $front = implode('', $tokens);

        // sanitize: keep only a-z 0-9
        $front = preg_replace('/[^a-z0-9]/', '', $front);
        $last  = preg_replace('/[^a-z0-9]/', '', $last);

        return "{$front}.{$last}@student.iium.edu.my";
    }

    /**
     * "20-AUG-02" → "2002-08-20" (Carbon-compatible date string).
     * Returns null if it can't parse.
     */
    protected function parseImaalumDate(string $raw): ?string
    {
        if ($raw === '') return null;
        try {
            $dt = \DateTime::createFromFormat('d-M-y', $raw);
            if (!$dt) return null;
            // Two-digit years: 00-30 → 2000s, 31-99 → 1900s
            $y = (int) $dt->format('Y');
            if ($y > 2069) {
                $dt->modify('-100 years');
            }
            return $dt->format('Y-m-d');
        } catch (\Throwable) {
            return null;
        }
    }

    protected function fail(string $reason): array
    {
        return [
            'success'   => false,
            'reason'    => $reason,
            'student'   => null,
            'lecturers' => [],
            'semester'  => null,
            'courses'   => [],
        ];
    }
}
