<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Crisis Helper Controller
 *
 * Backend proxy for live disaster/news data. Includes:
 *  - MET Malaysia weather warnings
 *  - Google News RSS feed (Bahasa Malaysia)
 *  - Location extraction from news titles (Malaysian states/districts)
 *  - 5-min response cache
 *  - Graceful fallback to static links if APIs fail
 *
 * Endpoint:
 *  GET /student/crisis-helpers/disaster-context?type=flood
 */
class CrisisHelperController extends Controller
{
    /** Malaysian states + major cities for location extraction from news headlines */
    private const MALAYSIAN_LOCATIONS = [
        // States
        'Johor', 'Kedah', 'Kelantan', 'Melaka', 'Negeri Sembilan', 'Pahang', 'Penang', 'Pulau Pinang',
        'Perak', 'Perlis', 'Sabah', 'Sarawak', 'Selangor', 'Terengganu', 'Putrajaya',
        'Kuala Lumpur', 'Labuan',
        // Major cities/districts commonly in news
        'Shah Alam', 'Petaling Jaya', 'Klang', 'Subang Jaya', 'Cheras', 'Ampang',
        'Johor Bahru', 'Iskandar Puteri', 'Muar', 'Batu Pahat',
        'Ipoh', 'Taiping', 'Teluk Intan',
        'Kuantan', 'Temerloh', 'Cameron Highlands', 'Genting Highlands',
        'Kota Kinabalu', 'Sandakan', 'Tawau',
        'Kuching', 'Miri', 'Sibu', 'Bintulu',
        'Kota Bharu', 'Pasir Mas', 'Tumpat',
        'Kuala Terengganu', 'Dungun', 'Kemaman',
        'Alor Setar', 'Sungai Petani', 'Langkawi',
        'Seremban', 'Port Dickson',
        'Kangar', 'Arau',
        'George Town', 'Butterworth', 'Bukit Mertajam',
        // IIUM-relevant areas (students would be here)
        'Gombak', 'Gambang', 'Pagoh', 'Kuantan',
    ];

    /**
     * Aggregated endpoint — single fetch returns everything the wizard needs.
     */
    public function disasterContext(Request $request): JsonResponse
    {
        $type = $request->query('type', 'flood');
        $type = in_array($type, ['flood', 'landslide', 'fire', 'storm', 'haze', 'earthquake'], true)
            ? $type : 'flood';

        $payload = Cache::remember("crisis-context-{$type}", now()->addMinutes(5), function () use ($type) {
            $warnings = $this->fetchMetWarnings($type);
            $news     = $this->fetchGoogleNews($type);

            return [
                'warnings'    => $warnings,
                'news'        => $news,
                'static_links' => $this->getStaticLinks($type),
                'fetched_at'  => now()->toIso8601String(),
                'has_live'    => !empty($warnings) || !empty($news),
            ];
        });

        return response()->json($payload);
    }

    /** Always-available links — shown regardless of whether live APIs work. */
    private function getStaticLinks(string $type): array
    {
        $base = [
            ['label' => 'NADMA Portal',          'url' => 'https://www.nadma.gov.my/',           'icon' => 'building'],
            ['label' => 'MET Malaysia Warnings', 'url' => 'https://www.met.gov.my/',             'icon' => 'cloud'],
        ];

        $typeSpecific = match ($type) {
            'flood' => [
                ['label' => 'JKM Bantuan Banjir', 'url' => 'https://bencanaalam.jkm.gov.my/', 'icon' => 'house'],
                ['label' => 'Public Info Banjir', 'url' => 'https://publicinfobanjir.water.gov.my/', 'icon' => 'droplet'],
            ],
            'landslide' => [
                ['label' => 'JMG (Geology Dept)', 'url' => 'https://www.jmg.gov.my/', 'icon' => 'mountain'],
            ],
            'fire' => [
                ['label' => 'Bomba (Fire Dept)',  'url' => 'https://www.bomba.gov.my/', 'icon' => 'fire'],
            ],
            'haze' => [
                ['label' => 'APIMS Air Quality',  'url' => 'https://apims.doe.gov.my/', 'icon' => 'wind'],
            ],
            'earthquake' => [
                ['label' => 'MetMalaysia Seismic', 'url' => 'https://www.met.gov.my/data/gempa/', 'icon' => 'activity'],
            ],
            default => [],
        };

        return array_merge($typeSpecific, $base);
    }

    /**
     * MET Malaysia Severe Weather Warnings
     */
    private function fetchMetWarnings(string $disasterType): array
    {
        try {
            $response = Http::timeout(6)
                ->retry(1, 200)
                ->withHeaders(['Accept' => 'application/json'])
                ->get('https://api.met.gov.my/v2.1/data', [
                    'datasetid'      => 'FORECAST',
                    'datacategoryid' => 'GENERAL:WARNING',
                    'locationid'     => 'COUNTRY:MY',
                ]);

            if (!$response->successful()) {
                Log::info('MET API non-success', ['status' => $response->status()]);
                return [];
            }

            $data = $response->json('results', []);
            if (!is_array($data) || empty($data)) return [];

            $relevantKeywords = match ($disasterType) {
                'flood'      => ['rain', 'hujan', 'banjir', 'flood'],
                'landslide'  => ['rain', 'hujan', 'landslide', 'tanah runtuh'],
                'fire'       => ['fire', 'kebakaran', 'haze', 'dry'],
                'storm'      => ['storm', 'thunderstorm', 'ribut', 'wind', 'angin'],
                'haze'       => ['haze', 'jerebu', 'air quality'],
                'earthquake' => ['earthquake', 'gempa', 'tremor'],
                default      => [],
            };

            $warnings = collect($data)
                ->filter(function ($w) use ($relevantKeywords) {
                    $title = strtolower($w['title'] ?? '');
                    foreach ($relevantKeywords as $kw) {
                        if (str_contains($title, $kw)) return true;
                    }
                    return false;
                })
                ->take(3)
                ->map(fn ($w) => [
                    'title'      => $w['title'] ?? 'Weather warning',
                    'severity'   => $w['attributes']['severity'] ?? 'moderate',
                    'area'       => $w['attributes']['locations'] ?? null,
                    'valid_from' => $w['attributes']['valid_from'] ?? null,
                    'valid_to'   => $w['attributes']['valid_to'] ?? null,
                ])
                ->values()
                ->all();

            return $warnings;
        } catch (\Throwable $e) {
            Log::warning('MET API call failed', ['error' => $e->getMessage()]);
            return [];
        }
    }

    /**
     * Google News RSS feed (Malaysia, Bahasa Malaysia, last 3 days)
     * Extracts location from each headline.
     */
    private function fetchGoogleNews(string $disasterType): array
    {
        $query = match ($disasterType) {
            'flood'      => 'banjir malaysia',
            'landslide'  => 'tanah runtuh malaysia',
            'fire'       => 'kebakaran malaysia',
            'storm'      => 'ribut malaysia',
            'haze'       => 'jerebu malaysia',
            'earthquake' => 'gempa malaysia',
            default      => 'bencana malaysia',
        };

        try {
            $url = 'https://news.google.com/rss/search?' . http_build_query([
                'q'    => $query . ' when:3d',
                'hl'   => 'ms-MY',
                'gl'   => 'MY',
                'ceid' => 'MY:ms',
            ]);

            $response = Http::timeout(6)->retry(1, 200)->get($url);
            if (!$response->successful()) {
                Log::info('News RSS non-success', ['status' => $response->status()]);
                return [];
            }

            $xml = @simplexml_load_string($response->body(), 'SimpleXMLElement', LIBXML_NOCDATA);
            if (!$xml || !isset($xml->channel->item)) return [];

            $items = [];
            foreach ($xml->channel->item as $item) {
                $title   = (string) $item->title;
                $link    = (string) $item->link;
                $pubDate = (string) $item->pubDate;
                $source  = (string) ($item->source ?? 'News');

                $cleanTitle = preg_replace('/\s*-\s*[^-]+$/u', '', $title) ?: $title;
                $location   = $this->extractLocation($cleanTitle);

                $items[] = [
                    'title'     => $cleanTitle,
                    'link'      => $link,
                    'source'    => $source,
                    'location'  => $location, // null if no location found
                    'published' => $pubDate
                        ? \Carbon\Carbon::parse($pubDate)->diffForHumans()
                        : null,
                ];

                if (count($items) >= 5) break;
            }

            return $items;
        } catch (\Throwable $e) {
            Log::warning('Google News RSS failed', ['error' => $e->getMessage()]);
            return [];
        }
    }

    /**
     * Pattern-match a news headline against known Malaysian state/city names.
     * Returns the matched location string, or null.
     */
    private function extractLocation(string $title): ?string
    {
        // Sort by length descending so longer matches win (e.g. "Kuala Terengganu" over "Kuala")
        $locations = self::MALAYSIAN_LOCATIONS;
        usort($locations, fn($a, $b) => strlen($b) - strlen($a));

        foreach ($locations as $loc) {
            if (stripos($title, $loc) !== false) {
                return $loc;
            }
        }
        return null;
    }
}
