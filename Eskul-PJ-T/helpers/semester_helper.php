<?php
//SET SEMESTER
function getCurrentSemester() {
    global $conn;
    $currentDate = new DateTime();
    $year = $currentDate->format('Y');
    
    $stmt = $conn->prepare("SELECT id_semester, semester, tahun_ajaran 
                           FROM semester 
                           WHERE tahun_ajaran = ?
                           AND ((semester = 'Ganjil' AND MONTH(?) BETWEEN 7 AND 12)
                           OR (semester = 'Genap' AND MONTH(?) BETWEEN 1 AND 6))
                           LIMIT 1");
    
    $currentDate = $currentDate->format('Y-m-d');
    $tahun_ajaran = $year . "/" . ($year + 1);
    $stmt->bind_param("sss", $tahun_ajaran, $currentDate, $currentDate);
    $stmt->execute();
    
    $result = $stmt->get_result();
    if ($result->num_rows > 0) {
        return $result->fetch_assoc();
    }
    return false;
}

//SAMA SEMESTER
function isValidSemesterDate($date) {
    $checkDate = new DateTime($date);
    $year = $checkDate->format('Y');
    
    $ganjilStart = new DateTime("$year-07-14");
    $ganjilEnd = new DateTime("$year-12-20");
    $genapStart = new DateTime("$year-01-05");
    $genapEnd = new DateTime("$year-06-13");
    
    return ($checkDate >= $ganjilStart && $checkDate <= $ganjilEnd) || 
           ($checkDate >= $genapStart && $checkDate <= $genapEnd);
}

//ABSENSI
function calculateAttendancePoints($totalPresent, $totalScheduled) {
    if ($totalScheduled == 0) return 0;
    
    $percentage = ($totalPresent / $totalScheduled) * 100;
    
    if ($percentage >= 100) return 4.0;
    if ($percentage >= 90) return 3.5;
    if ($percentage >= 80) return 3.0;
    if ($percentage >= 70) return 2.5;
    if ($percentage >= 60) return 2.0;
    if ($percentage >= 50) return 1.5;
    return 1.0;
}
