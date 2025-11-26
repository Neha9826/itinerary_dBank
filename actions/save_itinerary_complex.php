<?php
session_start();
include '../config/db.php';

if($_SESSION['role'] != 'admin') exit("Access Denied");

// --- 1. HANDLE FILE UPLOADS ---
$upload_dir = "../assets/uploads/itineraries/";
if (!file_exists($upload_dir)) mkdir($upload_dir, 0777, true);

function uploadFile($fileInputName, $targetDir) {
    if (!empty($_FILES[$fileInputName]["name"])) {
        $fileName = time() . "_" . basename($_FILES[$fileInputName]["name"]);
        if(move_uploaded_file($_FILES[$fileInputName]["tmp_name"], $targetDir . $fileName)) {
            return $fileName;
        }
    }
    return null;
}

$header_img = uploadFile('header_image', $upload_dir);
$footer_img = uploadFile('footer_image', $upload_dir);

// --- 2. STRUCTURE THE JSON DATA ---
$data = [];

// Part 1: Program Overview
$data['program'] = [
    'title' => $_POST['program_title'],
    'category' => $_POST['hotel_category'],
    'duration' => $_POST['duration'],
    'cost' => $_POST['cost'],
    'pax' => $_POST['pax_size'],
    'flights' => $_POST['flights'],
    'meals' => $_POST['meals'],
    'transport' => $_POST['transport']
];

// Part 2: Hotels (Clean up array keys)
$data['hotels'] = array_values($_POST['hotels'] ?? []);

// Part 3: Detailed Itinerary (Handle Day Images manually)
$days = [];
if(isset($_POST['days'])) {
    foreach($_POST['days'] as $key => $day) {
        $dayData = [
            'title' => $day['title'],
            'desc' => $day['desc'],
            'images' => []
        ];

        // Upload images for this specific day
        $inputName = "day_images_" . $key;
        if(isset($_FILES[$inputName])) {
            foreach($_FILES[$inputName]['tmp_name'] as $idx => $tmpName) {
                if(!empty($tmpName)) {
                    $fName = time() . "_day{$key}_" . $idx . ".jpg";
                    move_uploaded_file($tmpName, $upload_dir . $fName);
                    $dayData['images'][] = $fName;
                }
            }
        }
        $days[] = $dayData;
    }
}
$data['timeline'] = $days;

// Part 4: Other Sections
$data['sections'] = array_values($_POST['sections'] ?? []);

// --- 3. SAVE TO DATABASE ---
$title = $conn->real_escape_string($_POST['program_title']);
$destination = "Multi-Location"; // You can make this dynamic if needed
$base_price = (float)filter_var($_POST['cost'], FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
$json_content = $conn->real_escape_string(json_encode($data));

$sql = "INSERT INTO master_itineraries 
        (title, destination, base_price, header_image, footer_image, content, created_by) 
        VALUES ('$title', '$destination', '$base_price', '$header_img', '$footer_img', '$json_content', {$_SESSION['user_id']})";

if($conn->query($sql)) {
    echo "<script>alert('Itinerary Saved Successfully!'); window.location.href='../dashboard.php';</script>";
} else {
    echo "Error: " . $conn->error;
}
?>