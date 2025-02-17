# Main-TA-Proj
TA PROJECT


# Extracurricular Management System

## Overview
The **Extracurricular Management System** is a web-based application developed using **PHP** and **MySQL** to facilitate the management of extracurricular activities. This system helps administrators, teachers, and students to manage registrations, track attendance, record assessments, and monitor overall participation.

## Features
- **User Authentication:** Secure login system for administrators, teachers, and students.
- **Extracurricular Registration:** Students can browse available activities and register online.
- **Attendance Tracking:** Teachers can mark and review student attendance.
- **Assessment & Grading:** Teachers can evaluate and assign grades for student performance.
- **Dashboard & Reports:** Admins can generate reports on participation, attendance, and grades.
- **Responsive Design:** Works on desktops, tablets, and mobile devices.

---

## ⚙️ Procedures & Triggers in MySQL
| Procedure / Trigger         | Function |
|-----------------------------|-------------------------------------------------------------|
| **AddStudentToEskul**       | Menambahkan siswa ke ekstrakurikuler hanya jika belum terdaftar. |
| **CalculateAttendancePoints** | Menghitung persentase kehadiran & poin eskul berdasarkan jumlah pertemuan. |
| **CalculateAverageGrade**    | Menghitung nilai rata-rata dari semua siswa dalam satu eskul. |
| **MarkStudentExited**       | Mengupdate status siswa menjadi "Exited" saat keluar dari eskul. |
| **AddAttendance**           | Menambahkan absensi hanya jika belum ada di hari yang sama. |
| **trg_update_attendance_date** | Mengupdate tanggal otomatis saat status absensi berubah. |
| **trg_user_activity**       | Menyimpan aktivitas user dalam log setiap kali ada perubahan. |
| **trg_delete_student**      | Menghapus semua data siswa terkait saat akun dihapus. |
| **trg_delete_teacher**      | Menghapus data guru dari eskul saat akun dihapus. |

---


---

# Folders Arrangement
Eskul-PJ-T/
│── index.php                  # Halaman utama
│── config.php                  # Koneksi database
│── dashboard_admin.php         # Dashboard Admin
│── dashboard_guru.php          # Dashboard Guru
│── dashboard_siswa.php         # Dashboard Siswa
│── register_eskul.php          # Pendaftaran ekstrakurikuler
│── logout.php                  # Logout pengguna
│
├── asset1/                     # Folder untuk aset web
│   ├── style.css               # CSS utama
│   ├── main.js                 # JavaScript utama
│   ├── images/                 # Folder untuk gambar
│
├── profil/                     # Fitur Profil Pengguna
│   ├── profil_admin.php        # Profil Admin
│   ├── profil_guru.php         # Profil Guru
│   ├── profil_siswa.php        # Profil Siswa
│   ├── edit_profil.php         # Edit Profil (nama, email, foto, password)
│
├── crud-admin/                 # CRUD Pengguna (Admin)
│   ├── add_user.php            # Tambah Pengguna
│   ├── edit_user.php           # Edit Pengguna
│   ├── delete_user.php         # Hapus Pengguna
│
├── crud-eskul/                 # CRUD Ekstrakurikuler
│   ├── add_eskul.php           # Tambah Eskul
│   ├── edit_eskul.php          # Edit Eskul
│   ├── delete_eskul.php        # Hapus Eskul
│
├── absensi/                     # Manajemen Absensi
│   ├── absensi_eskul.php       # Kelola Absensi
│   ├── laporan_absensi.php     # Laporan Absensi
│
├── penilaian/                   # Manajemen Penilaian Siswa
│   ├── penilaian_eskul.php     # Kelola Penilaian
│   ├── laporan_penilaian.php   # Laporan Penilaian
│
└── database/                    # Database & Backup
    ├── db_eskul_test.sql        # Database MySQL
    ├── backup/                  # Backup Data

---


## Technology Stack
- **Backend:** PHP
- **Database:** MySQL
- **Frontend:** HTML, CSS, JavaScript (Bootstrap for styling)
- **Additional Libraries:** jQuery, AJAX (for dynamic interactions)

## Installation
### Requirements:
- Apache Server (e.g., XAMPP, WAMP, LAMP)
- PHP 7.4+
- MySQL Database

### Steps:
1. Clone the repository:
   ```sh
   [git clone https://github.com/your-repository/extracurricular-management.git](https://github.com/Muh-P/Main-TA-Proj.git)
   ```
2. Move the project folder to your web server directory (e.g., `htdocs` for XAMPP).
3. Create a MySQL database and import the provided SQL file (`database.sql`).
4. Configure the database connection in `config.php`:
   ```php
   define('DB_HOST', 'localhost');
   define('DB_USER', 'root');
   define('DB_PASS', 'yourpassword');
   define('DB_NAME', 'extracurricular_db');
   ```
5. Start your Apache and MySQL services.
6. Access the system via `http://localhost/eskul-pj-t`.

## Usage
- **Admin:** Manage users, activities, and view reports.
- **Teachers:** Mark attendance, evaluate students, and view reports.
- **Students:** Register for activities and check their participation records.

## Future Enhancements
- Integration with email notifications.
- Mobile app support.
- AI-based performance analytics.


## License
This project is licensed under the **MIT License**. Feel free to modify and distribute it.

## Contact
For any inquiries or contributions, contact: [-]

