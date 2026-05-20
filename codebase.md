# Codebase Guide — Kalimantan Fiber DevPortal

> Dokumen ini dibuat untuk AI agent agar tidak perlu scanning ulang seluruh codebase setiap kali ada task baru.
> Baca bagian yang relevan dengan task, lalu langsung ke file yang dituju.

---

## 1. Project Overview

| Aspek | Detail |
|-------|--------|
| Nama | Kalimantan Fiber DevPortal |
| Framework | Laravel 8 (PHP 7.3+/8.0+) |
| Frontend | Blade templates + Bootstrap 5 + vanilla JavaScript |
| Build tool | Laravel Mix (Webpack via `webpack.mix.js`) |
| ORM | Eloquent (MySQL / SQL Server) |
| Authentication | LDAP + database fallback |
| PDF | DomPDF 2.1 |
| HTTP client (frontend) | Axios (CSRF auto-included) |
| Multi-language | 7 bahasa (`resources/lang/`) |

---

## 2. Directory Map

```
app/
  Console/                    - Artisan commands
  Exceptions/Handler.php      - Exception handler global
  Http/
    Controllers/
      Admin/                  - 26+ admin controllers (user, module, sidemenu, company, department, dll)
      Auth/                   - 8 auth controllers (Login, Register, ForgotPassword, dll)
      Module/                 - 15+ module controllers (DataCandidate, Employeedata, Ecatalog, MCOP, dll)
      Submission/             - 36+ submission controllers (Project, Financial/*, HRIS/*, MMF/*, Memorandum, dll)
      HomeController.php
      ListController.php
      MainController.php
      MemorandumReportController.php
      BerkasController.php
      ApproverListController.php
      ApproverHistoryController.php
      AssignmenttoController.php
      AttachmentController.php
      CategoryController.php
      GeneratemenuController.php
      LogErrorController.php
      LogSuccessController.php
      SessionCheckController.php
      StackholdersController.php
    Middleware/
      Authenticate.php              - Redirect unauthenticated ke login
      SessionCheckMiddleware.php    - Validasi session (key: session.check)
      Localization.php              - Penanganan bahasa
      RedirectIfAuthenticated.php
      EncryptCookies.php
      VerifyCsrfToken.php
      TrimStrings.php
      TrustProxies.php
      TrustHosts.php
      PreventRequestsDuringMaintenance.php
    Traits/
      ApproverTrait.php         - Logika approver (REUSE ini)
      CopytoserverTrait.php     - Copy file ke server
      DateTrait.php             - Utilitas tanggal
      HasAuth.php               - Helper autentikasi
      HasGenerateCode.php       - Generate kode unik
      HasGetModule.php          - Ambil data module
      HasMessage.php            - Penanganan pesan/notifikasi
      LogTrait.php              - Logging ke LogSuccess/LogError
      ProcessProjectTrait.php   - Pemrosesan project
    Kernel.php
  Ldap/User.php               - LDAP user integration
  Mail/
    ApproverNotification.php   - Notifikasi ke approver
    ReminderMail.php           - Pengingat
    SubmissionMail.php         - Konfirmasi submission
  Models/                     - 66+ Eloquent models (lihat Section 4)
  Providers/                  - AppServiceProvider, AuthServiceProvider, dll
  Services/
    DataCandidateService.php  - Query, filter, pagination, export CSV kandidat
  View/Components/
    employeedata.php           - Blade component employee data

bootstrap/
config/
  app.php, auth.php, database.php, ldap.php, session.php
  mail.php, queue.php, cache.php, logging.php, cors.php
  filesystems.php, sanctum.php, services.php, dompdf.php
  broadcasting.php, hashing.php, view.php, (19 total)

database/
  migrations/           - Schema migrations
  migrations/backup/    - Backup migrations
  seeders/
  factories/
  sql/                  - Raw SQL files

public/                 - Web root (assets di-compile ke sini via Mix)

resources/
  assets/               - Static assets (pre-compiled)
  fonts/                - 26 font files
  images/               - 25 direktori gambar (per fitur)
  js/
    app.js              - Entry point JavaScript
    bootstrap.js        - Setup Axios + CSRF token
    pages/*.init.js     - JavaScript per halaman
  lang/                 - en, de, es, it, ru, + 2 lainnya
  sass/, scss/          - Stylesheet source
  views/
    area/               - Data candidate (EMDF) views
    auth/               - Login, register, password reset
    components/         - breadcrumb, capexhistory, employeedata, mmf30, panduan-modal, wphc
    core/               - Template/demo views (charts, forms, ecommerce)
    dashboard/          - Dashboard views (project, report, GHM booking, index)
    emails/             - 18 email templates (diorganisir per modul: Ecatalog, HRIS, IT, MMF, dll)
    errors/             - 401.blade.php, 404.blade.php, 500.blade.php
    import/             - MCOP import, memorandum import
    layouts/            - 20 layout files (app, master, master-without-nav, sidebar, topbar, dll)

routes/
  web.php               - Web routes utama (dashboard, GHM booking, MCOP, memorandum PDF)
  api.php               - Core API (upload-berkas, getlogin, check-user-access, logsuccess, logerror)
  admin.php             - Admin API resources (24 resource routes)
  submission.php        - Submission API resources — TERBESAR (50+ resource routes)
  module.php            - Module/HR API resources (13 resource routes)
  list.php              - List view routes
  channels.php          - Broadcasting channels
  console.php           - Console commands

tests/
webpack.mix.js          - Konfigurasi asset compilation (SASS, RTL, library third-party)
composer.json           - PHP dependencies
package.json            - Node dependencies (Bootstrap 5, ApexCharts, CKEditor, FullCalendar, dll)
```

---

## 3. Authentication Flow

```
User login (samaccountname + password)
  └─> LoginController
        ├─> LDAP bind (samaccountname)
        │     └─> Berhasil: sync user ke DB (fullname ← cn, email ← mail, username ← samaccountname)
        │           └─> Buat session (user_id)
        └─> Gagal LDAP: fallback ke DB credentials
              └─> Buat session jika cocok

Logout: delete session records dari DB, user logged out
```

**Key files:**
- [app/Http/Controllers/Auth/LoginController.php](app/Http/Controllers/Auth/LoginController.php)
- [app/Models/User.php](app/Models/User.php) — implements `LdapAuthenticatable`, trait `AuthenticatesWithLdap`
- [app/Ldap/User.php](app/Ldap/User.php)
- [config/ldap.php](config/ldap.php)
- [app/Http/Middleware/SessionCheckMiddleware.php](app/Http/Middleware/SessionCheckMiddleware.php) — middleware `session.check`

**Guard:** `web` (session-based, file driver default)

---

## 4. Models — Daftar Lengkap

### Root Models (`app/Models/`)
Address, Answer, Approvaltype, Approvaluser, ApproverListHistory, ApproverListReq,
Assignmentto, Attachment, Bank, Category, CategoryForm, Categoryhrsc, Code,
Communication, Company, CoreValue, Currency, Department, Designation, Developer,
Document, Ecatalog, Education, Employee, Ethnic, Experience, Family, Ghm_room,
Grade, Holiday, Icon, Language, Level, Location, LogError, LogSuccess, Menu, Module,
Nationality, PersonalData, Purchasinguser, Question, RefCapexq, Reference, RekeningCcm,
Religion, Rfc, Score, Sequence, Session, SideMenu, Size, Skill, SocialMedia, SpklDetail,
Stackholders, Tax, Theme, UavAsset, Unit, User, Useraccess, WphcDetail

### Module Models (`app/Models/Module/`)
- Headcounts
- History

### Reference Models (`app/Models/Reference/`)
- TypeDocument

### Submission Models (`app/Models/Submission/`)

**Financial:**
- `Financial/Advance`, `AdvanceDetail`
- `Financial/Capex`, `CapexDetail`, `CapexJustification`, `CapexQuestion`, `CapexQuestionCF`
- `Financial/Ccm`, `CcmDetail`
- `Financial/ExchangeRate`
- `Financial/Financial`

**HRIS:**
- `HRIS/Hris`
- `HRIS/Hcrf/Hcrf`, `HcrfDetail`

**Procurement:**
- `Ecatalog/MaterialReq`, `Materialdetail`
- `MMF/Mmf`, `Mmf28`, `Mmf30`, `Mmf30detail`

**Other:**
- `Ghm`, `Hrsc`, `IT/ActiveDirectory`, `Jdi`, `Legal`
- `Memorandum`, `MemorandumDetail`
- `Mom`, `MomTask`, `MomTaskBound`, `MomTaskUpdate`
- `Project`, `Spkl`, `Ticket`
- `UavMission`, `UavMissionDetail`
- `Wphc`

---

## 5. API Endpoints

### Admin API (`routes/admin.php`) — 24 resource routes
```
/api/user, /api/module, /api/useraccess, /api/sidemenu, /api/icons,
/api/sequence, /api/theme, /api/company, /api/department, /api/grade,
/api/level, /api/location, /api/position, /api/ethnic, /api/religion,
/api/nationality, /api/approvaltype, /api/approvaluser, /api/developer,
/api/uavasset, /api/categoryhrsc, /api/ghm_admin, /api/categoryform,
/api/exchange_rate
```

### Submission API (`routes/submission.php`) — 50+ resource routes
```
/api/projectrequest, /api/projectreport
/api/ticketrequest
/api/missionrequest, /api/missionrequestdetail
/api/momrequest, /api/momtaskdetail, /api/momtaskbound, /api/momtaskupdate
/api/hrscrequest, /api/hrscreport
/api/ghmrequest
/api/jdirequest, /api/jdireport
/api/adrequest
/api/mmf28request, /api/mmf30request, /api/mmf30detail
/api/materialrequest, /api/materialdetail
/api/advancerequest, /api/advancedetail
/api/hcrfrequest, /api/hcrfdetail
/api/ccmrequest, /api/ccmdetail
/api/capexrequest, /api/capexdetail, /api/capexjustification, /api/capexquestion, /api/capexquestioncf
/api/memorandum_request, /api/memorandum_detail, /api/memorandum_approver, /api/memorandum_report, /api/memorandum-import
/api/legalrequest
/api/wphc_request, /api/wphc_detail, /api/wphc_report
/api/spkl_request, /api/spkl_report, /api/spkl_timesheet, /api/spkl_detail
/api/attachmentrequest, /api/approverlistrequest, /api/assignmentto, /api/stackholders, /api/categorysubmission
```

### Module API (`routes/module.php`) — 13 resource routes
```
/api/headcounts, /api/historyhcbu, /api/historyhcdepartment,
/api/historyhclocation, /api/historyhcposition, /api/historyhcgrade,
/api/historyhclevel, /api/historyhcappraisal, /api/employeedata,
/api/ecatalog, /api/purchasinguser, /api/mcop, /api/datacandidate
```

### Core API (`routes/api.php`)
```
POST /api/upload-berkas/{modname}   - Upload file attachment
POST /api/getlogin                  - Cek login status
POST /api/check-user-access         - Validasi akses user
POST /api/changedepthead            - Ganti dept head
GET|POST /api/logsuccess            - Log sukses
GET|POST /api/logerror              - Log error
```

---

## 6. Shared Traits — Selalu Reuse, Jangan Buat Ulang

Semua ada di `app/Http/Traits/`:

| Trait | Kegunaan |
|-------|----------|
| `ApproverTrait` | Logika approver (get approver, validasi, update status) |
| `CopytoserverTrait` | Copy file/attachment ke server tujuan |
| `DateTrait` | Format tanggal, konversi, kalkulasi |
| `HasAuth` | Helper cek autentikasi dan hak akses |
| `HasGenerateCode` | Generate kode unik per submission type |
| `HasGetModule` | Ambil data module aktif dari DB |
| `HasMessage` | Flash message, response JSON, notifikasi |
| `LogTrait` | Simpan ke `LogSuccess` / `LogError` (pakai dengan `LogTrait`) |
| `ProcessProjectTrait` | Pemrosesan khusus alur project submission |

---

## 7. Approval Flow Pattern

Semua submission mengikuti pola yang sama:

```
1. User buat request → simpan ke tabel submission (misal: Memorandum, Advance, Capex)
2. Tentukan approver → ApproverListReq + Assignmentto/Stackholders
3. Approver menerima notifikasi (Mail: ApproverNotification)
4. Approver approve/reject → update status di tabel submission
5. History tersimpan di ApproverListHistory
6. Notifikasi hasil ke requester (Mail: SubmissionMail)
```

Key models: `Approvaltype`, `Approvaluser`, `ApproverListReq`, `ApproverListHistory`, `Assignmentto`, `Stackholders`

---

## 8. View Layout System

Semua halaman extend dari layout di `resources/views/layouts/`:

```blade
@extends('layouts.master')
@section('title', 'Judul Halaman')
@section('content')
  ...
@endsection
```

Layout utama: `master.blade.php` → include `sidebar.blade.php` + `topbar.blade.php`

Shared data yang otomatis tersedia di semua view (dari `AppServiceProvider`):
- `$themes` — pengaturan tema user
- `$employee` — record employee user yang login
- `$developer` — profil developer

---

## 9. Frontend Patterns

- **AJAX**: Gunakan Axios (sudah di-setup di `resources/js/bootstrap.js`)
  ```javascript
  // CSRF token sudah otomatis di-include dari cookie
  axios.post('/api/endpoint', data).then(response => { ... })
  ```
- **JavaScript per halaman**: `resources/js/pages/namahalaman.init.js`
- **Notifikasi**: SweetAlert2 (sudah tersedia global)
- **Form validation**: Parsley.js
- **Rich text**: CKEditor 5 atau Quill
- **File upload**: Dropzone (endpoint: `POST /api/upload-berkas/{modname}`)
- **Tabel data**: GridJS atau DataTables
- **Charts**: ApexCharts, Chart.js, ECharts

---

## 10. Environment Variables (Key Names)

```env
# Application
APP_NAME, APP_ENV, APP_DEBUG, APP_KEY, APP_URL, ASSET_URL

# Database
DB_CONNECTION, DB_HOST, DB_PORT, DB_DATABASE, DB_USERNAME, DB_PASSWORD

# Session
SESSION_DRIVER, SESSION_LIFETIME, SESSION_DOMAIN

# Mail
MAIL_MAILER, MAIL_HOST, MAIL_PORT, MAIL_USERNAME, MAIL_PASSWORD
MAIL_ENCRYPTION, MAIL_FROM_ADDRESS, MAIL_FROM_NAME

# Cache & Queue
CACHE_DRIVER, QUEUE_CONNECTION

# Redis
REDIS_HOST, REDIS_PORT, REDIS_PASSWORD, REDIS_DB

# LDAP
LDAP_HOST, LDAP_USERNAME, LDAP_PASSWORD, LDAP_PORT, LDAP_BASE_DN
LDAP_TIMEOUT, LDAP_SSL, LDAP_TLS, LDAP_LOGGING, LDAP_CACHE

# AWS S3
AWS_ACCESS_KEY_ID, AWS_SECRET_ACCESS_KEY, AWS_DEFAULT_REGION, AWS_BUCKET

# Logging
LOG_CHANNEL, LOG_LEVEL
```

---

## 11. Conventions & Naming

| Hal | Konvensi |
|-----|----------|
| Controller methods | RESTful: `index`, `show`, `store`, `update`, `destroy` |
| Route naming | `{modul}.{action}` (misal: `memorandum.index`) |
| View path | Ikuti route group (misal: `submission/financial/advance/index.blade.php`) |
| Model naming | PascalCase singular (misal: `MemorandumDetail`) |
| API response | JSON `{status, message, data}` via `HasMessage` trait |
| Kode submission | Auto-generated via `HasGenerateCode` trait |
| File attachment | Upload ke `Attachment` model, relasi ke submission via `modname` |

---

## 12. Menambahkan Fitur Baru

### Submission baru (contoh pola):
1. Buat migration + model di `app/Models/Submission/`
2. Buat controller di `app/Http/Controllers/Submission/`
   - Use traits: `HasMessage`, `LogTrait`, `HasGenerateCode`, `ApproverTrait`
3. Daftarkan resource route di `routes/submission.php`
4. Buat views di `resources/views/` sesuai path route
5. Tambahkan email template di `resources/views/emails/`
6. Daftarkan menu di tabel `side_menus` (via SideMenu model)

### Admin panel baru:
1. Controller di `app/Http/Controllers/Admin/`
2. Route di `routes/admin.php`
3. View di `resources/views/` (ikuti struktur admin yang ada)

---

## 13. File Penting untuk Quick Reference

| Task | File |
|------|------|
| Routing web | [routes/web.php](routes/web.php) |
| Routing submission | [routes/submission.php](routes/submission.php) |
| Routing admin | [routes/admin.php](routes/admin.php) |
| Auth logic | [app/Http/Controllers/Auth/LoginController.php](app/Http/Controllers/Auth/LoginController.php) |
| User model | [app/Models/User.php](app/Models/User.php) |
| Session middleware | [app/Http/Middleware/SessionCheckMiddleware.php](app/Http/Middleware/SessionCheckMiddleware.php) |
| Shared view data | [app/Providers/AppServiceProvider.php](app/Providers/AppServiceProvider.php) |
| Asset build config | [webpack.mix.js](webpack.mix.js) |
| LDAP config | [config/ldap.php](config/ldap.php) |
| DB config | [config/database.php](config/database.php) |
| Master layout | [resources/views/layouts/master.blade.php](resources/views/layouts/master.blade.php) |
