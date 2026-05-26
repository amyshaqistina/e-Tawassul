<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\AdminCrisisController;
use App\Http\Controllers\AdminStudentController;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\BlockchainController;
use App\Http\Controllers\ConfirmEmailController;
use App\Http\Controllers\CrisisController;
use App\Http\Controllers\CrisisHelperController;
use App\Http\Controllers\CrisisReportController;
use App\Http\Controllers\DeathConfirmationController;
use App\Http\Controllers\DonationController;
use App\Http\Controllers\LDMSController;
use App\Http\Controllers\LecturerController;
use App\Http\Controllers\NOKController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\PdfExportController;
use App\Http\Controllers\StudentController;
use App\Http\Controllers\StudentProfileController;
use App\Http\Controllers\NokCrisisController;
use App\Http\Controllers\ChatbotController;
use Illuminate\Support\Facades\Route;

Route::get('/test-imaalum', function () {
    $base = env('IMAALUM_API_BASE');
    $response = Http::get($base . '/search', [
        'q' => 'ahmad'
    ]);
    dd([
        'status' => $response->status(),
        'json' => $response->json(),
        'raw' => $response->body(),
    ]);
});

/*
|--------------------------------------------------------------------------
| Chatbot AI
|--------------------------------------------------------------------------
*/
Route::post('/chatbot/ask', [ChatbotController::class, 'ask']);

/*
|--------------------------------------------------------------------------
| Public routes
|--------------------------------------------------------------------------
*/
Route::get('/', [CrisisController::class, 'index'])->name('home');
Route::get('/dashboard', [CrisisController::class, 'index'])->name('public.dashboard');
Route::get('/crisis/{crisis}', [CrisisController::class, 'show'])->name('crisis.show');
Route::get('/donate/{crisis}', [DonationController::class, 'create'])->name('donate.create');
Route::post('/donate/{crisis}', [DonationController::class, 'store'])->name('donate.store');
Route::get('/crisis/{crisis}/progress', [DonationController::class, 'progress'])->name('donate.progress');

Route::get('/pdf/crisis/{crisis}', [PdfExportController::class, 'crisisReceipt'])->name('pdf.crisis');
Route::get('/pdf/donation/{donation}', [DonationController::class, 'donationReceipt'])->name('pdf.donation');

/*
|--------------------------------------------------------------------------
| Authentication
|--------------------------------------------------------------------------
*/
Route::get('/login',  [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->name('login.post');
Route::post('/logout',[AuthController::class, 'logout'])->name('logout');

Route::get('/twofactor',         [AuthController::class, 'showTwoFactor'])->name('nok.twofactor.show');
Route::post('/twofactor',        [AuthController::class, 'verifyTwoFactor'])->name('nok.twofactor.verify');
Route::post('/twofactor/resend', [AuthController::class, 'resendOtp'])->name('nok.twofactor.resend');

/*
|--------------------------------------------------------------------------
| Student email confirmation gate
|--------------------------------------------------------------------------
*/
Route::middleware('role:student')->prefix('student')->name('student.')->group(function () {
    Route::get('/confirm-email',  [ConfirmEmailController::class, 'show'])->name('confirm-email.show');
    Route::post('/confirm-email', [ConfirmEmailController::class, 'store'])->name('confirm-email.store');
});

/*
|--------------------------------------------------------------------------
| Student area (REQUIRES email confirmation)
|--------------------------------------------------------------------------
*/
Route::middleware(['role:student', 'email.confirmed'])->prefix('student')->name('student.')->group(function () {
    Route::get('/dashboard', [StudentController::class, 'dashboard'])->name('dashboard');
    Route::get('/profile',   [StudentController::class, 'profile'])->name('profile');

    Route::patch('/profile', [StudentProfileController::class, 'updateProfile'])->name('profile.update');

    Route::post  ('/kin',                       [StudentProfileController::class, 'storeKin'])      ->name('kin.store');
    Route::patch ('/kin/{kin}',                 [StudentProfileController::class, 'updateKin'])     ->name('kin.update');
    Route::delete('/kin/{kin}',                 [StudentProfileController::class, 'destroyKin'])    ->name('kin.destroy');
    Route::patch ('/kin/{kin}/make-primary',    [StudentProfileController::class, 'makePrimaryKin'])->name('kin.primary');

    Route::patch ('/bank',     [StudentProfileController::class, 'updateBank'])->name('bank.update');
    Route::post  ('/qr',       [StudentProfileController::class, 'uploadQr']) ->name('qr.upload');
    Route::delete('/qr',       [StudentProfileController::class, 'deleteQr']) ->name('qr.delete');

    Route::get('/crisis/create', [CrisisReportController::class, 'create'])->name('crisis.create');
    Route::post('/crisis',       [CrisisReportController::class, 'store'])->name('crisis.store');
    Route::get('/crisis/{report}', [CrisisReportController::class, 'show'])->name('crisis.show');
    Route::get('/crisis/{report}/evidence/{index}', [CrisisReportController::class, 'downloadEvidenceStudent'])->name('crisis.evidence.download');

    // Report editing (pending + rejected only — verified are blockchain-locked)
    Route::get('/crisis/{report}/edit',  [CrisisReportController::class, 'edit'])->name('crisis.edit');
    Route::patch('/crisis/{report}',     [CrisisReportController::class, 'update'])->name('crisis.update');
    Route::delete('/crisis/{report}',    [CrisisReportController::class, 'destroy'])->name('crisis.destroy');

    // My Reports — full listing of student's submitted reports
    Route::get('/reports', [CrisisReportController::class, 'myReports'])->name('reports.index');

    Route::get('/crisis-helpers/disaster-context', [CrisisHelperController::class, 'disasterContext'])
        ->name('crisis.helpers.disaster-context');

    Route::resource('ldms', LDMSController::class);
    Route::get('/ldms/{ldms}/download/{filename}', [LDMSController::class, 'studentDownload'])->name('ldms.download');
});

/*
|--------------------------------------------------------------------------
| Admin area
|--------------------------------------------------------------------------
*/
Route::middleware('role:admin')->prefix('admin')->name('admin.')->group(function () {
    Route::get('/dashboard', [AdminController::class, 'dashboard'])->name('dashboard');
    Route::get('/students',  [AdminController::class, 'students'])->name('students.index');
    Route::get('/students/{student}', [AdminStudentController::class, 'show'])->name('students.show');
    Route::post('/students/{student}/kin', [AdminStudentController::class, 'storeKin'])->name('students.kin.store');

    Route::get('/crisis',                                  [AdminCrisisController::class, 'index'])->name('crisis.index');
    Route::get('/crisis/{report}',                         [CrisisReportController::class, 'adminShow'])->name('crisis.show');
    Route::get('/crisis/{report}/evidence/{index}',        [CrisisReportController::class, 'downloadEvidence'])->name('crisis.evidence.download');
    Route::post('/crisis/{report}/verify',                 [CrisisReportController::class, 'verify'])->name('crisis.verify');
    Route::post('/crisis/{report}/reject',                 [CrisisReportController::class, 'reject'])->name('crisis.reject');
// Donation control (added 2026-05-24) — open/close & set cap per crisis
    Route::post('/crisis/{crisis}/donation-cap',           [AdminCrisisController::class, 'updateDonationCap'])->name('crisis.donation-cap');
    Route::post('/crisis/{crisis}/toggle-donation',        [AdminCrisisController::class, 'toggleDonation'])->name('crisis.toggle-donation');

    Route::get('/death',                                  [DeathConfirmationController::class, 'adminIndex'])->name('death.index');
    Route::get('/death/{confirmation}',                   [DeathConfirmationController::class, 'adminShow'])->name('death.show');
    Route::get('/death/{confirmation}/document',          [DeathConfirmationController::class, 'downloadDocument'])->name('death.document.download');
    Route::post('/death/{confirmation}/verify',           [DeathConfirmationController::class, 'verify'])->name('death.verify');

    Route::get('/ldms',                 [LDMSController::class, 'adminIndex'])->name('ldms.index');
    Route::get('/ldms/{ldms}',          [LDMSController::class, 'adminShow'])->name('ldms.show');
    Route::post('/ldms/{ldms}/trigger', [LDMSController::class, 'trigger'])->name('ldms.trigger');

    Route::get('/blockchain',         [BlockchainController::class, 'index'])->name('blockchain.index');
    Route::post('/blockchain/verify', [BlockchainController::class, 'verify'])->name('blockchain.verify');

    Route::get('/donations',         [AdminController::class, 'donations'])->name('donations.index');
    Route::get('/donations/create',  [DonationController::class, 'adminCreate'])->name('donations.create');
    Route::post('/donations',        [DonationController::class, 'adminStore'])->name('donations.store');



    Route::get('/pdf/audit', [BlockchainController::class, 'pdfAuditLog'])->name('pdf.audit');
});

/*
|--------------------------------------------------------------------------
| NOK area
|--------------------------------------------------------------------------
*/
Route::middleware(['role:nok', 'twofactor'])->prefix('nok')->name('nok.')->group(function () {
Route::get('/dashboard', [NOKController::class, 'dashboard'])->name('dashboard');

    Route::get('/crisis/create', [NokCrisisController::class, 'create'])->name('crisis.create');
    Route::post('/crisis', [NokCrisisController::class, 'store'])->name('crisis.store');
    Route::get('/crisis/{report}', [NokCrisisController::class, 'show'])->name('crisis.show');
    Route::get('/crisis/{report}/edit',  [NokCrisisController::class, 'edit'])->name('crisis.edit');
    Route::patch('/crisis/{report}',     [NokCrisisController::class, 'update'])->name('crisis.update');
    Route::delete('/crisis/{report}',    [NokCrisisController::class, 'destroy'])->name('crisis.destroy');
    Route::get('/crisis-helpers/disaster-context', [\App\Http\Controllers\CrisisHelperController::class, 'disasterContext'])->name('crisis.helpers.disaster-context');
    Route::get('/death/create', [DeathConfirmationController::class, 'create'])->name('death.create');
    Route::post('/death',       [DeathConfirmationController::class, 'store'])->name('death.store');
    Route::get('/death/{confirmation}', [DeathConfirmationController::class, 'nokShow'])->name('death.show');
    // Death confirmation editing (pending + rejected only)
    Route::get('/death/{confirmation}/edit',  [DeathConfirmationController::class, 'nokEdit'])->name('death.edit');
    Route::patch('/death/{confirmation}',     [DeathConfirmationController::class, 'nokUpdate'])->name('death.update');

    // My submissions — combined view of NOK's crisis reports & death confirmations
    Route::get('/submissions', [NOKController::class, 'mySubmissions'])->name('submissions.index');

    Route::get('/ldms/{ldms}',  [LDMSController::class, 'nokShow'])->name('ldms.show');
    Route::get('/ldms/{ldms}/download/{filename}', [LDMSController::class, 'nokDownload'])->name('ldms.download');
});

/*
|--------------------------------------------------------------------------
| Lecturer area
|--------------------------------------------------------------------------
*/
Route::middleware('role:lecturer')->prefix('lecturer')->name('lecturer.')->group(function () {
    Route::get('/dashboard', [LecturerController::class, 'dashboard'])->name('dashboard');
});

/*
|--------------------------------------------------------------------------
| Notifications
|--------------------------------------------------------------------------
*/
Route::get('/notifications',              [NotificationController::class, 'index'])->name('notifications.index');
Route::post('/notifications/{id}/read',   [NotificationController::class, 'markAsRead'])->name('notifications.read');
Route::get('/notifications/unread-count', [NotificationController::class, 'unreadCount'])->name('notifications.unread');
