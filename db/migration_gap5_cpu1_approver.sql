-- =============================================================================
-- MIGRATION: GAP 5 - Register Nofri PN Lintang sebagai CPU 1 Approver (RFC)
-- Proposal: RFC Non-SK Enhancement
-- Date: 2026-06-06
-- =============================================================================
-- INSTRUKSI:
-- 1. Jalankan bagian STEP 1 (SELECT) dulu untuk memverifikasi data sebelum INSERT
-- 2. Konfirmasi employee_id Nofri PN Lintang dari hasil STEP 1
-- 3. Konfirmasi sequence yang tepat dari hasil STEP 1
-- 4. Lanjutkan STEP 2 dan STEP 3 setelah verifikasi
-- =============================================================================

-- -----------------------------------------------------------------------------
-- STEP 1: VERIFIKASI DATA (Jalankan dulu, jangan skip)
-- -----------------------------------------------------------------------------

-- 1a. Cari employee_id Nofri PN Lintang
SELECT id, FullName, LoginName, companycode, department_id
FROM tbl_employee
WHERE FullName LIKE '%Nofri%'
   OR FullName LIKE '%nofri%';

-- 1b. Cek sequence existing RFC approvers (untuk menentukan sequence CPU1)
SELECT a.id, a.sequence, a.approvaltype_id, at.ApprovalType, a.module,
       e.FullName, a.isFinal, a.isActive, a.CompanyList
FROM tbl_approver a
JOIN tbl_approvaltype at ON a.approvaltype_id = at.id
JOIN tbl_employee e ON a.employee_id = e.id
WHERE a.module = 'RFC'
ORDER BY a.sequence;

-- 1c. Cek apakah approvaltype_id=70 sudah ada di tbl_approvaltype dengan module RFC
SELECT id, Module, ApprovalType, remarks, isactive
FROM tbl_approvaltype
WHERE id = 70;

-- 1d. Cek apakah sudah ada row CPU1 (approvaltype_id=70) untuk RFC di tbl_approver
SELECT a.id, a.module, a.employee_id, e.FullName, a.sequence,
       a.approvaltype_id, a.isFinal, a.isActive, a.CompanyList
FROM tbl_approver a
JOIN tbl_employee e ON a.employee_id = e.id
WHERE a.module = 'RFC' AND a.approvaltype_id = 70;

-- -----------------------------------------------------------------------------
-- STEP 2: PASTIKAN approvaltype_id=70 memiliki label yang benar di tbl_approvaltype
-- (Jalankan HANYA jika STEP 1c menunjukkan ApprovalType bukan 'CPU' atau tidak relevan)
-- Catatan: approvaltype_id=70 mungkin sudah digunakan modul lain — UPDATE hati-hati
-- -----------------------------------------------------------------------------

-- UPDATE tbl_approvaltype
-- SET ApprovalType = 'CPU', remarks = 'CPU - Procurement Monitoring RFC Non-SK'
-- WHERE id = 70;

-- Jika id=70 belum ada di tbl_approvaltype, INSERT:
-- INSERT INTO tbl_approvaltype (id, Module, ApprovalType, remarks, isactive)
-- VALUES (70, 'RFC', 'CPU', 'CPU 1 - Procurement Monitoring Non-SK', 1)
-- ON DUPLICATE KEY UPDATE Module = Module; -- tidak overwrite jika sudah ada

-- -----------------------------------------------------------------------------
-- STEP 3: INSERT Nofri PN Lintang sebagai CPU 1 approver RFC
-- Ganti {EMPLOYEE_ID_NOFRI} dengan hasil dari STEP 1a
-- Ganti {SEQUENCE_BETWEEN_BUHEAD_AND_CPU} dengan nilai di antara BU Head dan CPU
-- dari hasil STEP 1b (contoh: jika BU Head=50, CPU=60 → gunakan 55)
-- -----------------------------------------------------------------------------

-- INSERT INTO tbl_approver (module, employee_id, sequence, approvaltype_id, isFinal, isActive, remarks, CompanyList)
-- VALUES (
--     'RFC',
--     {EMPLOYEE_ID_NOFRI},
--     {SEQUENCE_BETWEEN_BUHEAD_AND_CPU},
--     70,
--     0,
--     1,
--     'CPU 1 - Procurement Monitoring Non-SK RFC',
--     'AHL,IHM,KPS,KPA,KF,LDU,KFAHL,KFIHM,KFKPS,KFKPA,KPLP,NKL,KPSI'
-- );

-- -----------------------------------------------------------------------------
-- STEP 4: VERIFIKASI HASIL (Jalankan setelah INSERT)
-- -----------------------------------------------------------------------------

-- SELECT a.id, a.sequence, a.approvaltype_id, at.ApprovalType,
--        e.FullName, a.isFinal, a.isActive, a.CompanyList
-- FROM tbl_approver a
-- JOIN tbl_approvaltype at ON a.approvaltype_id = at.id
-- JOIN tbl_employee e ON a.employee_id = e.id
-- WHERE a.module = 'RFC'
-- ORDER BY a.sequence;
