<?php
session_start();
include '../config/db.php';
date_default_timezone_set('Asia/Kolkata'); // Force IST

// 1. Security Check
if(!isset($_SESSION['role']) || $_SESSION['role'] != 'employee') {
    header("Location: ../dashboard.php");
    exit();
}

$attendance_id = $_SESSION['attendance_id'];
$action = $_POST['action'];
$now = date('Y-m-d H:i:s');

// --- 1. START BREAK ---
if ($action == 'start_break') {
    $reason = $conn->real_escape_string($_POST['reason']);
    
    // Insert new break record
    $insert = "INSERT INTO breaks (attendance_id, start_time, reason) VALUES ($attendance_id, '$now', '$reason')";
    if($conn->query($insert)) {
        // Success
    } else {
        // Optional: Log error if needed
        error_log("Break Start Error: " . $conn->error);
    }

// --- 2. END BREAK ---
} elseif ($action == 'end_break') {
    
    // Find the currently active break (end_time is NULL)
    $check = $conn->query("SELECT id FROM breaks WHERE attendance_id = $attendance_id AND end_time IS NULL");
    
    if($check->num_rows > 0) {
        $break_row = $check->fetch_assoc();
        $break_id = $break_row['id'];
        
        // Close it
        $update = "UPDATE breaks SET end_time = '$now' WHERE id = $break_id";
        $conn->query($update);
    }
}

// Redirect back to Dashboard
header("Location: ../dashboard.php");
exit();
?>