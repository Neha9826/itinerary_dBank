<?php
session_start();
include '../config/db.php';

// Check if form was submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    // 1. Sanitize and collect input data
    $name = $conn->real_escape_string($_POST['name']);
    $email = $conn->real_escape_string($_POST['email']);
    $password = $_POST['password']; 
    $role = $conn->real_escape_string($_POST['role']);

    // 2. Basic Validation
    if (empty($name) || empty($email) || empty($password) || empty($role)) {
        die("Please fill all fields.");
    }

    // 3. Check if email already exists
    $check_sql = "SELECT id FROM users WHERE email = '$email'";
    $result = $conn->query($check_sql);

    if ($result->num_rows > 0) {
        // Email already taken
        echo "<script>
                alert('Email already registered! Please login.');
                window.location.href='../login.php';
              </script>";
        exit();
    }

    // 4. Hash the password (Security Best Practice)
    // We use PASSWORD_DEFAULT which currently uses Bcrypt
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

    // 5. Insert User into Database
    // Note: We are not tracking login time here; that happens on actual Login
    $sql = "INSERT INTO users (name, email, password, role) VALUES ('$name', '$email', '$hashed_password', '$role')";

    if ($conn->query($sql) === TRUE) {
        // Registration successful
        echo "<script>
                alert('Registration Successful! Please login.');
                window.location.href='../login.php';
              </script>";
    } else {
        echo "Error: " . $sql . "<br>" . $conn->error;
    }
} else {
    // If someone tries to access this file directly without submitting form
    header("Location: ../register.php");
    exit();
}

$conn->close();
?>