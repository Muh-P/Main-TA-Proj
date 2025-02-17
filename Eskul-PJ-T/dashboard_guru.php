<?php
session_start();
// Cek apakah user sudah login dan memiliki peran sebagai guru
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true || $_SESSION['role'] !== 'teacher') {
    header("Location: login.php");
    exit;
}

// Penghilang Cache
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");

//REG ID
session_regenerate_id(true);

// Database connection
$servername = "localhost";
$username = "root";
$password = "";
$database = "db_eskul_test";

$conn = new mysqli($servername, $username, $password, $database);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Memastikan User Ada Di Session
if (!isset($_SESSION['user_id'])) {
    die("Error: Session user_id tidak ditemukan. Silakan login kembali.");
}

$user_id = $_SESSION['user_id'];

// Ambil informasi guru berdasarkan user_id
$query_teacher = "SELECT teacher_id, name FROM teachers WHERE user_id = ?";
$stmt_teacher = $conn->prepare($query_teacher);
$stmt_teacher->bind_param("i", $user_id);
$stmt_teacher->execute();
$result_teacher = $stmt_teacher->get_result();
$teacher = $result_teacher->fetch_assoc();

if (!$teacher) {
    die("Error: Tidak menemukan data guru untuk user_id " . $user_id);
}

$teacher_id = $teacher['teacher_id'];
$teacher_name = $teacher['name'];

// Ambil parameter pencarian jika ada
$search_query = "";
if (isset($_GET['search'])) {
    $search_query = trim($_GET['search']);
}

// Ambil eskul yang diajar oleh guru ini
$query_eskul = "SELECT e.eskul_id, e.name FROM eskul_teachers et
          JOIN eskul e ON et.eskul_id = e.eskul_id
          WHERE et.teacher_id = ?";
$stmt = $conn->prepare($query_eskul);
$stmt->bind_param("i", $teacher_id);
$stmt->execute();
$eskul_result = $stmt->get_result();

// Mapping ikon & warna eskul
$eskul_icons = [
    "Basketball" => ["🏀", "#ff9800"],
    "Music" => ["🎵", "#2D336B"],
    "Futsal" => ["⚽", "#2196f3"],
    "Drama" => ["🎭", "#9c27b0"],
    "Pencak Silat" => ["🥋", "#f44336"],
    "RoboTech" => ["🤖", "#607d8b"]
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Guru - Eskul Management</title>
    <link rel="stylesheet" href="asset1/style.css">
</head>
<body>
    <div class="navbar">
        <h2>Dashboard Guru</h2>
        <div class="menu">
            <span>Welcome, <?php echo htmlspecialchars($teacher_name); ?>!</span>
            <a href="#" onclick="confirmLogout(event)">Logout</a>
        </div>
    </div>

    <aside id="sidebar">
        <div class="sidebar_content sidebar_head">
            <h1>Guru Menu</h1>
        </div>
        <div class="sidebar_content sidebar_body">
            <nav class="side_navlinks">
                <ul>
                    <li><a href="#">Dashboard</a></li>
                    <li><a href="#">Kelola Eskul</a></li>
                    <li><a href="#" onclick="confirmLogout(event)">Logout</a></li>
                </ul>
            </nav>
        </div>
    </aside>

    <div class="container">
        <h3>Eskul yang Diajar</h3>
        <form method="GET" action="">
            <input type="text" name="search" placeholder="Cari siswa..." value="<?php echo htmlspecialchars($search_query); ?>">
            <button type="submit" class="button">Cari</button>
        </form>
        
        <?php 
        if ($eskul_result->num_rows > 0) {
            while ($row = $eskul_result->fetch_assoc()) {
                $eskul_id = $row['eskul_id'];
                $eskul_name = $row['name'];
                $icon = $eskul_icons[$eskul_name][0] ?? "📌";
                $color = $eskul_icons[$eskul_name][1] ?? "#000";

                echo "<div class='eskul-section' style='border-left: 5px solid $color; padding-left: 10px;'>";
                echo "<h4>$icon " . htmlspecialchars($eskul_name) . "</h4>";

                // Ambil daftar murid dalam eskul ini
                $query_students = "SELECT s.student_id, s.name FROM eskul_students es
                                  JOIN students s ON es.student_id = s.student_id
                                  WHERE es.eskul_id = ?";
                if (!empty($search_query)) {
                    $query_students .= " AND s.name LIKE ?";
                }

                $stmt_students = $conn->prepare($query_students);
                if (!empty($search_query)) {
                    $search_term = "%" . $search_query . "%";
                    $stmt_students->bind_param("is", $eskul_id, $search_term);
                } else {
                    $stmt_students->bind_param("i", $eskul_id);
                }
                $stmt_students->execute();
                $students_result = $stmt_students->get_result();
                
                echo "<table class='user-table'>";
                echo "<thead>
                        <tr>
                            <th>Nama Siswa</th>
                            <th>Aksi</th>
                        </tr>
                      </thead>";
                echo "<tbody>";

                if ($students_result->num_rows > 0) {
                    while ($student = $students_result->fetch_assoc()) {
                        echo "<tr>";
                        echo "<td>🎓 " . htmlspecialchars($student['name']) . "</td>";
                        echo "<td>
                                <a href='kelola_absensi.php?eskul_id=$eskul_id&student_id=" . $student['student_id'] . "' class='button'>Kelola Absensi</a>
                                <a href='kelola_nilai.php?eskul_id=$eskul_id&student_id=" . $student['student_id'] . "' class='button'>Kelola Nilai</a>
                              </td>";
                        echo "</tr>";
                    }
                } else {
                    echo "<tr><td colspan='2' style='color: red;'>⚠ Tidak ada murid ditemukan.</td></tr>";
                }

                echo "</tbody></table>";
                echo "</div><br>";
            }
        } else {
            echo "<p style='color: red;'>⚠ Anda belum mengajar eskul mana pun.</p>";
        }
        ?>
    </div>

    <div class="logout-overlay" id="logoutOverlay">
        <div class="logout-modal">
            <h3>Apakah Anda yakin ingin logout?</h3>
            <div class="modal-buttons">
                <a href="logout.php" class="button">Lanjutkan</a>
                <button class="button" onclick="closeLogoutModal()">Batal</button>
            </div>
        </div>
    </div>

    <script src="asset1/main.js"></script>
    <script>
        function confirmLogout(event) {
            event.preventDefault();
            document.getElementById("logoutOverlay").classList.add("show");
        }
        
        function closeLogoutModal() {
            document.getElementById("logoutOverlay").classList.remove("show");
        }
    </script>
</body>
</html>
