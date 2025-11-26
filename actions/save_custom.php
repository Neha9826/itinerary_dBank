<?php
session_start();
include '../config/db.php';

$master_id = $_POST['master_id'];
$employee_id = $_SESSION['user_id'];
$agent_id = $_POST['agent_id'];
$custom_title = $_POST['custom_title'];
$content = $_POST['custom_content'];
$price = $_POST['final_price'];

$stmt = $conn->prepare("INSERT INTO sent_itineraries (master_itinerary_id, employee_id, agent_id, custom_title, custom_content, final_price, sent_at) VALUES (?, ?, ?, ?, ?, ?, NOW())");
$stmt->bind_param("iiissd", $master_id, $employee_id, $agent_id, $custom_title, $content, $price);

if($stmt->execute()) {
    header("Location: ../dashboard.php?msg=sent");
} else {
    echo "Error: " . $conn->error;
}
?>