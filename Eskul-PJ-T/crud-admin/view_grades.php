<?php
session_start();
if (!isset($_SESSION['logged_in']) || $_SESSION['role'] != 'admin') {
    header("Location: ../login.php");
    exit;
}

include '../koneksi.php';

// Ambil data filter
$tahunFilter = $_GET['tahun_ajaran'] ?? '';
$semesterFilter = $_GET['semester'] ?? '';
$namaSiswaFilter = $_GET['nama_siswa'] ?? '';
$namaEskulFilter = $_GET['nama_eskul'] ?? '';
$export = isset($_GET['export']) ? true : false;

// Base query updated to match database schema
$query = "
SELECT 
    pe.*,
    s.name AS nama_siswa,
    e.name AS nama_eskul,
    pk.komentar,
    pr.rekomendasi,
    pkf.nilai AS nilai_keaktifan
FROM penilaian_eskul pe
JOIN students s ON pe.student_id = s.student_id
JOIN eskul e ON pe.eskul_id = e.eskul_id
LEFT JOIN penilaian_komentar pk ON pe.penilaian_id = pk.penilaian_id
LEFT JOIN penilaian_rekomendasi pr ON pe.penilaian_id = pr.penilaian_id
LEFT JOIN penilaian_keaktifan pkf ON pe.penilaian_id = pkf.penilaian_id
WHERE 1
";

if (!empty($tahunFilter)) $query .= " AND pe.tahun_ajaran = '$tahunFilter'";
if (!empty($semesterFilter)) $query .= " AND pe.semester = '$semesterFilter'";
if (!empty($namaSiswaFilter)) $query .= " AND s.name LIKE '%$namaSiswaFilter%'";
if (!empty($namaEskulFilter)) $query .= " AND e.name LIKE '%$namaEskulFilter%'";

$query .= " ORDER BY pe.tahun_ajaran DESC, pe.semester ASC, s.name ASC";

// Untuk Excel atau untuk tampilan tabel
if ($export) {
    $result_export = $conn->query($query);

    header("Content-Type: application/vnd.ms-excel");
    header("Content-Disposition: attachment; filename=Data_Nilai_Eskul.xls");

    echo "<table border='1' style='border-collapse: collapse; table-layout: fixed; width: 100%;'>";
    echo "<thead>";
    echo "<tr style='background-color: #FFA500; color: white;'>";
    echo "<th>Nama Siswa</th><th>Eskul</th><th>Semester</th><th>Tahun Ajaran</th>
          <th>Nilai Keaktifan</th><th>Nilai Akhir</th><th>Rekomendasi</th><th>Komentar</th>";
    echo "</tr>";
    echo "</thead><tbody>";

    while ($row = $result_export->fetch_assoc()) {
        echo "<tr style='text-align: center;'>";
        echo "<td>".htmlspecialchars($row['nama_siswa'])."</td>";
        echo "<td>".htmlspecialchars($row['nama_eskul'])."</td>";
        echo "<td>".htmlspecialchars($row['semester'])."</td>";
        echo "<td>".htmlspecialchars($row['tahun_ajaran'])."</td>";
        echo "<td>".htmlspecialchars($row['nilai_keaktifan'] ?? 'Belum dinilai')."</td>";
        echo "<td>".htmlspecialchars($row['nilai_akhir'] ?? 'Belum dinilai')."</td>";
        echo "<td>".htmlspecialchars($row['rekomendasi'] ?? 'Belum ada')."</td>";
        echo "<td style='text-align:left;'>".htmlspecialchars($row['komentar'] ?? 'Belum ada komentar')."</td>";
        echo "</tr>";
    }
    echo "</tbody></table>";
    exit;
} else {
    // untuk view biasa
    $result = $conn->query($query);
}

// Query filter tahun dan eskul untuk select option
$tahunResult = $conn->query("SELECT DISTINCT tahun_ajaran FROM penilaian_eskul ORDER BY tahun_ajaran DESC");
$eskulResult = $conn->query("SELECT name FROM eskul ORDER BY name ASC");
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>View Nilai - Admin Eskul</title>
    <link rel="stylesheet" href="../css/style.css">
    <link rel="stylesheet" href="../css/view-grades.css"> 
    <script type="module" src="../js/main.js"></script>
</head>

<body>
<?php include 'partials/navbar_admin.php'; ?>
<?php include 'partials/sidebar_admin.php'; ?>

<div class="container">
    <h2>Data Penilaian Eskul Siswa</h2>

    <form method="GET" action="" class="filter-form">
        <label for="tahun_ajaran">Tahun Ajaran:</label>
        <select name="tahun_ajaran" id="tahun_ajaran">
            <option value="">Semua</option>
            <?php while ($tahun = $tahunResult->fetch_assoc()): ?>
                <option value="<?= htmlspecialchars($tahun['tahun_ajaran']) ?>" <?= ($tahunFilter == $tahun['tahun_ajaran']) ? 'selected' : '' ?>>
                    <?= htmlspecialchars($tahun['tahun_ajaran']) ?>
                </option>
            <?php endwhile; ?>
        </select>

        <label for="semester">Semester:</label>
        <select name="semester" id="semester">
            <option value="">Semua</option>
            <option value="Ganjil" <?= ($semesterFilter == 'Ganjil') ? 'selected' : '' ?>>Ganjil</option>
            <option value="Genap" <?= ($semesterFilter == 'Genap') ? 'selected' : '' ?>>Genap</option>
        </select>

        <label for="nama_eskul">Nama Eskul:</label>
        <select name="nama_eskul" id="nama_eskul">
            <option value="">Semua</option>
            <?php while ($eskul = $eskulResult->fetch_assoc()): ?>
                <option value="<?= htmlspecialchars($eskul['name']) ?>" <?= ($namaEskulFilter == $eskul['name']) ? 'selected' : '' ?>>
                    <?= htmlspecialchars($eskul['name']) ?>
                </option>
            <?php endwhile; ?>
        </select>

        <label for="nama_siswa">Nama Siswa:</label>
        <input type="text" name="nama_siswa" value="<?= htmlspecialchars($namaSiswaFilter) ?>" placeholder="Cari siswa">

        <button type="submit" class="button">Filter</button>
        <a href="?<?= http_build_query($_GET) ?>&export=1" class="button">Export ke Excel</a>
    </form>

    <!-- Tabel Data -->
    <div class="table-responsive">
    <table class="view-grades-table">
        <thead>
            <tr>
                <th>Nama Siswa</th>
                <th>Eskul</th>
                <th>Semester</th>
                <th>Tahun Ajaran</th>
                <th>Nilai Keaktifan</th>
                <th>Nilai Akhir</th>
                <th>Rekomendasi</th>
                <th>Komentar</th>
            </tr>
        </thead>
        <tbody>
            <?php if ($result->num_rows > 0): ?>
                <?php while ($row = $result->fetch_assoc()): ?>
                    <tr>
                        <td><?= htmlspecialchars($row['nama_siswa']) ?></td>
                        <td><?= htmlspecialchars($row['nama_eskul']) ?></td>
                        <td><?= htmlspecialchars($row['semester']) ?></td>
                        <td><?= htmlspecialchars($row['tahun_ajaran']) ?></td>
                        <td><?= htmlspecialchars($row['nilai_keaktifan'] ?? 'Belum dinilai') ?></td>
                        <td><?= htmlspecialchars($row['nilai_akhir'] ?? 'Belum dinilai') ?></td>
                        <td><?= htmlspecialchars($row['rekomendasi'] ?? 'Belum ada') ?></td>
                        <td><?= htmlspecialchars($row['komentar'] ?? 'Belum ada komentar') ?></td>
                    </tr>
                <?php endwhile; ?>
            <?php else: ?>
                <tr><td colspan="8">Tidak ada data ditemukan.</td></tr>
            <?php endif; ?>
        </tbody>
    </table>
    </div>

</div>

</body>
</html>
