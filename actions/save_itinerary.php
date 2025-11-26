<?php
session_start();
include '../config/db.php';

// Check Admin
if(!isset($_SESSION['role']) || $_SESSION['role'] != 'admin') exit("Access Denied");

// Collect Data
$title = $conn->real_escape_string($_POST['title']);
$dest = $conn->real_escape_string($_POST['destination']);
$days = (int)$_POST['duration_days'];
$price = (float)$_POST['base_price'];
$content = $conn->real_escape_string($_POST['content']);
$user_id = $_SESSION['user_id'];

// Insert Query
$sql = "INSERT INTO master_itineraries (title, destination, duration_days, base_price, content, created_by) 
        VALUES ('$title', '$dest', $days, $price, '$content', $user_id)";

if($conn->query($sql)) {
    // Redirect with success message
    echo "<script>alert('Master Itinerary Created Successfully!'); window.location.href='../dashboard.php';</script>";
} else {
    echo "Error: " . $conn->error;
}
?>