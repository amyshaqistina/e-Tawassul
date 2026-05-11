<?php

namespace App\Jobs;

use App\Services\ImaalumScraperService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class ScrapeImaalumData implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 1;
    public int $timeout = 60;

    public function __construct(public string $studentId, public string $password) {}

    public function handle(ImaalumScraperService $scraper): void
    {
        $result = $scraper->syncStudent($this->studentId, $this->password);
        // Discard password from job payload context.
        $this->password = '';
        Log::info('ScrapeImaalumData finished', [
            'student_id' => $this->studentId,
            'success'    => $result['success'] ?? false,
        ]);
    }

    public function failed(\Throwable $e): void
    {
        Log::warning('ScrapeImaalumData failed', [
            'student_id' => $this->studentId,
            'error'      => $e->getMessage(),
        ]);
    }
}
