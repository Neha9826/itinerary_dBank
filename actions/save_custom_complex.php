<?php
session_start();
include '../config/db.php';

// 1. Security Check
if($_SESSION['role'] != 'employee' && $_SESSION['role'] != 'admin') {
    die("Access Denied");
}

// 2. Setup Upload Directory
$upload_dir = "../assets/uploads/itineraries/";
if (!file_exists($upload_dir)) mkdir($upload_dir, 0777, true);

// 3. Collect Basic Info
$master_id = $_POST['master_id'];
$agent_id = $_POST['agent_id'];
$employee_id = $_SESSION['user_id'];
$custom_title = $conn->real_escape_string($_POST['custom_title']);

// Sanitizing Price for DB Sorting (Remove 'Rs.', ',', '/-')
$raw_cost = $_POST['cost']; 
$final_price = (float)filter_var($raw_cost, FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);

// --- 4. CONSTRUCT JSON STRUCTURE ---
$data = [];

// Part A: Program Overview
$data['program'] = [
    'title' => $_POST['program_title'],
    'category' => $_POST['hotel_category'],
    'duration' => $_POST['duration'],
    'cost' => $_POST['cost'], // Store the formatted string (e.g. "Rs. 49,900/-")
    'pax' => $_POST['pax_size'],
    'flights' => $_POST['flights'],
    'meals' => $_POST['meals'],
    'transport' => $_POST['transport']
];

// Part B: Hotels
// array_values() re-indexes the array to 0,1,2... incase user deleted row #2
$data['hotels'] = isset($_POST['hotels']) ? array_values($_POST['hotels']) : [];

// Part C: Detailed Itinerary (The Tricky Part - Image Merging)
$days = [];
if(isset($_POST['days'])) {
    foreach($_POST['days'] as $key => $day) {
        
        $dayData = [
            'title' => $day['title'],
            'desc' => $day['desc'],
            'images' => [] 
        ];

        // 1. Keep Existing Images (if any were preserved)
        if(isset($day['existing_images'])) {
            foreach($day['existing_images'] as $oldImg) {
                $dayData['images'][] = $oldImg;
            }
        }

        // 2. Handle New File Uploads for this specific day
        // The input name in HTML is: day_images_{ROW_ID}[]
        $inputName = "day_images_" . $key;
        
        if(isset($_FILES[$inputName])) {
            // Loop through each file uploaded for this day
            foreach($_FILES[$inputName]['tmp_name'] as $idx => $tmpName) {
                if(!empty($tmpName)) {
                    $originalName = $_FILES[$inputName]['name'][$idx];
                    $ext = pathinfo($originalName, PATHINFO_EXTENSION);
                    
                    // Generate unique name: timestamp_dayX_index.jpg
                    $newName = time() . "_day{$key}_{$idx}." . $ext;
                    
                    if(move_uploaded_file($tmpName, $upload_dir . $newName)) {
                        $dayData['images'][] = $newName;
                    }
                }
            }
        }

        $days[] = $dayData;
    }
}
$data['timeline'] = $days;

// Part D: Other Sections
$data['sections'] = isset($_POST['sections']) ? array_values($_POST['sections']) : [];

// --- 5. ENCODE & SAVE ---
$json_content = $conn->real_escape_string(json_encode($data));

$sql = "INSERT INTO sent_itineraries 
        (master_itinerary_id, employee_id, agent_id, custom_title, custom_content, final_price, sent_at) 
        VALUES 
        ('$master_id', '$employee_id', '$agent_id', '$custom_title', '$json_content', '$final_price', NOW())";

if($conn->query($sql)) {
    // Redirect with success flag
    echo "<script>
        alert('Itinerary Sent Successfully!'); 
        window.location.href='../dashboard.php';
    </script>";
} else {
    echo "Error: " . $conn->error;
}
?>