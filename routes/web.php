<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\BlockchainController;
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
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Public routes
|--------------------------------------------------------------------------
*/
Route::get('/', [CrisisController::class, 'index'])->name('home');
Route::get('/crisis/{crisis}', [CrisisController::class, 'show'])->name('crisis.show');
Route::get('/donate/{crisis}', [DonationController::class, 'create'])->name('donate.create');
Route::post('/donate/{crisis}', [DonationController::class, 'store'])->name('donate.store');
Route::get('/crisis/{crisis}/progress', [DonationController::class, 'progress'])->name('donate.progress');

Route::get('/pdf/crisis/{crisis}', [PdfExportController::class, 'crisisReceipt'])->name('pdf.crisis');
Route::get('/pdf/donation/{donation}', [PdfExportController::class, 'donationReceipt'])->name('pdf.donation');

/*
|--------------------------------------------------------------------------
| Authentication
|--------------------------------------------------------------------------
*/
Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->name('login.post');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

Route::get('/twofactor', [AuthController::class, 'showTwoFactor'])->name('nok.twofactor.show');
Route::post('/twofactor', [AuthController::class, 'verifyTwoFactor'])->name('nok.twofactor.verify');
Route::post('/twofactor/resend', [AuthController::class, 'resendOtp'])->name('nok.twofactor.resend');

/*
|--------------------------------------------------------------------------
| Student area
|--------------------------------------------------------------------------
*/
Route::middleware('role:student')->prefix('student')->name('student.')->group(function () {
    Route::get('/dashboard', [StudentController::class, 'dashboard'])->name('dashboard');
    Route::get('/profile', [StudentController::class, 'profile'])->name('profile');

    Route::get('/crisis/create', [CrisisReportController::class, 'create'])->name('crisis.create');
    Route::post('/crisis', [CrisisReportController::class, 'store'])->name('crisis.store');
    Route::get('/crisis/{report}', [CrisisReportController::class, 'show'])->name('crisis.show');

    // Live disaster helper endpoints (used by the crisis wizard JS)
    Route::get('/crisis-helpers/disaster-context', [CrisisHelperController::class, 'disasterContext'])
        ->name('crisis.helpers.disaster-context');

    Route::resource('ldms', LDMSController::class)->except(['show']);
});

/*
|--------------------------------------------------------------------------
| Admin area
|--------------------------------------------------------------------------
*/
Route::middleware('role:admin')->prefix('admin')->name('admin.')->group(function () {
    Route::get('/dashboard', [AdminController::class, 'dashboard'])->name('dashboard');
    Route::get('/students', [AdminController::class, 'students'])->name('students.index');

    Route::get('/crisis', [CrisisReportController::class, 'adminIndex'])->name('crisis.index');
    Route::get('/crisis/{report}', [CrisisReportController::class, 'adminShow'])->name('crisis.show');
    Route::post('/crisis/{report}/verify', [CrisisReportController::class, 'verify'])->name('crisis.verify');
    Route::post('/crisis/{report}/reject', [CrisisReportController::class, 'reject'])->name('crisis.reject');

    Route::get('/death', [DeathConfirmationController::class, 'adminIndex'])->name('death.index');
    Route::get('/death/{confirmation}', [DeathConfirmationController::class, 'adminShow'])->name('death.show');
    Route::post('/death/{confirmation}/verify', [DeathConfirmationController::class, 'verify'])->name('death.verify');

    Route::post('/ldms/{ldms}/trigger', [LDMSController::class, 'trigger'])->name('ldms.trigger');

    Route::get('/blockchain', [BlockchainController::class, 'index'])->name('blockchain.index');
    Route::post('/blockchain/verify', [BlockchainController::class, 'verify'])->name('blockchain.verify');

    Route::get('/donations', [AdminController::class, 'donations'])->name('donations.index');

    Route::get('/pdf/audit', [BlockchainController::class, 'pdfAuditLog'])->name('pdf.audit');
});

/*
|--------------------------------------------------------------------------
| NOK area (with 2FA required)
|--------------------------------------------------------------------------
*/
Route::middleware(['role:nok', 'twofactor'])->prefix('nok')->name('nok.')->group(function () {
    Route::get('/dashboard', [NOKController::class, 'dashboard'])->name('dashboard');

    Route::get('/death/create', [DeathConfirmationController::class, 'create'])->name('death.create');
    Route::post('/death', [DeathConfirmationController::class, 'store'])->name('death.store');

    Route::get('/ldms/{ldms}', [LDMSController::class, 'nokShow'])->name('ldms.show');

    // Streams a decrypted LDMS attachment to the verified NOK.
    // Needed because encrypted files cannot be served as static URLs.
    Route::get('/ldms/{ldms}/download/{filename}', [LDMSController::class, 'nokDownload'])
        ->name('ldms.download');
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
| Notifications (any authenticated user)
|--------------------------------------------------------------------------
*/
Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications.index');
Route::post('/notifications/{id}/read', [NotificationController::class, 'markAsRead'])->name('notifications.read');
Route::get('/notifications/unread-count', [NotificationController::class, 'unreadCount'])->name('notifications.unread');
