<?php
session_start();
include '../config/db.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    // Security: Ensure the ID submitted matches the logged-in user
    if($_POST['user_id'] != $_SESSION['user_id']) {
        die("Unauthorized Action");
    }

    $id = $_SESSION['user_id'];
    $phone = $conn->real_escape_string($_POST['phone']);
    $address = $conn->real_escape_string($_POST['address']);

    // --- File Upload Logic ---
    $upload_dir = "../assets/uploads/";
    
    // 1. Profile Pic
    $profile_pic = $_POST['old_profile_pic'];
    if (!empty($_FILES["profile_pic"]["name"])) {
        $filename = time() . "_profile_" . basename($_FILES["profile_pic"]["name"]);
        if(move_uploaded_file($_FILES["profile_pic"]["tmp_name"], $upload_dir . $filename)) {
            $profile_pic = $filename;
        }
    }

    // 2. ID Proof
    $id_proof = $_POST['old_id_proof'];
    if (!empty($_FILES["id_proof"]["name"])) {
        $filename = time() . "_id_" . basename($_FILES["id_proof"]["name"]);
        if(move_uploaded_file($_FILES["id_proof"]["tmp_name"], $upload_dir . $filename)) {
            $id_proof = $filename;
        }
    }

    // --- Update Query (Restricted Fields only) ---
    $sql = "UPDATE users SET 
            phone='$phone', 
            address='$address', 
            profile_pic='$profile_pic', 
            id_proof='$id_proof' 
            WHERE id=$id";

    if ($conn->query($sql) === TRUE) {
        echo "<script>alert('Profile Updated Successfully!'); window.location.href='../profile.php';</script>";
    } else {
        echo "Error: " . $conn->error;
    }
}
?>