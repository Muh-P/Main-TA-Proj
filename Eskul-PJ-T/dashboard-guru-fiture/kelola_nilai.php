<?php

include '../koneksi.php';

$eskul_id = isset($_GET['eskul_id']) ? $_GET['eskul_id'] : null;
$student_id = isset($_GET['student_id']) ? $_GET['student_id'] : null;


if ($eskul_id === null || $student_id === null) {
    die("Invalid parameters. Eskul ID or Student ID is missing.");
}


$student_stmt = $conn->prepare("SELECT name FROM students WHERE student_id = ?");
$student_stmt->bind_param("i", $student_id); 
$student_stmt->execute();
$student_result = $student_stmt->get_result();

if ($student_result->num_rows > 0) {
    $student = $student_result->fetch_assoc();
    $student_name = $student['name'];
} else {
    $student_name = "Unknown Student"; 
}


$grade_stmt = $conn->prepare("SELECT subject, grade FROM grades WHERE eskul_id = ? AND student_id = ?");
$grade_stmt->bind_param("ii", $eskul_id, $student_id); 
$grade_stmt->execute();
$grades_result = $grade_stmt->get_result();

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Nilai - <?php echo htmlspecialchars($student_name); ?></title>
    <link rel="stylesheet" href="../css/style.css"> 
    <style>
       
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f9;
            margin: 0;
            padding: 0;
        }
        .container {
            width: 80%;
            margin: auto;
            background-color: #fff;
            padding: 20px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }
        h1 {
            text-align: center;
            color: #333;
        }
        table {
            width: 100%;
            margin-top: 20px;
            border-collapse: collapse;
        }
        th, td {
            padding: 10px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }
        th {
            background-color: #f2f2f2;
        }
        tr:hover {
            background-color: #f9f9f9;
        }
        .button {
            display: inline-block;
            padding: 10px 20px;
            margin: 10px 0;
            background-color: #4CAF50;
            color: white;
            text-decoration: none;
            border-radius: 5px;
        }
        .button:hover {
            background-color: #45a049;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Kelola Nilai untuk: <?php echo htmlspecialchars($student_name); ?> (Eskul ID: <?php echo htmlspecialchars($eskul_id); ?>)</h1>

        <table>
            <thead>
                <tr>
                    <th>Mata Pelajaran</th>
                    <th>Nilai</th>
                </tr>
            </thead>
            <tbody>
                <?php
      
                if ($grades_result->num_rows > 0) {
                    while ($grade = $grades_result->fetch_assoc()) {
                        echo "<tr>";
                        echo "<td>" . htmlspecialchars($grade['subject']) . "</td>";
                        echo "<td>" . htmlspecialchars($grade['grade']) . "</td>";
                        echo "</tr>";
                    }
                } else {
                    echo "<tr><td colspan='2' style='color: red;'>⚠ Tidak ada nilai ditemukan.</td></tr>";
                }
                ?>
            </tbody>
        </table>

        <div style="text-align: center;">
            <a href="dashboard.php" class="button">Kembali ke Dashboard</a>
        </div>
    </div>
</body>
</html>

<?php

$student_stmt->close();
$grade_stmt->close();
$conn->close();
?>
