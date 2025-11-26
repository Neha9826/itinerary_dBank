<?php
session_start();
include '../config/db.php';
date_default_timezone_set('Asia/Kolkata');

// 1. Security Check
if(!isset($_SESSION['role']) || $_SESSION['role'] != 'employee') {
    die("❌ Error: You are not logged in as an employee.");
}

$attendance_id = $_SESSION['attendance_id'];
$action = $_POST['action'];
$now = date('Y-m-d H:i:s');

// --- SKIP BREAK LOGIC FOR DEBUGGING ---
if ($action == 'start_break' || $action == 'end_break') {
    // (Keep your existing break logic here if needed, but we are testing logout)
    header("Location: ../dashboard.php");
    exit();
}

// --- LOGOUT DEBUG MODE ---
if ($action == 'logout') {
    echo "<h1>🔍 Time Tracking Debugger</h1>";
    echo "Attendance ID: <b>$attendance_id</b><br>";
    echo "Logout Time: <b>$now</b><br><hr>";

    // A. Fetch Login Time
    $query = "SELECT login_time FROM attendance WHERE id = $attendance_id";
    $result = $conn->query($query);
    if(!$result || $result->num_rows == 0) {
        die("❌ Critical Error: Could not find attendance record for ID $attendance_id");
    }
    $row = $result->fetch_assoc();
    echo "✅ Login Time Found: " . $row['login_time'] . "<br>";

    // B. Calculate Duration
    $start = strtotime($row['login_time']);
    $end = strtotime($now);
    $session_seconds = $end - $start;
    echo "--- Raw Session Duration: $session_seconds seconds<br>";

    // C. Calculate Breaks
    $break_seconds = 0;
    $b_sql = "SELECT start_time, end_time FROM breaks WHERE attendance_id = $attendance_id AND end_time IS NOT NULL";
    $b_res = $conn->query($b_sql);
    echo "--- Breaks Found: " . $b_res->num_rows . "<br>";
    
    while($b = $b_res->fetch_assoc()) {
        $diff = strtotime($b['end_time']) - strtotime($b['start_time']);
        $break_seconds += $diff;
        echo "------ Break Deduction: $diff seconds<br>";
    }

    // D. Final Math
    $actual_work = $session_seconds - $break_seconds;
    if($actual_work < 0) $actual_work = 0;
    
    echo "<h3>🧮 Final Calculated Seconds: $actual_work</h3>";

    // E. Format
    $hours = floor($actual_work / 3600);
    $minutes = floor(($actual_work % 3600) / 60);
    $seconds = $actual_work % 60;
    $final_time = sprintf('%02d:%02d:%02d', $hours, $minutes, $seconds);

    echo "<h1>⏱ Time to Save: <span style='color:blue'>$final_time</span></h1>";

    // F. FORCE UPDATE
    $updateSql = "UPDATE attendance SET logout_time = '$now', total_hours = '$final_time' WHERE id = $attendance_id";
    echo "Executing SQL: <code>$updateSql</code><br><br>";

    if($conn->query($updateSql)) {
        echo "<h2 style='color:green'>✅ SUCCESS: Database accepted the query.</h2>";
    } else {
        echo "<h2 style='color:red'>❌ FAILURE: Database rejected the query.</h2>";
        echo "<b>MySQL Error:</b> " . $conn->error;
    }

    echo "<br><br><a href='../login.php'>Click here to continue to Login</a>";
    
    session_unset();
    session_destroy();
    exit();
}
?>