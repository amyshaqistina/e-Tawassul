<?php

namespace App\Console\Commands;

use App\Models\Lecturer;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Symfony\Component\DomCrawler\Crawler;

/**
 * SyncKictLecturers
 *
 * Scrapes the IIUM staff directory and populates the `lecturers` table.
 * Supports ONE or MULTIPLE kulliyyah codes (kcdiom) in a single run.
 *
 * Usage:
 *   php artisan lecturers:sync-kict                         # default = KICT (363)
 *   php artisan lecturers:sync-kict --dry-run               # preview, no DB writes
 *   php artisan lecturers:sync-kict --kcdiom=201            # one kulliyyah
 *   php artisan lecturers:sync-kict --kcdiom=201,323,367    # multiple, comma-separated
 *
 * Known kcdiom codes:
 *   201 = CELPAD (Languages)
 *   320 = KOED (Education)
 *   323 = AIKOL (Laws)
 *   330 = KOE (Engineering)
 *   363 = KICT (Information & Communication Technology)
 *   367 = AHAS KIRKHS (Islamic Revealed Knowledge & Human Sciences)
 *   368 = KENMS (Economics & Management Sciences)
 *   625 = CCC (Credited Co-Curricular Centre — percussion, Usrah, leadership courses)
 *
 * Safe to re-run. Lecturers are matched by email (unique). Existing rows
 * are UPDATED; new rows are CREATED; no duplicates.
 */
class SyncKictLecturers extends Command
{
    protected $signature = 'lecturers:sync-kict
                            {--kcdiom=363 : Single code or comma-separated list (e.g. 201,323,367)}
                            {--dry-run : Show what would happen without writing to the database}';

    protected $description = 'Scrape IIUM staff directory and populate the lecturers table (one or many kulliyyahs)';

    protected string $baseUrl = 'https://www.iium.edu.my/directory/';

    /** Friendly labels printed in the summary header. */
    protected array $kcdiomLabels = [
        '201' => 'CELPAD',
        '320' => 'KOED',
        '323' => 'AIKOL',
        '330' => 'KOE',
        '363' => 'KICT',
        '367' => 'AHAS KIRKHS',
        '368' => 'KENMS',
        '625' => 'CCC',
    ];

    public function handle(): int
    {
        $codes  = $this->parseCodes($this->option('kcdiom'));
        $dryRun = (bool) $this->option('dry-run');

        if (empty($codes)) {
            $this->error('No valid kcdiom codes provided.');
            return self::FAILURE;
        }

        $this->info('Will sync these kulliyyahs:');
        foreach ($codes as $code) {
            $label = $this->kcdiomLabels[$code] ?? 'UNKNOWN';
            $this->line("  - {$code} ({$label})");
        }
        if ($dryRun) {
            $this->warn('DRY RUN MODE — nothing will be written to the database.');
        }
        $this->newLine();

        $grandTotals = [
            'seen'    => 0,
            'created' => 0,
            'updated' => 0,
            'skipped' => 0,
        ];

        foreach ($codes as $code) {
            $label = $this->kcdiomLabels[$code] ?? "kcdiom={$code}";
            $this->info("=== Syncing {$label} (kcdiom={$code}) ===");

            $stats = $this->syncOne($code, $dryRun);

            $this->newLine();
            $this->line(sprintf(
                'Subtotal for %s — seen: %d, created: %d, updated: %d, skipped: %d',
                $label,
                $stats['seen'],
                $stats['created'],
                $stats['updated'],
                $stats['skipped'],
            ));
            $this->newLine();

            foreach ($grandTotals as $k => $_) {
                $grandTotals[$k] += $stats[$k];
            }
        }

        $this->info('====================================');
        $this->info('All kulliyyahs synced.');
        $this->table(
            ['Total seen', 'Created', 'Updated', 'Skipped (no email)'],
            [[
                $grandTotals['seen'],
                $grandTotals['created'],
                $grandTotals['updated'],
                $grandTotals['skipped'],
            ]]
        );

        return self::SUCCESS;
    }

    /** Parse "201,323,367" or "363" into a clean array of code strings. */
    protected function parseCodes(string $raw): array
    {
        $parts = array_map('trim', explode(',', $raw));
        $codes = array_filter($parts, fn ($p) => $p !== '' && ctype_digit($p));
        return array_values(array_unique($codes));
    }

    /**
     * Sync one kulliyyah end-to-end (all pages).
     */
    protected function syncOne(string $kcdiom, bool $dryRun): array
    {
        $page    = 1;
        $seen    = 0;
        $created = 0;
        $updated = 0;
        $skipped = 0;

        while (true) {
            $this->line("Fetching page {$page}...");

            $html = $this->fetchPage($kcdiom, $page);
            if (!$html) {
                $this->error("Failed to fetch page {$page}. Stopping this kulliyyah.");
                break;
            }

            $lecturers = $this->parseLecturers($html);
            if (empty($lecturers)) {
                $this->line("No more lecturers on page {$page}. Done.");
                break;
            }

            $this->line('Found ' . count($lecturers) . " lecturer(s) on page {$page}.");

            foreach ($lecturers as $data) {
                $seen++;

                if (empty($data['email'])) {
                    $this->warn("  - SKIP (no email): {$data['raw_name']}");
                    $skipped++;
                    continue;
                }

                if ($dryRun) {
                    $this->line("  - WOULD SAVE: {$data['first_name']} {$data['last_name']} <{$data['email']}>");
                    $created++;
                    continue;
                }

                $existing = Lecturer::where('email', $data['email'])->first();
                if ($existing) {
                    $existing->fill([
                        'first_name' => $data['first_name'],
                        'last_name'  => $data['last_name'],
                        'department' => $data['department'],
                    ])->save();
                    $this->line("  - UPDATED: {$data['first_name']} {$data['last_name']} <{$data['email']}>");
                    $updated++;
                } else {
                    Lecturer::create([
                        'first_name' => $data['first_name'],
                        'last_name'  => $data['last_name'],
                        'email'      => $data['email'],
                        'department' => $data['department'],
                        'password'   => bcrypt(Str::random(32)),
                    ]);
                    $this->line("  - CREATED: {$data['first_name']} {$data['last_name']} <{$data['email']}>");
                    $created++;
                }
            }

            $page++;
            if ($page > 30) {
                $this->warn('Reached 30 page safety limit for this kulliyyah. Stopping.');
                break;
            }

            usleep(500_000); // 0.5s polite delay between requests
        }

        return compact('seen', 'created', 'updated', 'skipped');
    }

    protected function fetchPage(string $kcdiom, int $page): ?string
    {
        try {
            $resp = Http::timeout(20)
                ->withHeaders(['User-Agent' => 'Mozilla/5.0 (compatible; e-Tawassul/1.0)'])
                ->get($this->baseUrl, [
                    'kcdiom' => $kcdiom,
                    'sort'   => '01',
                    'page'   => $page,
                ]);

            if (!$resp->successful()) {
                $this->error("HTTP {$resp->status()} on page {$page}");
                return null;
            }
            return $resp->body();
        } catch (\Throwable $e) {
            Log::warning('IIUM directory fetch failed', [
                'kcdiom' => $kcdiom,
                'page'   => $page,
                'error'  => $e->getMessage(),
            ]);
            $this->error("Exception on page {$page}: {$e->getMessage()}");
            return null;
        }
    }

    protected function parseLecturers(string $html): array
    {
        $crawler = new Crawler($html);
        $results = [];

        $crawler->filter('div.card.iiumcard')->each(function (Crawler $node) use (&$results) {
            $rawName = $node->filter('h5.card-title')->count()
                ? trim($node->filter('h5.card-title')->text())
                : null;
            if (!$rawName) return;

            $cleanName = $rawName;
            if (preg_match('/^(.+?)\s*\(.+?\)\s*$/u', $rawName, $m)) {
                $cleanName = trim($m[1]);
            }

            $parts     = preg_split('/\s+/', $cleanName, 2);
            $firstName = $parts[0] ?? $cleanName;
            $lastName  = $parts[1] ?? '';

            // Email — first .col-md-10 inside the card that looks like a valid email
            $email = null;
            $node->filter('.col-md-10')->each(function (Crawler $n) use (&$email) {
                if ($email) return;
                $c = trim($n->text());
                if (filter_var($c, FILTER_VALIDATE_EMAIL)) {
                    $email = strtolower($c);
                }
            });

            // Department text — line containing "KULLIYYAH" (or "CENTRE"/"INSTITUTE")
            $department = null;
            if ($node->filter('p.card-text')->count()) {
                $cardText = $node->filter('p.card-text')->text();
                foreach (preg_split('/\r?\n/', $cardText) as $line) {
                    $line = trim($line);
                    if ($line === '') continue;

                    if (
                        stripos($line, 'KULLIYYAH') !== false ||
                        stripos($line, 'CENTRE') !== false ||
                        stripos($line, 'INSTITUTE') !== false ||
                        stripos($line, 'OFFICE') !== false ||
                        stripos($line, 'DIVISION') !== false ||
                        stripos($line, 'DEPARTMENT') !== false
                    ) {
                        $department = $line;
                        break;
                    }
                }
                if (!$department) {
                    // fallback: first non-empty line
                    foreach (preg_split('/\r?\n/', $cardText) as $line) {
                        $line = trim($line);
                        if ($line !== '') { $department = $line; break; }
                    }
                }
            }

            $results[] = [
                'raw_name'   => $rawName,
                'first_name' => $firstName,
                'last_name'  => $lastName,
                'email'      => $email,
                'department' => $department ?: 'UNKNOWN',
            ];
        });

        return $results;
    }
}
