# Ringkasan Hirarki Approval Module
## Advance · Payment Request · Expense Request

---

## 1. Advance Module (`advancemodule.class.php`)

Modul pengajuan uang muka untuk kebutuhan operasional atau HR.

### Struktur Tabel

```
tbl_advance
  ├── tbl_advancedetail      (rincian item biaya)
  ├── tbl_advanceapproval    (daftar approver yang digenerate)
  ├── tbl_advancehistory     (log aksi approval)
  └── tbl_advanceattachment  (lampiran)
```

### Status

**Request Status (`tbl_advance.requeststatus`):**

| Kode | Keterangan |
|------|-----------|
| 0 | Draft |
| 1 | Waiting for Approval |
| 2 | Need Rework |
| 3 | Approved |
| 4 | Rejected |

**Approval Status per approver (`tbl_advanceapproval.approvalstatus`):**

| Kode | Keterangan |
|------|-----------|
| 0 | Pending |
| 1 | Rework |
| 2 | Approved |
| 3 | Rejected |

### Jenis Form

| Nilai | Nama | Keterangan |
|-------|------|-----------|
| 1 | HR Related | Cuti, tunjangan, kebutuhan HR |
| 2 | Ops Related | Operasional, pembelian langsung |
| 3 | HR Related — HTI HE | Khusus entitas HTI / Hutan Eka |

### Tipe Approval

| ID | Nama | Keterangan |
|----|------|-----------|
| 35 | Dept Head | Superior langsung (dinamis per karyawan) |
| 36 | HRD | HR Director |
| 37 | BUFC | Business Unit Finance Controller |
| 38 | BU HEAD | Business Unit Head |
| 40 | MD | Managing Director |
| 41 | KFFC | Key Finance Finance Committee |
| 42 | PROC | Procurement |
| 44 | HR Verifikator | HR Verifikasi |
| 45 | KF SSL | Key Finance SSL |
| 49 | Superior | Superior khusus (emp_id 789) |
| 61 | FC HO | Finance Controller Head Office |
| 62 | MGR SSL | Manager SSL |
| 63 | APPR COOR | Approver Coordinator HTI HE |
| 64 | APPR HRD HEAD | Approver HRD Head |
| 69 | BUFC 2 | Business Unit Finance Controller 2 |

---

### Alur Pembuatan & Approver Generation

Approver dibangun secara **inkremental** melalui serangkaian API call yang dipicu oleh pilihan user.

```
[1] CREATE advance
      │
      ├─ companycode = 'LDU'  →  tambah BU HEAD (38)
      └─ companycode lain     →  tambah BUFC (37) + BUFC2 (69) jika ada


[2] USER pilih Form Type  (trigger API: 'appform')
      │
      ├─ Form 1 — HR Related
      │    hapus : HRV(44), BUFC(37), MD(40), MGR SSL(62), APPR COOR(63),
      │            KF SSL(45), KFFC(41), BU HEAD(38), APPR HRD HEAD(64)
      │    tambah: HRV(44)  →  BUFC(37)  →  HRD(36)
      │
      ├─ Form 2 — Ops Related
      │    hapus : HRV(44), BUFC(37), MD(40), MGR SSL(62),
      │            KF SSL(45), KFFC(41), HRD(36)
      │    tambah: BUFC(37)
      │
      └─ Form 3 — HR Related HTI HE
           hapus : HRV(44), BU HEAD(38), MD(40), APPR COOR(63), MGR SSL(62),
                   KF SSL(45), KFFC(41), HRD(36), APPR HRD HEAD(64)
           tambah: KFFC(41)  →  MD(40)  →  APPR HRD HEAD(64)  →  APPR COOR(63)


[3] USER pilih Ops Category  (trigger API: 'opscategory')  ← khusus Form 2
      │
      ├─ Cat 1 — General Ops
      │    hapus : KFFC(41), HRV(44), MGR SSL(62), KF SSL(45), BU HEAD(38), PROC(42)
      │    tambah: HRV(44)
      │
      ├─ Cat 2 — Other Ops
      │    hapus : HRV(44), KFFC(41), BU HEAD(38), PROC(42)
      │    tambah: KFFC(41)  [hanya jika companycode ≠ 'LDU']
      │
      ├─ Cat 3
      │    hapus : HRV(44), KFFC(41), BU HEAD(38), PROC(42)
      │    tambah: (tidak ada)
      │
      ├─ Cat 4 — Direct Purchase
      │    hapus : HRV(44), MGR SSL(62), KF SSL(45), BU HEAD(38), KFFC(41)
      │    tambah: HRV(44)
      │
      └─ Cat 5 — SSL
           hapus : HRV(44), MGR SSL(62), KF SSL(45), BU HEAD(38), KFFC(41)
           tambah: MGR SSL(62)  →  KF SSL(45)


[4] USER pilih Budget Status  (trigger API: 'appcon')  ← Form 1 & 2
      │
      │   Logika berdasarkan kombinasi AMOUNT × IS_BUDGETED:
      │
      │   ┌─────────────┬──────────────────────────────┬──────────────────────────────┐
      │   │ Amount      │ isbudgeted = 0  (Budgeted)   │ isbudgeted = 1  (Unbudgeted) │
      │   ├─────────────┼──────────────────────────────┼──────────────────────────────┤
      │   │ < 5 Juta    │ hapus: BU HEAD, FC HO,       │ hapus: BU HEAD, FC HO,       │
      │   │             │        KFFC, PROC             │        KFFC, PROC            │
      │   │             │ tambah: (tidak ada)           │ tambah: (tidak ada)          │
      │   ├─────────────┼──────────────────────────────┼──────────────────────────────┤
      │   │ 5 – 10 Juta │ hapus: semua lama             │ hapus: semua lama            │
      │   │             │ tambah: FC HO(61), KFFC(41)  │ tambah: BU HEAD(38),         │
      │   │             │                               │         FC HO(61), KFFC(41)  │
      │   ├─────────────┼──────────────────────────────┼──────────────────────────────┤
      │   │ ≥ 10 Juta   │ hapus: semua lama             │ hapus: semua lama            │
      │   │             │ tambah: BU HEAD(38),          │ tambah: BU HEAD(38),         │
      │   │             │         FC HO(61), KFFC(41)  │         FC HO(61), KFFC(41)  │
      │   └─────────────┴──────────────────────────────┴──────────────────────────────┘
      │
      └─  PROC (42) ditambahkan jika Form 2 + opscategory = 4 + amount ≥ 5 Juta
```

---

### Approval Completion Logic

Saat approver menyetujui, sistem menentukan apakah request selesai lewat dua mekanisme:

**Mekanisme 1 — Flag `isfinal` di tbl_approver**
Jika approver yang baru menyetujui memiliki `isfinal = 1`, request langsung selesai.
Semua approver pending yang tersisa dihapus otomatis.

**Mekanisme 2 — Cek Explicit (form + budget + amount + approvaltype)**

| Form | isbudgeted | Amount | Approver Terminal | Catatan |
|------|-----------|--------|-------------------|---------|
| 1 | 0 (budgeted) | < 5 Juta | BUFC (37) | Selesai setelah BUFC approve |
| 1 | 0 (budgeted) | 5–10 Juta | KFFC (41) | Selesai setelah KFFC approve |
| 1 | 1 (unbudgeted) | < 5 Juta | BUFC (37) | Selesai setelah BUFC approve |
| 1 | 1 (unbudgeted) | 5–10 Juta | BU HEAD (38) | Selesai setelah BU HEAD approve |
| 2 | 0 (budgeted) | < 5 Juta | BUFC (37) | Selesai setelah BUFC approve |
| 2 | 0 (budgeted) | 5–10 Juta | KFFC (41) | Selesai setelah KFFC approve |
| 2 | 1 (unbudgeted) | < 5 Juta | BUFC (37) | Selesai setelah BUFC approve |
| 3 | 0 (budgeted) | Semua | MD (40) | Selesai setelah MD approve |
| Semua | — | ≥ 10 Juta | — | Hanya via `isfinal=1` |

---

## 2. Payment Request Module (`advpaymentmodule.class.php`)

Modul pengajuan pembayaran. Berdiri sendiri — tidak ada foreign key ke tbl_advance. Referensi ke advance menggunakan field string `advanceno`.

### Struktur Tabel

```
tbl_advpayment
  ├── tbl_advpaymentdetail
  ├── tbl_advpaymentapproval
  ├── tbl_advpaymenthistory
  └── tbl_advpaymentattachment
```

### Jenis Form

| Nilai | Keterangan |
|-------|-----------|
| 1 | HR Related |
| 2 | Ops Related |

### Approval Completion Logic

| Form | Ops Category | Approver Terminal | Mekanisme |
|------|-------------|-------------------|-----------|
| 1 | — | BU HEAD (38) | Cek explicit |
| 2 | Cat 1 | BU HEAD (38) | Cek explicit |
| 2 | Cat lain | Approver `isfinal=1` | Flag isfinal |

**Side effect:** Jika `advanceno` terisi, setelah request disetujui `tbl_advance.isused` di-set ke `1`.

---

## 3. Expense Request Module (`advexpensemodule.class.php`)

Modul klaim / settlement pengeluaran. Juga berdiri sendiri, referensi ke advance via string `advanceno`.

### Struktur Tabel

```
tbl_advexpense
  ├── tbl_advexpensedetail
  ├── tbl_advexpenseapproval
  ├── tbl_advexpensehistory
  └── tbl_advexpenseattachment
```

### Approval Completion Logic

| Kondisi | Mekanisme Selesai |
|---------|------------------|
| Approver dengan `approvaltype_id = 36` (HRD) menyetujui | Otomatis selesai |
| Approver dengan `isfinal = 1` menyetujui | Otomatis selesai |

**Side effect:** Jika `advanceno` terisi, setelah disetujui `tbl_advance.isused` di-set ke `1`.

---

## 4. Hubungan Antar Modul

Ketiga modul **independen secara relasi database** (tidak ada foreign key antar tabel), namun terhubung secara bisnis:

```
┌─────────────────────────────────────────────────────────────────────┐
│                          ALUR BISNIS                                 │
│                                                                       │
│   [Advance]                                                          │
│    Pengajuan uang muka                                               │
│       │                                                              │
│       │ (referensi string advanceno)                                 │
│       ▼                                                              │
│   [Payment Request]          ──OR──    [Expense Request]            │
│    Pengajuan pembayaran                 Klaim pengeluaran            │
│       │                                       │                      │
│       └──────────────────────────────────────┘                      │
│                         │                                            │
│                         ▼                                            │
│               tbl_advance.isused = 1                                 │
│               (advance ditandai "sudah terpakai")                    │
└─────────────────────────────────────────────────────────────────────┘
```

**Catatan penting:**
- Payment Request dan Expense Request bisa dibuat **tanpa** mengisi `advanceno` (standalone)
- Jika `advanceno` diisi, keduanya hanya bisa mereferensi advance yang sudah ada di DB; tidak ada validasi FK otomatis dari ORM
- Urutan bisnis umumnya: Advance → (dipakai) → Payment/Expense → Advance ditandai `isused=1`

---

*Dokumen dibuat: 2026-05-29*
