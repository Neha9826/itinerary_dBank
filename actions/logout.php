<?php
session_start();
include '../config/db.php';
date_default_timezone_set('Asia/Kolkata'); // Force IST

// 1. Check if this is an Employee trying to logout
if (isset($_SESSION['role']) && $_SESSION['role'] == 'employee' && isset($_SESSION['attendance_id'])) {
    
    $attendance_id = $_SESSION['attendance_id'];
    $now = date('Y-m-d H:i:s');

    // A. CLOSE ANY ACTIVE BREAKS
    // We check if there is an open break and close it before calculating
    $check_break = $conn->query("SELECT id FROM breaks WHERE attendance_id = $attendance_id AND end_time IS NULL");
    if ($check_break->num_rows > 0) {
        $break_row = $check_break->fetch_assoc();
        $break_id = $break_row['id'];
        $conn->query("UPDATE breaks SET end_time = '$now' WHERE id = $break_id");
    }

    // B. UPDATE LOGOUT TIME
    $conn->query("UPDATE attendance SET logout_time = '$now' WHERE id = $attendance_id");

    // --- C. PERFORM CALCULATION (The Missing Part) ---
    
    // 1. Fetch Login & Logout Times
    $query = "SELECT login_time, logout_time FROM attendance WHERE id = $attendance_id";
    $result = $conn->query($query);
    
    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        
        $start = strtotime($row['login_time']);
        $end = strtotime($now); // Use current time for accuracy
        $session_seconds = $end - $start;

        // 2. Calculate Total Break Time
        $break_seconds = 0;
        $b_sql = "SELECT start_time, end_time FROM breaks WHERE attendance_id = $attendance_id AND end_time IS NOT NULL";
        $b_res = $conn->query($b_sql);
        
        while($b = $b_res->fetch_assoc()) {
            $b_start = strtotime($b['start_time']);
            $b_end = strtotime($b['end_time']);
            $break_seconds += ($b_end - $b_start);
        }

        // 3. Final Math
        $actual_work = $session_seconds - $break_seconds;
        if ($actual_work < 0) $actual_work = 0;

        // 4. Format Time (HH:MM:SS)
        $hours = floor($actual_work / 3600);
        $minutes = floor(($actual_work % 3600) / 60);
        $seconds = $actual_work % 60;
        $final_time = sprintf('%02d:%02d:%02d', $hours, $minutes, $seconds);

        // 5. SAVE TO DATABASE
        $update = "UPDATE attendance SET total_hours = '$final_time' WHERE id = $attendance_id";
        
        if (!$conn->query($update)) {
            // If error, STOP and show it (Debug Mode)
            die("❌ Database Error: " . $conn->error);
        }
    }
}

// 2. Destroy Session (Log out)
session_unset();
session_destroy();

// 3. Redirect to Login
header("Location: ../login.php");
exit();
?>