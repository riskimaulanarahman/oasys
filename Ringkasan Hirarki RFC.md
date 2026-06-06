# Ringkasan Hirarki Lengkap (Semua Kemungkinan Approver)

| approvaltype_id | Nama Approver | Selalu Ada? | Kondisi Khusus |
|-----------------|---------------|-------------|----------------|
| 6               | Dept Head     | Tidak       | Dipilih manual user |
| 11              | BU Head       | Ya          | Per CompanyList |
| 10              | BU FC         | Ya          | Per CompanyList |
| 68              | BU FC 2       | Ya          | Per CompanyList |
| 9               | HR BU         | Tidak       | Company IHM/AHL/KPS/KPA + activity HR |
| 15              | HR KF         | Tidak       | Activity `ishrrelated = 1` |
| 55              | HR Services   | Tidak       | Activity `ishrrelated = 1` |
| 14              | MD            | Tidak       | Rate Type = Non-SK |
| 13              | KF FC         | Tidak       | Rate Type = Non-SK |
| 70              | CPU 1         | Tidak       | Rate Type = Non-SK (Procurement monitoring, input hasil bidding) |
| 12              | CPU           | Tidak       | Rate Type = Non-SK |
| 8               | CAD KF        | Ya          | Semua company; dihapus untuk IHM/AHL saat SK |
| 7               | CAD BU        | Ya          | Per CompanyList |
