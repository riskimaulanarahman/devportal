# Kalimantan Fiber DevPortal

Portal internal Kalimantan Fiber untuk manajemen submission, SDM, pengadaan, keuangan, dan administrasi — dibangun di atas Laravel 8.

---

## Tech Stack

| Layer | Teknologi |
|-------|-----------|
| Backend | Laravel 8 (PHP 7.3+/8.0+) |
| Frontend | Blade templates + Bootstrap 5 + vanilla JavaScript |
| ORM | Eloquent (MySQL / SQL Server) |
| Auth | LDAP (Active Directory) + database fallback |
| Build | Laravel Mix (Webpack) |
| PDF | DomPDF |
| Email | Laravel Mail (SMTP) |

---

## Fitur Utama

- **Submission workflow** — Project, Tiket, UAV Mission, MOM, HRSC, GHM, JDI, Legal, Memorandum, WPHC, SPKL
- **Keuangan** — Advance, CAPEX, CCM dengan multi-level approval
- **HRIS & SDM** — HCRF, MCOP, Headcount monitoring, History SDM
- **Pengadaan** — E-Catalog, Material Request, MMF28/MMF30
- **IT** — Active Directory Request
- **Approval engine** — Multi-level approver, notifikasi email, history tracking
- **GHM Booking** — Reservasi Guest House & Mess
- **Data Kandidat (EMDF)** — Manajemen data calon karyawan
- **Admin panel** — Manajemen user, modul, sidemenu, referensi data
- **Multi-bahasa** — 7 bahasa (en, de, es, it, ru, dll)

---

## Setup

### Prerequisites
- PHP 7.3+ / 8.0+
- Composer
- Node.js + npm
- MySQL / SQL Server
- LDAP server (opsional, ada fallback ke DB)

### Install

```bash
# Clone repository
git clone <repo-url>
cd devportal

# Install PHP dependencies
composer install

# Install Node dependencies
npm install

# Copy environment file
cp .env.example .env

# Generate application key
php artisan key:generate

# Setup database di .env, lalu jalankan migrasi
php artisan migrate --seed

# Compile assets
npm run dev

# Jalankan server
php artisan serve
```

### Environment Variables Penting

```env
APP_URL=http://localhost
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_DATABASE=devportal
DB_USERNAME=root
DB_PASSWORD=

# LDAP (opsional)
LDAP_HOST=127.0.0.1
LDAP_BASE_DN=dc=company,dc=local

# Mail
MAIL_MAILER=smtp
MAIL_HOST=smtp.example.com
MAIL_FROM_ADDRESS=noreply@kalimantanfiber.co.id
```

---

## Panduan AI Agent

File **[codebase.md](codebase.md)** berisi panduan lengkap struktur codebase untuk AI agent (Claude Code, Copilot, dll) agar tidak perlu scanning ulang project setiap kali ada task baru.

Dokumen tersebut mencakup:
- Directory map lengkap dengan anotasi fungsi setiap folder/file
- Daftar lengkap 66+ model Eloquent (dikelompokkan per domain)
- Semua API endpoint (Admin, Submission, Module, Core)
- Shared traits yang wajib di-reuse
- Pola approval flow standar
- Konvensi naming dan struktur view
- Panduan menambahkan fitur baru
- Quick reference file-file kritis

---

## Struktur Folder Utama

```
app/
  Http/
    Controllers/    - Admin/, Auth/, Module/, Submission/
    Middleware/     - 10 middleware (termasuk SessionCheckMiddleware)
    Traits/         - 9 shared traits (ApproverTrait, LogTrait, dll)
  Models/           - 66+ Eloquent models
  Services/         - DataCandidateService
  Mail/             - ApproverNotification, ReminderMail, SubmissionMail
config/             - 19 config files (auth, ldap, database, mail, dll)
database/           - migrations, seeders, factories, sql
resources/
  views/            - Blade templates (layouts, components, per-modul)
  js/               - app.js, bootstrap.js, pages/*.init.js
  lang/             - File terjemahan
routes/
  web.php           - Web routes
  api.php           - Core API
  admin.php         - Admin API resources
  submission.php    - Submission API resources (50+ endpoints)
  module.php        - Module API resources
```

---

## Lisensi

Internal use — Kalimantan Fiber Group
