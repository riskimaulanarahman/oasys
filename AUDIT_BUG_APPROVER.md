# Audit Bug: Approver Tidak Lengkap / Hilang
## Advance · Payment Request · Expense Request

> Dokumen ini khusus membahas root cause dan analisa teknis isu approver generation pada ketiga modul.

---

## Ringkasan Prioritas

| # | Bug | Severity | Modul Terpengaruh |
|---|-----|----------|-------------------|
| 1 | NULL POINTER `$nAdvanceapproval` setelah last approver approve | **KRITIS** | Advance, Payment, Expense |
| 2 | NULL POINTER saat submit tanpa approver | **KRITIS** | Advance |
| 3 | Approver tidak digenerate jika `isbudgeted = null` & amount ≥ 5 Juta | **TINGGI** | Advance |
| 4 | Form 3 + unbudgeted tidak ditangani di approval action | **TINGGI** | Advance |
| 5 | NULL POINTER pada `$dx` di case 'find' | **MEDIUM** | Advance |
| 6 | Variable shadowing `$data` dalam foreach | **MEDIUM** | Advance, Payment, Expense |
| 7 | `$tdetailamount` tidak diinisialisasi sebelum foreach | **RENDAH** | Advance |

---

## BUG #1 — NULL POINTER pada `$nAdvanceapproval` setelah last approver approve

**Severity: KRITIS**

**Lokasi:**
- [advancemodule.class.php:1835](class/advancemodule.class.php#L1835)
- [advpaymentmodule.class.php:2064](class/advpaymentmodule.class.php#L2064)
- [advexpensemodule.class.php:1205](class/advexpensemodule.class.php#L1205)

**Kode bermasalah (`advancemodule.class.php`):**
```php
// Baris 1823: approver saat ini disimpan (status diubah dari 0 → 2)
$Advanceapproval->save();

// Baris 1835-1836: mencari approver BERIKUTNYA dengan ApprovalStatus=0
$nAdvanceapproval = Advanceapproval::find('first', array(
    'joins'      => $joinx,
    'conditions' => array("advance_id=? and ApprovalStatus=0", $doid),
    'order'      => "tbl_approver.sequence",
    'include'    => array('approver' => array('employee'))
));
$username = $nAdvanceapproval->approver->employee->loginname; // ← FATAL ERROR jika null!
$adb      = Addressbook::find('first', array('conditions' => array("username=?", $username)));
```

**Root Cause:**

Setelah approver terakhir menyimpan aksinya (baris 1823), query di baris 1835 mencari approver berikutnya yang masih pending (`ApprovalStatus=0`). Jika tidak ada lagi approver pending, `$nAdvanceapproval` bernilai `null`. Akses chained `->approver->employee->loginname` pada `null` memicu **PHP Fatal Error**.

**Urutan kejadian yang menyebabkan bug:**

```
1. Approver terakhir klik "Approve"
2. $Advanceapproval->save()  ← OK, approvalstatus = 2, tersimpan di DB
3. Query cari next approver (ApprovalStatus=0)  ← returns NULL
4. $nAdvanceapproval->approver->...  ← FATAL ERROR, PHP abort
5. $Advance->requeststatus = 3 TIDAK PERNAH TERSIMPAN  (baris 2157 tidak tercapai)
6. Email tidak terkirim
```

**Dampak yang tampak oleh user:**
- Approver sudah menekan "Approve" dan aksinya tercatat di histori
- Namun status advance/payment/expense tetap "Waiting for Approval"
- Seolah-olah ada approver yang "hilang" atau aksi tidak berpengaruh

**Skenario pemicu:**
- Advance perusahaan LDU (hanya 1 approver: BU HEAD)
- Approver terakhir dalam rantai menyetujui, semua sebelumnya sudah selesai
- Approver dengan flag `isfinal=1` yang juga merupakan satu-satunya approver pending

**Perbaikan yang disarankan:**
```php
$nAdvanceapproval = Advanceapproval::find('first', array(
    'joins'      => $joinx,
    'conditions' => array("advance_id=? and ApprovalStatus=0", $doid),
    'order'      => "tbl_approver.sequence",
    'include'    => array('approver' => array('employee'))
));

// Tambahkan null guard:
if ($nAdvanceapproval !== null) {
    $username = $nAdvanceapproval->approver->employee->loginname;
    $adb = Addressbook::find('first', array('conditions' => array("username=?", $username)));
} else {
    $adb = null; // Tidak ada approver berikutnya — email akan ke requester
}
```

---

## BUG #2 — NULL POINTER saat Submit Tanpa Approver

**Severity: KRITIS**

**Lokasi:** [advancemodule.class.php:1412](class/advancemodule.class.php#L1412)

**Kode bermasalah:**
```php
// Saat requeststatus diubah dari 0 (draft) → 1 (submitted):
$Advanceapproval = Advanceapproval::find('first', array(
    'joins'      => $joinx,
    'conditions' => array("ApprovalStatus=0 and advance_id=?", $id),
    'order'      => "tbl_approver.sequence",
    'include'    => array('approver' => array('employee'))
));
$username = $Advanceapproval->approver->employee->loginname; // ← FATAL jika null!
$adb      = Addressbook::find('first', array('conditions' => array("username=?", $username)));
```

**Root Cause:**

Jika user membuat advance tetapi tidak melengkapi semua pilihan (form type, ops category, budget status), proses approver generation tidak pernah dipanggil. `tbl_advanceapproval` kosong. Saat submit, query mengembalikan `null` dan terjadi fatal error.

**Skenario pemicu:**
- User membuat advance → langsung submit tanpa memilih form type
- Form type dipilih tetapi budget status tidak dipilih pada Form 2
- Data approver di `tbl_approver` tidak ada yang cocok dengan `CompanyList` karyawan

**Dampak:**
- Advance tetap di status Draft (requeststatus tidak berubah ke 1)
- Email ke approver tidak terkirim
- User tidak menerima pesan error yang informatif

**Perbaikan yang disarankan:**
```php
$Advanceapproval = Advanceapproval::find('first', array(
    'joins'      => $joinx,
    'conditions' => array("ApprovalStatus=0 and advance_id=?", $id),
    'order'      => "tbl_approver.sequence",
    'include'    => array('approver' => array('employee'))
));

// Tambahkan validasi sebelum akses:
if ($Advanceapproval === null) {
    echo json_encode(array("status" => "error", "message" => "Tidak ada approver yang terdaftar. Pastikan form type dan budget status sudah dipilih."));
    return;
}
$username = $Advanceapproval->approver->employee->loginname;
```

---

## BUG #3 — Approver Tidak Digenerate Jika `isbudgeted = null` & Amount ≥ 5 Juta

**Severity: TINGGI**

**Lokasi:** [advancemodule.class.php:305](class/advancemodule.class.php#L305) dan [baris 412](class/advancemodule.class.php#L412)

**Kode bermasalah:**
```php
// Setelah menghapus semua approver lama (BU HEAD, FC HO, KFFC, PROC)...
if ($is_budgeted !== null) {    // ← Jika null, blok ini dilewati seluruhnya!
    if ($is_budgeted == 0) {
        // tambah FC HO(61) + KFFC(41)
    } else if ($is_budgeted == 1) {
        // tambah BU HEAD(38) + FC HO(61) + KFFC(41)
    }
}
// Tidak ada else → jika null, tidak ada yang ditambahkan
```

**Root Cause:**

Saat user memilih budget status, API `appcon` dipanggil. Langkah pertama adalah **menghapus** semua approver upper-level yang ada. Langkah kedua adalah **menambah** approver baru berdasarkan `$is_budgeted`. Jika `$is_budgeted` bernilai `null` (user tidak memilih / nilai tidak terkirim dari frontend), blok penambahan tidak pernah berjalan — advance kehilangan approver upper-level.

**Dampak:**
- Advance dengan amount ≥ 5 Juta hanya memiliki approver dasar (BUFC + HRD)
- Approval level FC HO, KFFC, BU HEAD tidak tersertakan
- Advance bisa disetujui tanpa melalui level approval yang seharusnya

**Perbaikan yang disarankan:**
```php
// Tambahkan else case:
if ($is_budgeted !== null) {
    // ... logic existing
} else {
    // $is_budgeted null — kembalikan approver ke state sebelumnya atau log warning
    error_log("WARNING: isbudgeted null pada advance_id=$id, amount=$tdetailamount");
}
```
Lebih baik lagi: validasi `isbudgeted` di frontend sebelum memperbolehkan submit.

---

## BUG #4 — Form 3 (HTI HE) + `isbudgeted=1` Tidak Ditangani di Approval Action

**Severity: TINGGI**

**Lokasi:** [advancemodule.class.php:2105](class/advancemodule.class.php#L2105)

**Kode bermasalah:**
```php
} else if ($form_type == 3) {
    if ($is_budgeted == 0) {          // Hanya menangani budgeted!
        if (($all) && $approvaltype_id == 40) {
            // mark complete (MD approve)
        } else {
            // pass to next
        }
    }
    // ← TIDAK ADA blok else if ($is_budgeted == 1) untuk Form 3!
}
```

**Root Cause:**

Saat approval action diproses untuk Form 3 dengan `isbudgeted=1` (unbudgeted), tidak ada kondisi yang match. Variabel `$complete` tidak pernah di-set ke `true` dan `$Advance->requeststatus` tidak ditetapkan menjadi 3. Satu-satunya jalan keluar adalah jika approver terakhir memiliki flag `isfinal=1` di `tbl_approver`.

**Dampak:**
- Advance Form 3 + unbudgeted stuck jika tidak ada approver dengan `isfinal=1`
- Approval chain tidak pernah selesai meskipun semua approver sudah menyetujui

**Perbaikan yang disarankan:**
```php
} else if ($form_type == 3) {
    if ($is_budgeted == 0) {
        if (($all) && $approvaltype_id == 40) {
            // mark complete...
            $complete = true;
        } else {
            // pass to next
        }
    } else if ($is_budgeted == 1) {
        // Tambahkan logic untuk unbudgeted Form 3
        // Contoh: selesai setelah MD (40) atau approver tertentu
        if (($all) && $approvaltype_id == 40) {
            $complete = true;
            // set requeststatus = 3, email ke requester
        } else {
            // pass to next
        }
    }
}
```

---

## BUG #5 — NULL POINTER pada `$dx` di Case 'find'

**Severity: MEDIUM**

**Lokasi:** [advancemodule.class.php:1591](class/advancemodule.class.php#L1591)

**Kode bermasalah:**
```php
$dx = Advanceapproval::find('first', array(
    'joins'      => $join,
    'conditions' => array(
        "advance_id=? and tbl_approver.employee_id = ? and ApprovalStatus = 0",
        $query['advance_id'],
        $Employee->id
    ),
    'order'   => 'tbl_approver.sequence',
    'include' => array('approver' => array('employee'))
));
// ... beberapa baris kemudian:
if ($dx->approver->isfinal == 1) { // ← FATAL jika $dx null!
```

**Root Cause:**

`$dx` bisa bernilai null dalam beberapa skenario:
1. User yang login bukan bagian dari approver advance tersebut
2. Approver sudah menyetujui sebelumnya (`ApprovalStatus` bukan 0)
3. Tidak ada pending approval yang di-assign ke user ini

**Dampak:**
- Fatal error saat approver membuka halaman detail approval yang sudah selesai
- Fatal error saat user yang bukan approver membuka URL approval

**Perbaikan:**
```php
if ($dx === null) {
    $data = array("jml" => 0);
} else if ($dx->approver->isfinal == 1) {
    $data = array("jml" => 1);
} else {
    // ... logic existing
}
```

---

## BUG #6 — Variable Shadowing `$data` dalam foreach

**Severity: MEDIUM**

**Lokasi:** [advancemodule.class.php:1877](class/advancemodule.class.php#L1877), pola serupa di advpayment dan advexpense

**Kode bermasalah:**
```php
// $data semula adalah POST data (array):
// $data['approvalstatus'], $data['remarks'], $data['mode'], dll.

// Di dalam case '2' (approve), isfinal=1:
$Advanceapproval = Advanceapproval::find('all', array(...));
foreach ($Advanceapproval as $data) {   // ← $data POST TERTIMPA oleh object
    if ($data->approvalstatus == 0) {
        $data->delete();
    }
}
// Setelah foreach: $data adalah Advanceapproval object, bukan array POST
```

**Root Cause:**

Nama variabel `$data` digunakan untuk dua hal berbeda dalam satu scope: (1) POST data dari request HTTP, dan (2) variabel iterasi foreach. PHP tidak mengenal scoping block, sehingga nilai `$data` dari foreach menimpa nilai POST yang sebelumnya.

**Dampak:**
- Jika ada kode setelah foreach yang mengakses `$data['remarks']` atau key array lain, akan error karena `$data` adalah object
- Bug tersembunyi yang bisa muncul saat kode di sekitarnya dimodifikasi

**Perbaikan:**
```php
// Ganti nama variabel foreach menjadi lebih deskriptif:
foreach ($Advanceapproval as $pendingApproval) {
    if ($pendingApproval->approvalstatus == 0) {
        $logger = new Datalogger(...);
        $logger->SaveData();
        $pendingApproval->delete();
    }
}
// $data tetap berupa array POST setelah foreach
```

---

## BUG #7 — `$tdetailamount` Tidak Diinisialisasi Sebelum foreach

**Severity: RENDAH**

**Lokasi:**
- [advancemodule.class.php:200](class/advancemodule.class.php#L200) — case 'appcon'
- [advancemodule.class.php:524](class/advancemodule.class.php#L524) — case 'appform'
- [advancemodule.class.php:864](class/advancemodule.class.php#L864) — case 'opscategory'

**Kode bermasalah:**
```php
$amountdetail = Advancedetail::find('all', array('conditions' => array("advance_id=?", $Advance->id)));
foreach ($amountdetail as $val) {
    $tdetailamount += $val->amount; // ← $tdetailamount belum diinisialisasi!
}
```

**Root Cause:**

PHP secara implisit menganggap variabel yang belum diinisialisasi bernilai 0 ketika digunakan dalam operasi aritmatika — sehingga biasanya berfungsi benar. Namun, jika variabel ini terbawa dari konteks lain dalam request yang sama (misalnya switch case yang gagal di tengah jalan), nilainya bisa salah.

**Dampak:**
- Threshold amount salah → approver yang di-generate tidak sesuai dengan jumlah sebenarnya
- Advance < 5 Juta bisa diperlakukan seolah >= 5 Juta (atau sebaliknya)

**Perbaikan:**
```php
$tdetailamount = 0; // Tambahkan inisialisasi eksplisit
foreach ($amountdetail as $val) {
    $tdetailamount += $val->amount;
}
```

---

## Rekomendasi Umum

1. **Null guard wajib** sebelum setiap akses ke hasil `find('first')` — terutama yang diikuti chained property access (`->approver->employee->loginname`).

2. **Validasi sebelum submit:** Pastikan minimal 1 approver terdaftar di `tbl_advanceapproval` sebelum mengizinkan perubahan `requeststatus` ke 1. Tampilkan pesan error yang jelas jika tidak ada approver.

3. **Inisialisasi variabel akumulasi:** Selalu inisialisasi `$tdetailamount = 0` sebelum foreach yang menjumlahkan amount.

4. **Lengkapi blok Form 3 + unbudgeted** di approval action dengan logic completion yang eksplisit.

5. **Pisahkan nama variabel** pada foreach dari variabel POST `$data` untuk mencegah shadowing.

6. **Pertimbangkan refactor:** Logika penentuan "apakah approver ini yang terakhir" terduplikasi di case `'find'` dan case `'update'`. Pisahkan ke satu method/fungsi agar konsistensi lebih mudah dijaga.

---

*Dokumen dibuat: 2026-05-29 | Referensi: `advancemodule.class.php`, `advpaymentmodule.class.php`, `advexpensemodule.class.php`*
