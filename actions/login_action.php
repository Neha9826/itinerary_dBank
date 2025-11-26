<?php
session_start();
include '../config/db.php';

$email = $conn->real_escape_string($_POST['email']);
$password = $_POST['password'];

$sql = "SELECT * FROM users WHERE email = '$email'";
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    $user = $result->fetch_assoc();
    
    // VERIFY PASSWORD HASH
    if (password_verify($password, $user['password'])) {
        
        // Login Success
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['name'] = $user['name'];
        $_SESSION['role'] = $user['role'];

        // TIME TRACKING START (Employees Only)
        if($user['role'] == 'employee') {
            $date = date('Y-m-d');
            $time = date('Y-m-d H:i:s');
            
            // Check if already logged in today to prevent duplicate entries
            $check = $conn->query("SELECT id FROM attendance WHERE user_id = {$user['id']} AND date = '$date' AND logout_time IS NULL");
            
            if($check->num_rows == 0) {
                $conn->query("INSERT INTO attendance (user_id, login_time, date) VALUES ({$user['id']}, '$time', '$date')");
                $_SESSION['attendance_id'] = $conn->insert_id;
            } else {
                $row = $check->fetch_assoc();
                $_SESSION['attendance_id'] = $row['id'];
            }
        }

        header("Location: ../dashboard.php");
        exit();
        
    } else {
        echo "<script>alert('Invalid Password'); window.location.href='../login.php';</script>";
    }
} else {
    echo "<script>alert('User not found'); window.location.href='../login.php';</script>";
}
?>