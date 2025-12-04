<?php
session_start();
include '../config/db.php';

// Security Check
if($_SESSION['role'] != 'employee' && $_SESSION['role'] != 'admin') {
    die("Access Denied");
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    $original_id = $conn->real_escape_string($_POST['original_sent_id']);
    $new_agent_id = $conn->real_escape_string($_POST['new_agent_id']);
    $new_title = $conn->real_escape_string($_POST['new_title']);
    $employee_id = $_SESSION['user_id']; // The sender is the current logged-in user

    // 1. Fetch the Original Data
    $sql = "SELECT master_itinerary_id, custom_content, final_price FROM sent_itineraries WHERE id = '$original_id'";
    $result = $conn->query($sql);
    
    if($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        
        $master_id = $row['master_itinerary_id'];
        $content = $conn->real_escape_string($row['custom_content']); // Escape JSON for insertion
        $price = $row['final_price'];

        // 2. Insert New Record (Cloning)
        $insert_sql = "INSERT INTO sent_itineraries 
                       (master_itinerary_id, employee_id, agent_id, custom_title, custom_content, final_price, sent_at) 
                       VALUES 
                       ('$master_id', '$employee_id', '$new_agent_id', '$new_title', '$content', '$price', NOW())";

        if($conn->query($insert_sql)) {
            echo "<script>
                alert('Itinerary forwarded successfully!'); 
                window.location.href='../sent_history.php';
            </script>";
        } else {
            echo "Error forwarding: " . $conn->error;
        }

    } else {
        echo "Original itinerary not found.";
    }
}
?>