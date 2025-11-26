<?php
session_start();
include '../config/db.php';

if($_SESSION['role'] != 'admin') exit("Access Denied");

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    $id = $_POST['user_id'];
    $name = $conn->real_escape_string($_POST['name']);
    $phone = $conn->real_escape_string($_POST['phone']);
    $address = $conn->real_escape_string($_POST['address']);
    $joining = $_POST['joining_date'];
    $salary = !empty($_POST['salary']) ? $_POST['salary'] : 0;
    $role = $_POST['role'];

    // Collect Dynamic Fields
    $dept_name = trim($conn->real_escape_string($_POST['department']));
    $prof_name = trim($conn->real_escape_string($_POST['profile']));

    // --- SMART LOGIC: AUTO-ADD NEW DEPARTMENTS ---
    if(!empty($dept_name)) {
        $check_dept = $conn->query("SELECT id FROM departments WHERE name = '$dept_name'");
        if($check_dept->num_rows == 0) {
            $conn->query("INSERT INTO departments (name) VALUES ('$dept_name')");
        }
    }

    // --- SMART LOGIC: AUTO-ADD NEW PROFILES ---
    if(!empty($prof_name)) {
        $check_prof = $conn->query("SELECT id FROM profiles WHERE name = '$prof_name'");
        if($check_prof->num_rows == 0) {
            $conn->query("INSERT INTO profiles (name) VALUES ('$prof_name')");
        }
    }

    // --- File Upload Logic ---
    $upload_dir = "../assets/uploads/";
    
    // Profile Pic
    $profile_pic = $_POST['old_profile_pic'];
    if (!empty($_FILES["profile_pic"]["name"])) {
        $filename = time() . "_profile_" . basename($_FILES["profile_pic"]["name"]);
        if(move_uploaded_file($_FILES["profile_pic"]["tmp_name"], $upload_dir . $filename)) {
            $profile_pic = $filename;
        }
    }

    // ID Proof
    $id_proof = $_POST['old_id_proof'];
    if (!empty($_FILES["id_proof"]["name"])) {
        $filename = time() . "_id_" . basename($_FILES["id_proof"]["name"]);
        if(move_uploaded_file($_FILES["id_proof"]["tmp_name"], $upload_dir . $filename)) {
            $id_proof = $filename;
        }
    }

    // --- Update Query with New 'profile' Column ---
    $sql = "UPDATE users SET 
            name='$name', 
            phone='$phone', 
            department='$dept_name', 
            profile='$prof_name', 
            address='$address', 
            joining_date='$joining', 
            salary='$salary', 
            role='$role', 
            profile_pic='$profile_pic', 
            id_proof='$id_proof' 
            WHERE id=$id";

    if ($conn->query($sql) === TRUE) {
        echo "<script>alert('User Updated! New Departments/Profiles saved automatically.'); window.location.href='../manage_users.php';</script>";
    } else {
        echo "Error: " . $conn->error;
    }
}
?>