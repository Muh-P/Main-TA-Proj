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

