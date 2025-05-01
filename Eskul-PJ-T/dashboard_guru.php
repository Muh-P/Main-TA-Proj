<?php
session_start();

if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true || $_SESSION['role'] !== 'teacher') {
    header("Location: login.php");
    exit;
}

session_regenerate_id(true);

include 'koneksi.php';

if (!isset($_SESSION['user_id'])) {
    die("Error: Session user_id tidak ditemukan. Silakan login kembali.");
}

$user_id = $_SESSION['user_id'];

// Ambil data guru + foto profile (JOIN users)
$query_teacher = "SELECT t.teacher_id, t.name, u.profile_picture
                  FROM teachers t
                  JOIN users u ON t.user_id = u.id
                  WHERE t.user_id = ?";
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
$teacher_photo = !empty($teacher['profile_picture']) ? $teacher['profile_picture'] : 'fotos/profile/default.png';

// Search query
$search_query = "";
if (isset($_GET['search'])) {
    $search_query = trim($_GET['search']);
}

// Ambil eskul yang diajar
$query_eskul = "SELECT e.eskul_id, e.name FROM eskul_teachers et
          JOIN eskul e ON et.eskul_id = e.eskul_id
          WHERE et.teacher_id = ?";
$stmt = $conn->prepare($query_eskul);
$stmt->bind_param("i", $teacher_id);
$stmt->execute();
$eskul_result = $stmt->get_result();

// Ikon eskul
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
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Dashboard Guru - Eskul Management</title>
  <link rel="stylesheet" href="css/style.css">
  <script type="module" src="js/main.js" defer></script>
</head>

<body>

<!-- Navbar -->
<nav class="navbar">
  <div class="navbar-left">
    <h2>Dashboard Guru</h2>
  </div>
  <div class="navbar-right">
    <div class="profile">
      <a href="profil.php">
        <img src="<?php echo htmlspecialchars($teacher_photo); ?>" alt="Profile" class="profile-logo">
      </a>
      <span class="welcome-message">
        Hai! <?php echo htmlspecialchars($teacher_name); ?>, Selamat Datang
      </span>
    </div>
    <button id="menu-btn" class="menu-btn">☰</button>
  </div>
</nav>

<!-- Wrapper -->
<div class="wrapper">

  <!-- Sidebar -->
  <aside id="sidebar" class="sidebar">
    <button id="close-btn" class="close-btn">&times;</button>
    <ul>
      <li><a href="dashboard_guru.php">Dashboard</a></li>
      <li><a href="dashboard-guru-fiture/kelola_eskul.php">Kelola Eskul</a></li>
      <li><a href="profil.php">Profil</a></li>
      <li><a href="#" onclick="confirmLogout(event)">Logout</a></li>
    </ul>
  </aside>

  <!-- Main Content -->
  <main class="container">
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

            echo "<div class='eskul-section' style='border-left: 5px solid $color; padding-left: 10px; margin-bottom: 20px;'>";
            echo "<h4>$icon " . htmlspecialchars($eskul_name) . "</h4>";
            
            // Add button to manage students
            echo "<a href='dashboard-guru-fiture/kelola_siswa.php?eskul_id=$eskul_id' class='button'>Tambah/Kelola Siswa</a>";

            // Ambil daftar siswa
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
            
            echo "<div class='table-responsive'>";
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
                            <a href='dashboard-guru-fiture/kelola_absensi.php?eskul_id=$eskul_id&student_id=" . $student['student_id'] . "' class='button'>Kelola Absensi</a>
                            <a href='dashboard-guru-fiture/kelola_prestasi.php?eskul_id=$eskul_id&student_id=" . $student['student_id'] . "' class='button'>Kelola Prestasi</a>
                            <a href='dashboard-guru-fiture/kelola_keaktifan.php?eskul_id=$eskul_id' class='button'>Kelola Keaktifan</a>
                            <a href='dashboard-guru-fiture/kelola_siswa.php?eskul_id=$eskul_id' class='button'>Kelola Siswa</a>
                          </td>";
                    echo "</tr>";
                }
            } else {
                echo "<tr><td colspan='2' style='color: red;'>⚠ Tidak ada murid ditemukan.</td></tr>";
            }

            echo "</tbody></table>";
            echo "</div>";
            echo "</div>";
        }
    } else {
        echo "<p style='color: red;'>⚠ Anda belum mengajar eskul mana pun.</p>";
    }
    ?>
  </main>

</div>

<!-- Logout Modal -->
<div id="logoutOverlay" class="logout-overlay">
  <div class="logout-modal">
    <h3>Apakah Anda yakin ingin logout?</h3>
    <div class="modal-buttons">
      <a href="logout.php" class="button">Lanjutkan</a>
      <button class="button" onclick="closeLogoutModal()">Batal</button>
    </div>
  </div>
</div>

</body>
</html>
