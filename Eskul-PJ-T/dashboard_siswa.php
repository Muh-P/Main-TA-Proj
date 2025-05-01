<?php
session_start();

// Database connection
include 'koneksi.php'; 

if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true || $_SESSION['role'] !== 'student') {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];

// Ambil informasi murid
$query_murid = "SELECT s.student_id, s.name, k.nama_kelas as class_name, k.tingkat, k.jurusan
                FROM students s
                LEFT JOIN kelas k ON s.kelas_id = k.kelas_id
                WHERE s.user_id = ?";
$stmt = $conn->prepare($query_murid);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result_murid = $stmt->get_result();
$murid = $result_murid->fetch_assoc();

if (!$murid) {
    die("Error: Data murid tidak ditemukan");
}

$student_id = $murid['student_id'];
$student_name = $murid['name'];
$class_info = $murid['tingkat'] . ' ' . $murid['jurusan'] . ' ' . $murid['class_name'];

// Get list of student's eskul first
$query_eskul_list = "SELECT e.eskul_id, e.name, 
                     (SELECT COUNT(*) FROM prestasi WHERE eskul_id = e.eskul_id AND student_id = ?) as prestasi_count
                     FROM eskul_students es
                     JOIN eskul e ON es.eskul_id = e.eskul_id
                     WHERE es.student_id = ?";
$stmt = $conn->prepare($query_eskul_list);
$stmt->bind_param("ii", $student_id, $student_id);
$stmt->execute();
$eskul_list = $stmt->get_result();

$selected_eskul = isset($_GET['view_eskul']) ? intval($_GET['view_eskul']) : null;

// Get student's eskul list with details
$query_eskul = "SELECT e.eskul_id, e.name, 
                (SELECT COUNT(*) FROM attendance a 
                 JOIN eskul_schedule es ON a.schedule_id = es.schedule_id 
                 WHERE es.eskul_id = e.eskul_id AND a.student_id = ? AND a.status = 'Hadir') as total_hadir,
                (SELECT COUNT(*) FROM attendance a 
                 JOIN eskul_schedule es ON a.schedule_id = es.schedule_id 
                 WHERE es.eskul_id = e.eskul_id AND a.student_id = ?) as total_pertemuan
                FROM eskul_students es
                JOIN eskul e ON es.eskul_id = e.eskul_id
                WHERE es.student_id = ?";

$stmt = $conn->prepare($query_eskul);
$stmt->bind_param("iii", $student_id, $student_id, $student_id);
$stmt->execute();
$eskul_result = $stmt->get_result();

// Get student's achievements
$query_prestasi = "SELECT p.*, e.name as eskul_name 
                  FROM prestasi p
                  JOIN eskul e ON p.eskul_id = e.eskul_id
                  WHERE p.student_id = ?
                  ORDER BY p.tanggal DESC";
$stmt = $conn->prepare($query_prestasi);
$stmt->bind_param("i", $student_id);
$stmt->execute();
$prestasi_result = $stmt->get_result();

// Get student's eskul grades
$query_nilai = "SELECT pe.*, e.name as eskul_name, pk.nilai as nilai_keaktifan,
                pr.rekomendasi, pk2.komentar
                FROM penilaian_eskul pe
                JOIN eskul e ON pe.eskul_id = e.eskul_id
                LEFT JOIN penilaian_keaktifan pk ON pe.penilaian_id = pk.penilaian_id
                LEFT JOIN penilaian_rekomendasi pr ON pe.penilaian_id = pr.penilaian_id
                LEFT JOIN penilaian_komentar pk2 ON pe.penilaian_id = pk2.penilaian_id
                WHERE pe.student_id = ?";
$stmt = $conn->prepare($query_nilai);
$stmt->bind_param("i", $student_id);
$stmt->execute();
$nilai_result = $stmt->get_result();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Siswa</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
<div class="navbar">
    <div class="navbar-left">
        <h2>Dashboard Siswa</h2>
    </div>

    <div class="hamburger" onclick="toggleNavbar()">
        <div></div>
        <div></div>
        <div></div>
    </div>

    <div class="navbar-center">
        <ul class="nav-links" id="navLinks">
            <li><a href="dashboard_siswa.php">Home</a></li>
            <li><a href="profil.php">Profil</a></li>
            <li><a href="logout.php" onclick="return confirm('Yakin ingin logout?')">Logout</a></li>
        </ul>
    </div>

    <div class="navbar-right">
        <div class="profile">
            <img src="images/profile-placeholder.png" alt="Profile" class="profile-logo">
            <span class="welcome-message">Welcome, <?php echo htmlspecialchars($student_name); ?>!</span>
        </div>
    </div>
</div>

<link rel="stylesheet" href="css/style-eskul-siswa.css">

<div class="content-wrapper">
    <!-- Eskul Selector -->
    <div class="eskul-selector">
        <h2>Pilih Ekstrakurikuler</h2>
        <div class="eskul-cards">
            <?php while ($eskul = $eskul_list->fetch_assoc()): ?>
                <a href="?view_eskul=<?php echo $eskul['eskul_id']; ?>" 
                   class="eskul-card <?php echo ($selected_eskul == $eskul['eskul_id']) ? 'active' : ''; ?>">
                    <h3><?php echo htmlspecialchars($eskul['name']); ?></h3>
                    <div class="eskul-stats">
                        <span>📅 Jadwal</span>
                        <span>📊 Nilai</span>
                        <span>🏆 <?php echo $eskul['prestasi_count']; ?> Prestasi</span>
                    </div>
                    <div class="view-details">
                        <?php echo ($selected_eskul == $eskul['eskul_id']) ? 'Sedang Dilihat' : 'Lihat Detail'; ?>
                    </div>
                </a>
            <?php endwhile; ?>
        </div>
    </div>

    <?php if ($selected_eskul): ?>
        <!-- Detail Section - Only shown when eskul is selected -->
        <div class="eskul-details">
            <?php
            // Get details for selected eskul
            $stmt = $conn->prepare("SELECT e.name,
                (SELECT COUNT(*) FROM attendance a 
                 JOIN eskul_schedule es ON a.schedule_id = es.schedule_id 
                 WHERE es.eskul_id = e.eskul_id AND a.student_id = ? AND a.status = 'Hadir') as total_hadir,
                (SELECT COUNT(*) FROM attendance a 
                 JOIN eskul_schedule es ON a.schedule_id = es.schedule_id 
                 WHERE es.eskul_id = e.eskul_id AND a.student_id = ?) as total_pertemuan
                FROM eskul e WHERE e.eskul_id = ?");
            $stmt->bind_param("iii", $student_id, $student_id, $selected_eskul);
            $stmt->execute();
            $eskul_detail = $stmt->get_result()->fetch_assoc();
            ?>

            <h2><?php echo htmlspecialchars($eskul_detail['name']); ?></h2>
            
            <!-- Attendance Section -->
            <section class="detail-section">
                <h3>Kehadiran</h3>
                <div class="attendance-info">
                    <div class="stat-card">
                        <span class="stat-value">
                            <?php echo $eskul_detail['total_hadir']; ?>/<?php echo $eskul_detail['total_pertemuan']; ?>
                        </span>
                        <span class="stat-label">Total Kehadiran</span>
                    </div>
                    <div class="stat-card">
                        <span class="stat-value">
                            <?php 
                            $percentage = $eskul_detail['total_pertemuan'] > 0 ? 
                                ($eskul_detail['total_hadir'] / $eskul_detail['total_pertemuan']) * 100 : 0;
                            echo number_format($percentage, 1) . '%';
                            ?>
                        </span>
                        <span class="stat-label">Persentase Kehadiran</span>
                    </div>
                </div>
            </section>

            <!-- Achievements Section -->
            <section class="detail-section">
                <h3>Prestasi</h3>
                <div class="table-responsive">
                    <table class="user-table">
                        <thead>
                            <tr>
                                <th>Eskul</th>
                                <th>Prestasi</th>
                                <th>Tingkat</th>
                                <th>Tanggal</th>
                                <th>Poin</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if ($prestasi_result->num_rows > 0): ?>
                                <?php while ($prestasi = $prestasi_result->fetch_assoc()): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($prestasi['eskul_name']); ?></td>
                                    <td><?php echo htmlspecialchars($prestasi['judul_prestasi']); ?></td>
                                    <td><?php echo htmlspecialchars($prestasi['tingkat']); ?></td>
                                    <td><?php echo date('d/m/Y', strtotime($prestasi['tanggal'])); ?></td>
                                    <td><?php echo htmlspecialchars($prestasi['poin']); ?></td>
                                </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="5" class="text-center">Belum ada prestasi</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </section>

            <!-- Grades Section -->
            <section class="detail-section">
                <h3>Nilai</h3>
                <div class="table-responsive">
                    <table class="user-table">
                        <thead>
                            <tr>
                                <th>Eskul</th>
                                <th>Keaktifan</th>
                                <th>Nilai Akhir</th>
                                <th>Rekomendasi</th>
                                <th>Komentar</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if ($nilai_result->num_rows > 0): ?>
                                <?php while ($nilai = $nilai_result->fetch_assoc()): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($nilai['eskul_name']); ?></td>
                                    <td><?php echo htmlspecialchars($nilai['nilai_keaktifan'] ?? '-'); ?></td>
                                    <td><?php echo htmlspecialchars($nilai['nilai_akhir'] ?? '-'); ?></td>
                                    <td><?php echo htmlspecialchars($nilai['rekomendasi'] ?? '-'); ?></td>
                                    <td><?php echo htmlspecialchars($nilai['komentar'] ?? '-'); ?></td>
                                </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="5" class="text-center">Belum ada penilaian</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </section>
        </div>
    <?php endif; ?>
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

<script type="module" src="js/main.js"></script>
</body>
</html>
