# e-Tawassul

**Secure Blockchain-Based Crisis Response System for Student Well-being**
International Islamic University Malaysia (IIUM)

Built on **Laravel 10 (MVC)**, **MySQL**, **Bootstrap 5**, **Alpine.js**, and a permissioned **Quorum** audit chain (with a graceful MySQL-only simulation mode for development).

---

## Features

- **Public crisis dashboard** with live donation progress and case detail pages.
- **Four authenticated roles** (Student / Admin / Next of Kin / Lecturer) on separate Laravel guards.
- **Crisis report workflow** — student submission, admin verification with on-chain hash, donor receipts.
- **Death confirmation workflow** — NOK submission with supporting document, admin verification, automatic student status update.
- **Legacy Digital Messages (LDMS)** — encrypted-at-rest letters/audio/photos that students leave for next of kin. AES-encrypted via Laravel's `Crypt` facade; released only after a verified death confirmation.
- **Permissioned blockchain audit** — five event types (`CRISIS_VERIFIED`, `REPORT_REJECTED`, `DEATH_CONFIRMED`, `LDMS_TRIGGERED`, `DONATION_RECORDED`) recorded as SHA-256 hashes either on Quorum (when configured) or in a tamper-evident MySQL simulation table.
- **iMaalum scraper** — hybrid quddus-API + lecturers-table approach for student profile sync. Password is used only for the login call and immediately discarded.
- **NOK two-factor auth** — 6-digit OTP via email, 5-minute validity, 30-minute re-prompt window for sensitive LDMS access.
- **PDF exports** via `barryvdh/laravel-dompdf` — crisis case receipt, donation receipt, full blockchain audit log.
- **Notification system** — unified `NotificationService` that queues an email AND writes a `NotificationLog` row in a single call. In-app bell polls every 30 seconds.

---

## Local setup with XAMPP / Laragon

### 1. Prerequisites
- **PHP 8.1+** with extensions: `pdo_mysql`, `mbstring`, `openssl`, `tokenizer`, `xml`, `ctype`, `json`, `bcmath`, `fileinfo`, `curl`, `dom`
- **Composer 2.x**
- **MySQL 8.x** (or MariaDB 10.6+)
- A working web server — Apache (XAMPP) or nginx — or use Laravel's built-in server

### 2. Clone & install
```bash
cd path/to/htdocs            # or your Laragon www root
git clone <your-repo-url> e-tawassul
cd e-tawassul

composer install
```

### 3. Environment file
```bash
cp .env.example .env
php artisan key:generate
```

Open `.env` and update at minimum:
```
DB_DATABASE=e_tawassul
DB_USERNAME=root
DB_PASSWORD=
```

### 4. Create database
Open phpMyAdmin (`http://localhost/phpmyadmin`) and create an empty database called **`e_tawassul`** with `utf8mb4_unicode_ci` collation.

### 5. Migrate + seed
```bash
php artisan migrate --seed
```

This creates all 16 tables and seeds:
- 1 demo admin (`admin@iium.edu.my` / `password`)
- 2 random admins
- 15 IIUM lecturers
- 1 demo student (`2225498` / `password`) + 20 random students
- 1 demo NOK (`nok@example.com` / `password` + OTP) + 15 random NOK
- 30 public users
- 4 active crisis cases with verified reports + 8–30 donations each
- 3 pending crisis reports for admin to review

### 6. Storage link
```bash
php artisan storage:link
```

### 7. Run the queue worker
The system uses Laravel's database queue for emails and the iMaalum scrape job. Run this in a **separate terminal**:
```bash
php artisan queue:work --tries=3
```

### 8. Serve the app
```bash
php artisan serve
```
The app will be available at **<http://127.0.0.1:8000>**.

> Using Laragon? Just enable pretty URLs — Laragon auto-creates `http://e-tawassul.test` if your project is in `C:\laragon\www\e-tawassul`.

---

## Demo accounts

| Role        | Identifier               | Password   | Notes                   |
| ----------- | ------------------------ | ---------- | ----------------------- |
| **Admin**   | `admin@iium.edu.my`      | `password` | Super admin             |
| **Student** | `2225498`                | `password` | Triggers iMaalum scrape |
| **NOK**     | `nok@example.com`        | `password` | Requires email OTP (shown on screen in demo mode if mail isn't configured) |

---

## Blockchain mode

By default, the system runs in **simulation mode** — every event is hashed (SHA-256) and stored in the `blockchain` table with `mode = 'simulation'`. This is the recommended setup for development and demos.

To switch to a real **Quorum** permissioned network:

1. Deploy `contracts/CrisisAudit.sol` to your Quorum node (e.g. via Truffle or Hardhat).
2. Set in your `.env`:
   ```
   QUORUM_NODE_URL=http://localhost:22000
   QUORUM_CONTRACT_ADDRESS=0xYourContractAddress
   QUORUM_FROM_ADDRESS=0xYourAccountAddress
   QUORUM_CHAIN_ID=1337
   ```
3. The `BlockchainService` will automatically use the real chain for new events; if the node is unreachable, it falls back to simulation mode and logs a warning.

---

## Useful artisan commands

```bash
# Reset everything (drops + remigrates + reseeds)
php artisan migrate:fresh --seed

# Run queue worker (required for emails)
php artisan queue:work

# Clear all caches during dev
php artisan optimize:clear
```

---

## Project structure

```
app/
  Http/
    Controllers/        # 12 controllers (Auth, Student, Admin, NOK, Lecturer, Crisis, CrisisReport, LDMS, Donation, Notification, Blockchain, DeathConfirmation, PdfExport)
    Middleware/         # role + twofactor + standard middlewares
    Requests/           # 10 FormRequest classes for validation
  Mail/                 # 8 Mailable classes
  Models/               # 13 Eloquent models
  Policies/             # 4 multi-guard policies
  Services/             # BlockchainService, NotificationService, ImaalumScraperService, TwoFactorService
  Jobs/                 # ScrapeImaalumData
config/                 # Standard Laravel config + auth.php (4 guards) + blockchain.php
contracts/              # CrisisAudit.sol (Solidity)
database/
  migrations/           # 16 migrations
  seeders/              # Demo data
  factories/            # 6 factories
public/
  css/app.css           # Theme stylesheet
  js/app.js             # Alpine + MediaRecorder + bell + donation polling
resources/views/
  layouts/              # public, app (shared shell), student, admin, nok, lecturer
  components/           # crisis-card, donation-progress, notification-bell, blockchain-badge, priority-badge
  emails/               # 8 email templates
  pdf/                  # 3 PDF templates
  public/, auth/, student/, admin/, nok/, lecturer/, notifications/
routes/web.php          # Middleware-grouped named routes
```

---

## Tech credits

- Laravel 10
- Bootstrap 5.3 (loaded from CDN)
- Alpine.js 3.13 (loaded from CDN)
- Bootstrap Icons 1.11 (loaded from CDN)
- barryvdh/laravel-dompdf
- Quorum (permissioned Ethereum)

---

## License

MIT. Built for IIUM as part of a student well-being initiative.
