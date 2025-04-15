<?php

include '../koneksi.php';



$eskul_id = isset($_GET['eskul_id']) ? $_GET['eskul_id'] : null;
$student_id = isset($_GET['student_id']) ? $_GET['student_id'] : null;


if ($eskul_id === null || $student_id === null) {
    die("Invalid parameters. Eskul ID or Student ID is missing.");
}


$stmt = $conn->prepare("SELECT * FROM attendance WHERE eskul_id = ? AND student_id = ?");
$stmt->bind_param("ii", $eskul_id, $student_id);
$stmt->execute();
$attendance_result = $stmt->get_result();

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Kelola Absensi</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <h1>Kelola Absensi: <?php echo htmlspecialchars($student_id); ?></h1>
    <table>
        <thead>
            <tr>
                <th>Tanggal</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            <?php
          
            if ($attendance_result->num_rows > 0) {
                while ($attendance = $attendance_result->fetch_assoc()) {
                    echo "<tr>";
                    echo "<td>" . htmlspecialchars($attendance['date']) . "</td>";
                    echo "<td>" . htmlspecialchars($attendance['status']) . "</td>";
                    echo "</tr>";
                }
            } else {
                echo "<tr><td colspan='2' style='color: red;'>⚠ Tidak ada absensi ditemukan.</td></tr>";
            }
            ?>
        </tbody>
    </table>
</body>
</html>

<?php

$stmt->close();
$conn->close();
?>
