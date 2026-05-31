<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\CrisisReport;
use App\Models\Admin;
use App\Http\Requests\VerifyCrisisRequest;
use Illuminate\Support\Facades\Auth;

$report = CrisisReport::where('report_status', '!=', 'verified')->first();

if (!$report) {
    echo "No report found to verify.\n";
    exit(1);
}

echo "Found Report ID: {$report->report_id}. Triggering verification...\n";

// 1. Mock Admin Authentication
$admin = Admin::where('active', true)->first();
if (!$admin) {
    echo "Error: No active admin found in database to simulate verification.\n";
    exit(1);
}
Auth::guard('admin')->login($admin);

// 2. Create the specific Request class
$request = new VerifyCrisisRequest();
$request->merge([
    'admin_remarks' => 'Testing Mailtrap Notification via PHP script',
    'donation_target' => 0
]);

try {
    $controller = app(\App\Http\Controllers\CrisisReportController::class);
    $controller->verify($request, $report);
    echo "SUCCESS: Verification logic executed for report {$report->report_id}. Please check Mailtrap inbox.\n";
} catch (\Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
    exit(1);
}
