# OASys — Codebase Guide

Panduan ini dibuat untuk membantu AI agent memahami arsitektur, pola kode, dan konvensi yang digunakan di proyek OASys tanpa perlu mengeksplorasi ulang dari awal.

## Ikhtisar Proyek

**OASys (Online Approval System)** adalah sistem manajemen persetujuan internal berbasis web untuk karyawan PT Kalimantan Fiber. Sistem ini mendigitalisasi proses pengajuan dan persetujuan yang sebelumnya manual, meliputi:

- Request for Change (RFC)
- Advance & Expense management
- Travel & Assignment (SPKL)
- Training management
- IT Service Requests (email, share folder, equipment)
- Leave & Day-off
- Contract management
- Internal Hiring
- HR master data & Employee management
- Performance management (MMF)

## Stack Teknologi

| Layer | Teknologi |
|-------|-----------|
| Backend | PHP (no framework, custom MVC) |
| ORM | PHP-ActiveRecord |
| Frontend | AngularJS 1.x (SPA) |
| Database | MySQL |
| Auth | JWT (HS256) + LDAP fallback |
| UI Framework | Bootstrap 3 + DevExpress (dx.js) |
| PDF Generation | Html2Pdf |
| Email | PHPMailer |
| Icons | Font Awesome, Ionicons |
| In-browser SQL | AlasQL |

## Struktur Direktori

```
/
├── index.php              # HTML shell utama + bootstrap AngularJS app
├── api.php                # API gateway — routing request ke module class
├── incl/
│   ├── db_conf.php        # Konfigurasi database (GITIGNORED — tidak ada di repo)
│   └── functions.php      # Autoloader, helper functions
├── class/                 # 54 PHP controller classes (business logic)
│   ├── application.class.php   # Base class — semua module extends ini
│   ├── login.class.php         # Autentikasi JWT + LDAP
│   ├── jwtauth.class.php       # JWT encode/decode/checkAuth
│   ├── datalogger.class.php    # Audit trail
│   └── *module.class.php       # Satu file per fitur/modul
├── model/                 # 110 PHP ActiveRecord model files
│   └── *.php              # Satu file per tabel database
├── template/              # 79 AngularJS HTML template files
│   └── *.html             # Satu file per halaman/route
├── js/
│   ├── app.js             # AngularJS module + 79 route definitions
│   ├── factory.js         # AuthenticationService + CrudService
│   ├── services.js        # Utility services
│   ├── filter.js          # Custom AngularJS filters
│   ├── directive.js       # Custom directives
│   └── controllers/       # 79 AngularJS controller files
│       └── *.js           # Satu file per halaman/route
├── css/                   # 30 CSS files (Bootstrap, DevExpress, custom)
├── assets/                # Images, fonts, static assets
├── dx/                    # DevExpress library files
├── lib/                   # Icon font libraries
├── upload/                # User file uploads (GITIGNORED)
└── test/                  # Manual test scripts
```

## Arsitektur Sistem

```
Browser (AngularJS SPA)
    │
    │  HTTP POST /api/api{module}
    │  Header: Authorization: Bearer <jwt>
    │  Body: { criteria: "...", data: {...} }
    ▼
api.php  ←── routing berdasarkan action parameter
    │
    ▼
/class/{module}.class.php  (extends Application)
    │  checkAuth() via jwtauth
    │
    ▼
/model/*.php  (extends ActiveRecord\Model)
    │
    ▼
MySQL Database (tbl_*)
    │
    ▼
JSON Response → AngularJS controller → DevExpress DataGrid / UI
```

---

## Pola Backend (PHP)

### Base Class: Application

Semua module controller extends `Application` (`class/application.class.php`). Base class menyediakan:
- Inisialisasi koneksi database via ActiveRecord
- Instance JWT auth (`$this->jwt`)
- Instance mailer (`$this->mail`)
- Magic methods untuk property access
- `$this->post` dan `$this->get` sebagai shortcut

### Membuat Controller Baru

File: `class/{namafitur}module.class.php`

```php
<?php
defined('Po3nX') or die('No Direct access');

class NamafiturModule extends Application {

    public function __construct() {
        parent::__construct();
        $this->get  = isset($this->get)  ? $this->get  : $_GET;
        $this->post = isset($this->post) ? $this->post : $_POST;

        switch ($this->get['action']) {
            case 'apinamafitur':
                $this->namafiturManager();
                break;
        }
    }

    public function namafiturManager() {
        if (!count($this->post)) {
            http_response_code(405);
            echo json_encode(["message" => "Method not Allowed"]);
            return;
        }

        $auth = $this->jwt->checkAuth();
        if (!$auth) return; // checkAuth sudah echo JSON error + set HTTP code

        switch ($this->post['criteria']) {
            case 'all':
                $data = Namafitur::all();
                echo json_encode($data);
                break;

            case 'byid':
                $data = Namafitur::find($this->post['id']);
                echo json_encode($data);
                break;

            case 'create':
                $d = $this->post['data'];
                $data = new Namafitur();
                foreach ($d as $key => $val) {
                    $data->$key = $val;
                }
                $data->save();
                echo json_encode($data);
                break;

            case 'update':
                $data = Namafitur::find($this->post['id']);
                foreach ($this->post['data'] as $key => $val) {
                    $val = ($val == 'false') ? false : (($val == 'true') ? true : $val);
                    $data->$key = $val;
                }
                $data->save();
                echo json_encode($data);
                break;

            case 'delete':
                $data = Namafitur::find($this->post['id']);
                $data->delete();
                echo json_encode(["message" => "Deleted"]);
                break;
        }
    }
}
```

### Membuat Model Baru

File: `model/Namafitur.php`

```php
<?php
class Namafitur extends ActiveRecord\Model {
    static $table_name = 'tbl_namafitur';

    // Relasi belongs_to (foreign key default: {class}_id)
    static $belongs_to = array(
        array('employee'),
        // Custom foreign key:
        array('creator', 'class_name' => 'Employee', 'foreign_key' => 'createdby'),
    );

    // Relasi has_many
    static $has_many = array(
        array('namafiturdetail'),
        array('namafiturhistory'),
    );
}
```

Konvensi nama tabel: selalu prefix `tbl_` + nama lowercase (contoh: `tbl_rfc`, `tbl_advance`, `tbl_employee`).

### Mendaftarkan Module di api.php

Tambahkan di array `$modulelist` di `api.php` dan tambahkan case di switch action-nya:

```php
// Di array $modulelist:
'apinamafitur'

// Di switch routing api.php (jika belum menggunakan dynamic load):
case 'apinamafitur':
    require_once MYCLASS . DS . 'namafiturmodule.class.php';
    $obj = new NamafiturModule();
    break;
```

### Format Response JSON

```php
// Success dengan data
echo json_encode($data);                         // single record atau array

// Success dengan pesan
echo json_encode(["message" => "Berhasil disimpan", "status" => "success"]);

// Error aplikasi
echo json_encode(["status" => "error", "message" => "Deskripsi error"]);

// Auth error (sudah ditangani checkAuth(), tapi referensi:)
// HTTP 401 + { "status": "autherror", "message": "Access denied.", "error": "..." }
```

HTTP status codes yang dipakai:
- `200` — Success (default)
- `400` — Bad Request (input tidak lengkap)
- `401` — Unauthorized (token invalid/expired)
- `405` — Method Not Allowed (tidak ada POST body)
- `413` — File Too Large
- `415` — Unsupported Media Type

### Audit Trail

```php
// Panggil Datalogger setelah operasi tulis
$log = new Datalogger();
$log->username = $decoded->username; // dari JWT
$log->module   = 'namafitur';
$log->action   = 'create';
$log->olddata  = json_encode([]);
$log->newdata  = json_encode($data->to_array());
$log->SaveData();
```

### Email Notification

```php
$mail = new PHPMailer;
$mail->isSMTP();
$mail->Host     = SMTPSERVER;
$mail->Port     = 25;
$mail->From     = MAILFROM;
$mail->FromName = 'OASys';
$mail->addAddress($recipientEmail);
$mail->Subject  = 'Subject Email';
$mail->Body     = $this->mailbody; // HTML string
$mail->isHTML(true);
$mail->send();
```

### PDF Generation

```php
$html2pdf = new Html2Pdf('P', 'A4', 'id');
$html2pdf->writeHTML($htmlContent);
$html2pdf->output('filename.pdf', 'D'); // D = download
```

---

## Pola Frontend (AngularJS)

### Mendaftarkan Route Baru di js/app.js

```javascript
// Di dalam konfigurasi $stateProvider atau RouteProvider:
$routeProvider
    .when('/namafitur', RouteProvider.resolve('namafitur'))
    // ...
```

Template dan controller di-load secara lazy:
- Template: `template/namafitur.html?v=6.36`
- Controller: `js/controllers/namafitur.js?v=6.36`

Cache-bust via versi `v=6.36` — update angka ini jika perlu force-refresh.

### Template HTML

File: `template/namafitur.html`

```html
<div ng-controller="namafiturCtrl as nm">
    <div class="mb3 card">
        <div class="card-header-tab card-header">
            <div class="card-header-title font-size-lg">
                <i class="header-icon fa fa-list"></i>
                <span>Nama Fitur</span>
            </div>
        </div>
        <div class="card-body">
            <div ng-include src="'template/panel.html'"></div>
        </div>
    </div>
</div>
```

Template menggunakan `ng-include` ke `template/panel.html` yang berisi definisi DevExpress DataGrid. Untuk halaman detail/form, buat HTML langsung di template.

### AngularJS Controller

File: `js/controllers/namafitur.js`

```javascript
app.controller('namafiturCtrl', ['$scope', '$rootScope', 'CrudService', '$location',
function($scope, $rootScope, CrudService, $location) {

    // Load data saat controller aktif
    $scope.loadData = function() {
        CrudService.GetAll('namafitur').then(function(response) {
            if (response.data.status === 'error') {
                DevExpress.ui.notify(response.data.message, 'error', 3000);
            } else {
                $scope.dataSource = response.data;
            }
        });
    };

    // Create
    $scope.save = function(data) {
        CrudService.Create('namafitur', data).then(function(response) {
            DevExpress.ui.notify('Data berhasil disimpan', 'success', 2000);
            $scope.loadData();
        });
    };

    // Update
    $scope.update = function(id, data) {
        CrudService.Update('namafitur', id, data).then(function(response) {
            DevExpress.ui.notify('Data berhasil diupdate', 'success', 2000);
        });
    };

    // Delete
    $scope.remove = function(id) {
        CrudService.Delete('namafitur', id).then(function(response) {
            $scope.loadData();
        });
    };

    // Init
    $scope.loadData();
}]);
```

### CrudService — Semua API Call

Didefinisikan di `js/factory.js`. Selalu gunakan service ini, jangan panggil `$http` langsung.

```javascript
CrudService.GetAll('namafitur')              // criteria: 'all'
CrudService.GetById('namafitur', id)         // criteria: 'byid'
CrudService.FindData('namafitur', query)     // criteria: 'find'
CrudService.Create('namafitur', data)        // criteria: 'create'
CrudService.Update('namafitur', id, data)    // criteria: 'update'
CrudService.Delete('namafitur', id)          // criteria: 'delete'
CrudService.checkAccess('namafitur', username)
```

URL endpoint yang dibentuk: `api/apinamafitur` (prefix `api` + nama module).

### AuthenticationService

```javascript
// Cek apakah user sudah login
AuthenticationService.isAuthed()

// Login
AuthenticationService.Login(username, password, callback)

// Perbarui token
AuthenticationService.renewToken()

// Decode JWT (untuk ambil info user)
var decoded = AuthenticationService.parseJwt($localStorage.currentUser.token);
// decoded.id, decoded.username, decoded.isadmin, decoded.email, dst.
```

Data user aktif tersimpan di `$localStorage.currentUser.token`.

### DevExpress DataGrid

Pattern standar untuk menampilkan tabel data:

```javascript
$scope.gridOptions = {
    dataSource: $scope.dataSource,
    columns: [
        { dataField: 'id', caption: 'ID', width: 60 },
        { dataField: 'name', caption: 'Nama' },
        { dataField: 'status', caption: 'Status' },
    ],
    paging: { pageSize: 20 },
    searchPanel: { visible: true },
    headerFilter: { visible: true },
    export: { enabled: true, fileName: 'NamaFitur' },
};
```

---

## Daftar Modul & Endpoint API

| Modul | Class File | Endpoint API |
|-------|-----------|-------------|
| Login | `login.class.php` | `api/login` |
| User | `usermanager.class.php` | `api/apiuser` |
| Role | `usermanager.class.php` | `api/apirole` |
| Employee | `employeemodule.class.php` | `api/apiemployee` |
| Company | `companymodule.class.php` | `api/apicompany` |
| Department | `departmentmodule.class.php` | `api/apidepartment` |
| Division | *(divisionmodule)* | `api/apidivision` |
| Designation | `designationmodule.class.php` | `api/apidesignation` |
| Grade | `grademodule.class.php` | `api/apigrade` |
| Location | `locationmodule.class.php` | `api/apilocation` |
| Level | `levelmodule.class.php` | `api/apilevel` |
| Approver | `approvermodule.class.php` | `api/apiapprover` |
| Holiday | *(holidaymodule)* | `api/apiholiday` |
| RFC | `rfcmodule.class.php` | `api/apirfc`, `api/apirfcdetail`, `api/apirfcapp`, dll |
| Contract | `contractmodule.class.php` | `api/apicontract`, `api/apicontractreg` |
| Advance | `advancemodule.class.php` | `api/apiadvance`, `api/apiadvancedetail`, dll |
| Advance Payment | `advpaymentmodule.class.php` | `api/apiadvpayment` |
| Advance Expense | `advexpensemodule.class.php` | `api/apiadvexpense` |
| Day-off | `dayoffmodule.class.php` | `api/apidayoff` |
| Leave | `leavemodule.class.php` | `api/apileave` |
| Travel (SPKL) | `spklmodule.class.php` | `api/apispkl` |
| Training (TR) | `trmodule.class.php` | `api/apitr` |
| MMF (Performance) | `mmfmodule.class.php` | `api/apimmf` |
| MMF30 | `mmf30module.class.php` | `api/apimmf30` |
| IT Mail | `itimailmodule.class.php` | `api/apiitmail` |
| IT Share Folder | `itsharefoldermodule.class.php` | `api/apiitsharef` |
| IT Equipment | `iteiemodule.class.php` | `api/apiiteie` |
| IT Network Access | `itinetaccessmodule.class.php` | `api/apiitinetaccess` |
| Internal Hiring | `internalhiringmodule.class.php` | `api/apiinternalhiring` |
| RFC Activity | `rfcactivitymodule.class.php` | `api/apirfcactivity` |
| RFC Contractor | `rfccontractormodule.class.php` | `api/apirfccontractor` |
| Module Manager | `modulemanager.class.php` | `api/apimodule` |
| Currency | `currencymodule.class.php` | `api/apicurrency` |
| Expense Type | `expensetypemodule.class.php` | `api/apiexpensetype` |
| Dashboard | `dashboardmodule.class.php` | `api/apidashboard` |

---

## Konvensi & Aturan Kode

### Penamaan File

| Tipe | Konvensi | Contoh |
|------|----------|--------|
| PHP Controller | `{namamodule}module.class.php` | `rfcmodule.class.php` |
| PHP Model | `{NamaModel}.php` (PascalCase) | `Rfcapproval.php` |
| Tabel DB | `tbl_{namamodel}` (lowercase) | `tbl_rfcapproval` |
| AngularJS Controller | `{namaroute}.js` | `rfc.js`, `detailrfc.js` |
| HTML Template | `{namaroute}.html` | `rfc.html`, `detailrfc.html` |
| CSS class | Bootstrap/DevExpress class + custom di `css/style.css` |

### Error Handling

- Selalu set HTTP response code sebelum `echo json_encode()`
- Gunakan try-catch untuk operasi DB kritis dan log ke `Errorlog`
- Frontend: gunakan `DevExpress.ui.notify(message, 'error'/'success', duration)` untuk feedback user
- Jangan expose stack trace ke client

### Keamanan

- Semua endpoint API wajib panggil `$this->jwt->checkAuth()` — jangan skip
- File `incl/db_conf.php` TIDAK ada di repository (gitignored) — setup manual di server
- Konstanta `Po3nX` wajib ada di setiap file PHP: `defined('Po3nX') or die('No Direct access');`
- Upload file: simpan di `/upload/`, tidak di-serve langsung tanpa validasi

---

## Konfigurasi & Konstanta

Konstanta didefinisikan di `incl/db_conf.php` (tidak ada di repo, harus dibuat manual di server):

```php
define('DB_HOST',     'localhost');
define('DB_USER',     'username');
define('DB_PASSWORD', 'password');
define('DB_NAME',     'database_name');
define('SKEY',        'jwt_secret_key');
define('LDAP_SERVER', 'ldap://domain.com');
define('DOMAIN',      '@domain.com');
define('MAILFROM',    'noreply@domain.com');
define('SMTPSERVER',  'smtp.domain.com');
define('MAINTENANCE', false);
```

Konstanta path didefinisikan di `api.php`:
- `SITE_PATH` — root directory
- `DS` — directory separator
- `MYINC` — path ke `incl/`
- `MODEL` — path ke `model/`
- `MYCLASS` — path ke `class/`
- `Po3nX` — security flag

Timezone: `Asia/Makassar` (diset di `api.php`).

---

## Panduan Menambah Fitur Baru

Checklist lengkap saat membuat modul baru dari awal:

### Backend
- [ ] Buat tabel database: `tbl_{namamodule}` dan tabel pendukung (approval, history, attachment jika perlu)
- [ ] Buat model: `model/{NamaModule}.php` (extends ActiveRecord\Model, definisi relasi)
- [ ] Buat controller: `class/{namamodule}module.class.php` (extends Application, switch criteria)
- [ ] Daftarkan action endpoint di `api.php`

### Frontend
- [ ] Buat template: `template/{namaroute}.html`
- [ ] Buat AngularJS controller: `js/controllers/{namaroute}.js`
- [ ] Daftarkan route di `js/app.js` (RouteProvider.resolve)
- [ ] Tambahkan menu/link navigasi di `index.php` jika diperlukan

### Approval Workflow (jika modul memerlukan approval)
- [ ] Buat tabel `tbl_{namamodule}approval` dan `tbl_{namamodule}history`
- [ ] Buat model `{NamaModule}approval.php` dan `{NamaModule}history.php`
- [ ] Implementasi logika approval di controller (multi-level via tabel approver)
- [ ] Tambahkan notifikasi email ke approver saat request dibuat
- [ ] Tambahkan route approval: `template/{namaroute}approval.html` + `js/controllers/{namaroute}approval.js`

### File Attachment (jika ada upload)
- [ ] Buat tabel `tbl_{namamodule}attachment`
- [ ] Buat model `{NamaModule}attachment.php`
- [ ] Implementasi upload handler di controller (simpan ke `/upload/{namamodule}/`)
- [ ] Tambahkan endpoint `upload{namamodule}file` di controller dan `api.php`

### Laporan (jika ada halaman report)
- [ ] Buat `template/{namaroute}report.html`
- [ ] Buat `js/controllers/{namaroute}report.js`
- [ ] Daftarkan route report di `js/app.js`
- [ ] Implementasi endpoint export PDF di controller

---

## Tips untuk AI Agent

1. **Saat membaca controller yang ada** — lihat `class/advancemodule.class.php` atau `class/rfcmodule.class.php` sebagai referensi modul kompleks dengan approval workflow.

2. **Saat membaca model yang ada** — lihat `model/Advance.php`, `model/Rfc.php`, atau `model/Approver.php` sebagai referensi.

3. **Pola approval workflow** — semua modul yang punya approval mengikuti pola: request dibuat → status "pending" → notifikasi email ke approver → approver approve/reject → update status → simpan history.

4. **Nama tabel approval** — selalu mengikuti pola `tbl_{namamodule}approval`, `tbl_{namamodule}history`, `tbl_{namamodule}attachment`.

5. **Tidak ada migration file** — DDL database tidak ditrack di repository. Schema harus dilihat dari definisi model dan query di controller.

6. **Version cache busting** — setiap kali mengubah file JS atau HTML, update versi di `js/app.js` (`var v = '6.xx'`).

7. **DevExpress DataGrid** — komponen UI utama untuk tabel data. Lihat file controller yang ada untuk contoh konfigurasi `gridOptions`.

8. **CrudService vs $http langsung** — SELALU gunakan `CrudService` dari `js/factory.js`, jangan panggil `$http` langsung di controller kecuali untuk upload file atau operasi khusus.
