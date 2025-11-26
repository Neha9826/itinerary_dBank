<?php
// 1. Force PHP to use Indian Standard Time
date_default_timezone_set('Asia/Kolkata');

$servername = "localhost";
$username = "root"; 
$password = "";     
$dbname = "itinerary_dbank"; 

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// 2. Force MySQL to sync with PHP's timezone (Crucial for TIMESTAMPDIFF calculations)
$conn->query("SET time_zone = '+05:30'");
?>