<?php
session_start();
include '../config/db.php';

if($_SESSION['role'] != 'admin') exit("Access Denied");

$id = $_POST['id'];
$upload_dir = "../assets/uploads/itineraries/";

// --- 1. HANDLE BRANDING IMAGES ---
function handleUpload($fileKey, $oldKey, $dir) {
    if (!empty($_FILES[$fileKey]["name"])) {
        $name = time() . "_" . basename($_FILES[$fileKey]["name"]);
        move_uploaded_file($_FILES[$fileKey]["tmp_name"], $dir . $name);
        return $name;
    }
    return $_POST[$oldKey]; // Keep old if no new upload
}

$header_img = handleUpload('header_image', 'old_header_image', $upload_dir);
$footer_img = handleUpload('footer_image', 'old_footer_image', $upload_dir);

// --- 2. BUILD JSON STRUCTURE ---
$data = [];

// Program
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

// Hotels
$data['hotels'] = isset($_POST['hotels']) ? array_values($_POST['hotels']) : [];

// Detailed Itinerary (Merge Old & New Images)
$days = [];
if(isset($_POST['days'])) {
    foreach($_POST['days'] as $key => $day) {
        $dayData = [
            'title' => $day['title'],
            'desc' => $day['desc'],
            'images' => [] 
        ];

        // Keep Old Images
        if(isset($day['existing_images'])) {
            foreach($day['existing_images'] as $old) $dayData['images'][] = $old;
        }

        // Add New Images
        $inputName = "day_images_" . $key;
        if(isset($_FILES[$inputName])) {
            foreach($_FILES[$inputName]['tmp_name'] as $idx => $tmp) {
                if(!empty($tmp)) {
                    $name = time() . "_day{$key}_{$idx}.jpg";
                    move_uploaded_file($tmp, $upload_dir . $name);
                    $dayData['images'][] = $name;
                }
            }
        }
        $days[] = $dayData;
    }
}
$data['timeline'] = $days;

// Sections
$data['sections'] = isset($_POST['sections']) ? array_values($_POST['sections']) : [];

// --- 3. UPDATE DATABASE ---
$json_content = $conn->real_escape_string(json_encode($data));
$title = $conn->real_escape_string($_POST['program_title']);
$base_price = (float)filter_var($_POST['cost'], FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);

$sql = "UPDATE master_itineraries SET 
        title = '$title',
        base_price = '$base_price',
        header_image = '$header_img',
        footer_image = '$footer_img',
        content = '$json_content' 
        WHERE id = $id";

if($conn->query($sql)) {
    echo "<script>alert('Master Itinerary Updated!'); window.location.href='../preview_itinerary.php?id=$id';</script>";
} else {
    echo "Error: " . $conn->error;
}
?>