# e-Tawassul — Build Handoff Document (v2)

**Project:** e-Tawassul — Secure Blockchain-Based Crisis Response System for Student Well-being
**Institution:** International Islamic University Malaysia (IIUM)
**Framework:** Laravel 10+ (strict MVC), MySQL, Bootstrap 5 + Alpine.js
**Build status:** Steps 1–9a complete (~60%). Remaining: 1 policy, all controllers, routes, views, frontend assets, PDF export, project metadata files.

---

## ✅ COMPLETED — Steps 1–9a

### Step 1: Migrations (16 files in `database/migrations/`)
All 16 migrations present — students, admins, lecturers, next_of_kin, public_users, guardian_consent, crisis, crisis_report, death_confirmation, ldms, donation, notification_log, activity_log, blockchain, otp_codes, system tables (sessions/jobs/failed_jobs/cache).

### Step 1: Models (13 files in `app/Models/`)
All models include full Eloquent relationships. Key features:
- `Student.php` — student_id string PK, full relationship graph
- `Ldms.php` — auto-encrypts message_content via Crypt::encryptString accessor/mutator
- `Crisis.php` — progress_percent + priority_color accessors
- `ActivityLog.php` — static `record()` helper

### Step 2: Factories & Seeders
- 6 factories. `DatabaseSeeder.php` creates demo accounts + 4 active crisis cases + donations.

### Step 3: Authentication System
- `config/auth.php` — 4 guards: student, admin, nok, lecturer
- `app/Http/Controllers/Auth/AuthController.php` — login, 2FA flow, logout, rate limited
- `app/Http/Middleware/RoleMiddleware.php`
- `app/Http/Middleware/TwoFactorMiddleware.php`
- `app/Services/TwoFactorService.php`
- `app/Http/Requests/LoginRequest.php`

### Step 4: iMaalum Scraper (`app/Services/ImaalumScraperService.php`, `app/Jobs/ScrapeImaalumData.php`)
Hybrid quddus API + lecturers-table approach. Password discarded after use.

### Step 5-7: Blockchain Integration
- `app/Services/BlockchainService.php` — Quorum + MySQL simulation fallback
- `config/blockchain.php`
- `contracts/CrisisAudit.sol`

### ⭐ Step 8 (NEW — done in v2): Laravel Scaffolding
- `app/Http/Kernel.php` — middleware aliases registered including `role` and `twofactor`
- `app/Http/Controllers/Controller.php` — base controller
- `app/Http/Middleware/EncryptCookies.php`
- `app/Http/Middleware/VerifyCsrfToken.php`
- `app/Exceptions/Handler.php`
- `app/Console/Kernel.php`
- `app/Providers/AppServiceProvider.php` — **registers all 4 policies** (StudentPolicy, CrisisReportPolicy, LDMSPolicy, DeathConfirmationPolicy). DeathConfirmationPolicy is referenced here but still needs creating.
- `app/Providers/RouteServiceProvider.php`
- `app/Providers/EventServiceProvider.php`
- `bootstrap/app.php`
- `artisan`
- `public/index.php`
- `routes/console.php`

### ⭐ Step 9a (NEW — done in v2): Mailables, Form Requests, NotificationService, 3 Policies

**Mailables (8, all in `app/Mail/`)** — each pairs with a Blade template at `resources/views/emails/{kebab-name}.blade.php` that **DOES NOT EXIST YET**:
- `OtpMail.php` → `emails.otp`
- `CrisisReportSubmittedMail.php` → `emails.crisis-report-submitted`
- `CrisisVerifiedMail.php` → `emails.crisis-verified` (accepts blockchainHash)
- `CrisisRejectedMail.php` → `emails.crisis-rejected` (accepts reason)
- `DeathConfirmationSubmittedMail.php` → `emails.death-confirmation-submitted`
- `DeathVerifiedMail.php` → `emails.death-verified` (accepts blockchainHash)
- `LDMSReleasedMail.php` → `emails.ldms-released`
- `DonationReceivedMail.php` → `emails.donation-received`

**Form Requests (10, all in `app/Http/Requests/`)**:
- `SubmitCrisisReportRequest`, `VerifyCrisisRequest`, `RejectCrisisRequest`
- `SubmitDeathConfirmationRequest`, `VerifyDeathRequest`
- `CreateLDMSRequest`, `UpdateLDMSRequest`, `TriggerLDMSRequest`
- `DonateRequest`, `VerifyHashRequest`
- (plus existing `LoginRequest`)

**Services**:
- `app/Services/NotificationService.php` — wraps Mail::queue + NotificationLog::create with graceful failure. Signature:
  ```
  send(recipientType, recipientId, email, mailable, notificationType, subject, message, link=null, studentId=null): NotificationLog
  logOnly(recipientType, recipientId, notificationType, subject, message, link=null, studentId=null): NotificationLog
  ```

**Policies (3 of 4 in `app/Policies/`)**:
- `StudentPolicy` — viewByAdmin, viewByNok, viewSelf
- `CrisisReportPolicy` — viewAsStudent, viewAsAdmin, verify, reject, update
- `LDMSPolicy` — view, update, delete, viewByNok, trigger
- **`DeathConfirmationPolicy` — STILL TO BUILD** (AppServiceProvider already references it)

---

## ❌ REMAINING — Steps 9b through 12

### Step 9b: DeathConfirmationPolicy (1 file)
Needed methods:
- `submit(NextOfKin $nok)` — NOK can submit only for their linked student
- `viewAsAdmin(Admin $admin, DeathConfirmation $c)`
- `viewAsNok(NextOfKin $nok, DeathConfirmation $c)`
- `verify(Admin $admin, DeathConfirmation $c)` — check `verify_death` permission or super_admin

### Step 10: Controllers (all need building — 11 total in `app/Http/Controllers/`)
- `StudentController` — dashboard (auto-dispatches ScrapeImaalumData if stale), profile, scrape banner
- `AdminController` — dashboard with stats (pending reports count, active crises, recent activity), student audit table
- `NOKController` — dashboard, listing of own student's released LDMS
- `LecturerController` — dashboard, notifications listing
- `CrisisController` — `index` (public dashboard), `show`
- `CrisisReportController` — student `create`/`store`, admin `verify`/`reject` (call `BlockchainService::recordEvent` with event type `CRISIS_VERIFIED` or `REPORT_REJECTED`, then `NotificationService::send` to the student)
- `LDMSController` — student `index`/`create`/`store`/`edit`/`update`/`destroy`; admin `trigger` (calls `BlockchainService::recordEvent` with `LDMS_TRIGGERED`, sets `is_released=true`, notifies NOK via `LDMSReleasedMail`); NOK `show` (decrypts via the model accessor)
- `DonationController` — `create` (public donate form), `store` (process donation, update `crisis.donation_raised`, call `BlockchainService::recordEvent` with `DONATION_RECORDED`, send `DonationReceivedMail`), `progress` (AJAX JSON returning current raised/target/percent)
- `NotificationController` — `index`, `markAsRead` (AJAX), `unreadCount` (AJAX for bell)
- `BlockchainController` — admin `index` (audit log table), `verify` (POST with hash; uses `BlockchainService::verifyHashString`)
- `DeathConfirmationController` — NOK `create`/`store` (notifies admins), admin `verify`/`reject` (calls `BlockchainService::recordEvent` with `DEATH_CONFIRMED` on verify, updates `students.status='deceased'`, notifies NOK)
- `PdfExportController` (or methods on relevant controllers) — uses `barryvdh/laravel-dompdf`:
  - `crisisReceipt($crisisId)` — public crisis details + total raised
  - `donationReceipt($donationId)` — donation receipt
  - `auditLog()` — admin blockchain audit log export

### Step 10: routes/web.php
Use named middleware-grouped routes. Sketch:
```
// Public
Route::get('/', [CrisisController::class, 'index'])->name('home');
Route::get('/crisis/{crisis}', [CrisisController::class, 'show'])->name('crisis.show');
Route::get('/donate/{crisis}', [DonationController::class, 'create'])->name('donate.create');
Route::post('/donate/{crisis}', [DonationController::class, 'store'])->name('donate.store');
Route::get('/crisis/{crisis}/progress', [DonationController::class, 'progress'])->name('donate.progress');

// Auth
Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->name('login.post');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
Route::get('/twofactor', [AuthController::class, 'showTwoFactor'])->name('nok.twofactor.show');
Route::post('/twofactor', [AuthController::class, 'verifyTwoFactor'])->name('nok.twofactor.verify');
Route::post('/twofactor/resend', [AuthController::class, 'resendOtp'])->name('nok.twofactor.resend');

// Student
Route::middleware('role:student')->prefix('student')->name('student.')->group(function() {
    Route::get('/dashboard', [StudentController::class, 'dashboard'])->name('dashboard');
    Route::get('/profile', [StudentController::class, 'profile'])->name('profile');
    Route::get('/crisis/create', [CrisisReportController::class, 'create'])->name('crisis.create');
    Route::post('/crisis', [CrisisReportController::class, 'store'])->name('crisis.store');
    Route::get('/crisis/{report}', [CrisisReportController::class, 'show'])->name('crisis.show');
    Route::resource('ldms', LDMSController::class)->except(['show']);
});

// Admin
Route::middleware('role:admin')->prefix('admin')->name('admin.')->group(function() {
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

// NOK
Route::middleware(['role:nok','twofactor'])->prefix('nok')->name('nok.')->group(function() {
    Route::get('/dashboard', [NOKController::class, 'dashboard'])->name('dashboard');
    Route::get('/death/create', [DeathConfirmationController::class, 'create'])->name('death.create');
    Route::post('/death', [DeathConfirmationController::class, 'store'])->name('death.store');
    Route::get('/ldms/{ldms}', [LDMSController::class, 'nokShow'])->name('ldms.show');
});

// Lecturer
Route::middleware('role:lecturer')->prefix('lecturer')->name('lecturer.')->group(function() {
    Route::get('/dashboard', [LecturerController::class, 'dashboard'])->name('dashboard');
});

// Notifications (any auth)
Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications.index');
Route::post('/notifications/{id}/read', [NotificationController::class, 'markAsRead'])->name('notifications.read');
Route::get('/notifications/unread-count', [NotificationController::class, 'unreadCount'])->name('notifications.unread');
```

### Step 10: Blade Views (~30 files)

**Email templates (`resources/views/emails/`)** — 7 files (plus reuse): otp, crisis-report-submitted, crisis-verified, crisis-rejected, death-confirmation-submitted, death-verified, ldms-released, donation-received.

**Layouts (`resources/views/layouts/`)** — 5 files: public, student, admin, nok, lecturer. Each authed layout has sidebar + topbar + notification bell + content slot.

**Reusable components (`resources/views/components/`)** — 5 files: crisis-card, donation-progress (Alpine.js bar polling `/crisis/{id}/progress`), notification-bell (polls unread-count), blockchain-badge (green "Verified" pill with hash preview), priority-badge (colored impact_level).

**Public**: `dashboard.blade.php` (crisis-card grid), `donate.blade.php`.

**Auth**: `login.blade.php` (Bootstrap nav-tabs for 4 roles + demo credentials helper card), `twofactor.blade.php`.

**Student** (`resources/views/student/`): dashboard (own crises summary, scrape banner, notifications preview), crisis/create, crisis/show, ldms/index, ldms/create, ldms/edit (with MediaRecorder audio capture).

**Admin** (`resources/views/admin/`): dashboard (stat cards), crisis/index (pending+verified tabs), crisis/show (verify/reject forms), death/index, death/show, blockchain/index (audit log + hash-verify form), students/index, donations/index.

**NOK** (`resources/views/nok/`): dashboard, death/create, ldms/show (decrypted + media playback).

**Lecturer** (`resources/views/lecturer/`): dashboard (notifications).

### Step 10: Frontend assets
- `public/css/app.css` — theme: primary `#1a6fa8`, success `#28a745`, teal `#20c997`, soft backgrounds, sidebar, dashboard stat cards, login card with role tabs.
- `public/js/app.js` — Alpine.js init, donation AJAX progress poller (every 5s), notification bell polling (every 30s), MediaRecorder audio capture for LDMS, OTP 6-digit auto-advance widget, copy-to-clipboard helper.

### Step 12: Project files
- `composer.json`:
  ```
  "require": {
      "php": "^8.1",
      "laravel/framework": "^10.10",
      "guzzlehttp/guzzle": "^7.5",
      "symfony/dom-crawler": "^6.0",
      "symfony/css-selector": "^6.0",
      "barryvdh/laravel-dompdf": "^2.0"
  }
  ```
- `.env.example` — DB_*, MAIL_*, QUEUE_CONNECTION=database, SESSION_ENCRYPT=true, QUORUM_NODE_URL, QUORUM_CONTRACT_ADDRESS, QUORUM_FROM_ADDRESS, QUORUM_CHAIN_ID, LECTURER_DIRECTORY_FALLBACK.
- `package.json` — Bootstrap 5, Alpine.js.
- `README.md` with XAMPP/Laragon setup: composer install → copy .env → key:generate → create DB `e_tawassul` → migrate --seed → storage:link → queue:work (separate terminal) → serve.
- `.gitignore`.
- Standard Laravel 10 config files: `config/app.php`, `database.php`, `mail.php`, `queue.php`, `session.php`, `cache.php`, `filesystems.php`, `services.php`, `view.php`, `logging.php`. Only `auth.php` and `blockchain.php` are project-specific (both done).

---

## PROMPT TO USE IN THE NEW CHAT

> Continue building e-Tawassul (Secure Blockchain-Based Crisis Response System for IIUM, Laravel 10+ MVC). I've attached the partial build ZIP with Steps 1–9a complete: full schema (16 migrations, 13 models), seeders, factories, 4-guard auth + NOK 2FA, ImaalumScraperService, BlockchainService with Quorum+simulation fallback, CrisisAudit.sol, full Laravel scaffolding (Kernel with role+twofactor middleware aliases, providers, exception handler, bootstrap), all 8 Mailables, all 10 Form Requests, NotificationService, 3 of 4 policies (StudentPolicy, CrisisReportPolicy, LDMSPolicy).
>
> Read HANDOFF.md inside the ZIP first — it has the route sketch, controller responsibilities, and view inventory.
>
> Now complete Steps 9b–12:
> 1. DeathConfirmationPolicy (the missing 4th policy — AppServiceProvider already references it)
> 2. All 11 controllers per HANDOFF spec (Student/Admin/NOK/Lecturer/Crisis/CrisisReport/LDMS/Donation/Notification/Blockchain/DeathConfirmation), wiring BlockchainService::recordEvent for CRISIS_VERIFIED/REPORT_REJECTED/DEATH_CONFIRMED/LDMS_TRIGGERED/DONATION_RECORDED and NotificationService::send for all user-facing events
> 3. routes/web.php — middleware-grouped named routes per the sketch in HANDOFF.md
> 4. 7 email Blade templates in resources/views/emails/ matching the existing Mailables
> 5. 5 layouts + 5 reusable Blade components + all dashboard views (public/auth/student/admin/nok/lecturer) per the view inventory
> 6. public/css/app.css and public/js/app.js (Bootstrap 5 + Alpine.js + MediaRecorder)
> 7. PDF export using barryvdh/laravel-dompdf (crisis receipt, donation receipt, audit log)
> 8. composer.json, .env.example, package.json, README.md with XAMPP setup steps, .gitignore, standard Laravel 10 config files (app.php, database.php, mail.php, queue.php, session.php, cache.php, filesystems.php, services.php, view.php, logging.php)
>
> Build all of it, no placeholders, production-ready, then ZIP and present.

---

## Demo Accounts (already seeded)

| Role    | Identifier               | Password    |
| ------- | ------------------------ | ----------- |
| Admin   | `admin@iium.edu.my`      | `password`  |
| Student | `2225498`                | `password`  |
| NOK     | `nok@example.com`        | `password` (+ 6-digit OTP) |

## Key Design Decisions Already Made

1. **iMaalum scraper:** Hybrid — quddus API for profile/schedule, seeded `lecturers` table for email resolution. Optional directory fallback toggled by `LECTURER_DIRECTORY_FALLBACK=true`.
2. **Blockchain:** MySQL simulation by default; switches to real Quorum when `QUORUM_NODE_URL` is set. Off-chain data + on-chain hash pattern. Event types: `CRISIS_VERIFIED`, `DEATH_CONFIRMED`, `LDMS_TRIGGERED`, `DONATION_RECORDED`, `REPORT_REJECTED`.
3. **LDMS encryption:** Auto via accessor/mutator in `Ldms` model using `Crypt::encryptString`.
4. **NOK 2FA:** 5-min OTP via email, 30-min re-prompt window via TwoFactorMiddleware.
5. **Password handling for iMaalum:** Never stored — passed to scraper job constructor, unset immediately after login call.
6. **Notifications:** Always go through `NotificationService::send()` so both the email AND the in-app NotificationLog row are created in one call.
7. **Admin permission strings (in `admins.permissions` JSON):** `verify_crisis`, `verify_death`, `trigger_ldms`, `manage_donations`, `view_blockchain`. `role='super_admin'` bypasses these checks in policies.

cd C:\xampp\htdocs\e-tawassul\besu-network
docker-compose up -d
PowerShell -ExecutionPolicy Bypass -File .\test-rpc.ps1

// 1. Show Besu is alive
//$web3 = new \Web3\Web3(config('blockchain.node_url'));
//$web3->clientVersion(function($e,$v){dump('Connected: ' . $v);});

// 2. Record a crisis verification on-chain
//$svc = app(\App\Services\BlockchainService::class);
//$result = $svc->recordEvent('CRISIS_VERIFIED', ['demo' => true, 'time' => now()->toIso8601String()], 1, 'crisis_report');
dump($result);  // shows mode: onchain + tx_hash

// 3. Verify the hash made it into the smart contract
//$svc->verifyHashOnChain($result['hash']);  // shows exists: true

// 4. Demonstrate tamper-evidence
$svc->verifyHash($result['blockchain_id'], ['demo' => true, 'time' => 'wrong-time']);  // returns false
