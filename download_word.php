<?php
include 'config/db.php';

if(!isset($_GET['id'])) { exit("No ID specified"); }

$id = $_GET['id'];
$row = $conn->query("SELECT * FROM master_itineraries WHERE id=$id")->fetch_assoc();
$data = json_decode($row['content'], true);

// Function to convert image to Base64 (Essential for Word)
function imageToBase64($path) {
    if (file_exists($path)) {
        $type = pathinfo($path, PATHINFO_EXTENSION);
        $data = file_get_contents($path);
        return 'data:image/' . $type . ';base64,' . base64_encode($data);
    }
    return '';
}

// Prepare Images
$header_img_path = './assets/uploads/itineraries/'.$row['header_image'];
$header_src = !empty($row['header_image']) ? imageToBase64($header_img_path) : '';

$footer_img_path = './assets/uploads/itineraries/'.$row['footer_image'];
$footer_src = !empty($row['footer_image']) ? imageToBase64($footer_img_path) : '';

// --- FILE DOWNLOAD HEADERS ---
$filename = preg_replace('/[^A-Za-z0-9\-]/', '_', $row['title']) . "_Itinerary.doc";
header("Content-Type: application/vnd.ms-word");
header("Expires: 0");
header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
header("content-disposition: attachment;filename=$filename");

echo "<html xmlns:o='urn:schemas-microsoft-com:office:office' xmlns:w='urn:schemas-microsoft-com:office:word' xmlns='http://www.w3.org/TR/REC-html40'>";
echo "<head><meta charset='utf-8'><title>" . $row['title'] . "</title>";
?>

<style>
    /* BASIC STYLES */
    body { font-family: Arial, sans-serif; font-size: 11pt; color: #000; }
    table { width: 100%; border-collapse: collapse; margin-bottom: 15px; }
    td { vertical-align: top; padding: 5px; }
    
    /* BORDERED TABLES (Program & Hotels) */
    table.bordered { border: 1px solid #000; width: 100%; border-collapse: collapse; }
    table.bordered th { background-color: #f0f0f0; border: 1px solid #000; padding: 8px; text-align: left; }
    table.bordered td { border: 1px solid #000; padding: 8px; }

    /* LAYOUT HELPERS */
    .day-title { font-size: 14pt; font-weight: bold; color: #b91d47; text-transform: uppercase; margin-bottom: 5px; }
    .day-text { text-align: justify; font-size: 10.5pt; line-height: 1.4; }
    
    /* === SECTION 1: PAGE 1 (No Header/Footer Definitions) === */
    @page Section1 {
        size: 8.5in 11.0in; 
        margin: 0.5in 0.8in 0.5in 0.8in; /* Smaller margins so manual images fit well */
    }
    div.Section1 { page: Section1; }

    /* === SECTION 2: PAGE 2+ (Has Headers/Footers) === */
    @page Section2 {
        size: 8.5in 11.0in; 
        margin: 1.0in 0.8in 1.0in 0.8in;
        mso-header-margin: 0.5in;
        mso-footer-margin: 0.5in;
        mso-header: h1; /* Links to the Repeating Header */
        mso-footer: f1; /* Links to the Repeating Footer */
    }
    div.Section2 { page: Section2; }
</style>
</head>
<body>

<div style="display:none;">
    <div style="mso-element:header" id="h1">
        <?php if($header_src): ?>
            <p align="center"><img src="<?php echo $header_src; ?>" width="600" style="height:auto;"></p>
        <?php endif; ?>
    </div>
    <div style="mso-element:footer" id="f1">
        <?php if($footer_src): ?>
            <p align="center"><img src="<?php echo $footer_src; ?>" width="600" style="height:auto;"></p>
        <?php endif; ?>
        <p style="text-align: right; font-size: 8pt;">Page <span style='mso-field-code: PAGE '></span></p>
    </div>
</div>

<div class="Section1">
    
    <?php if($header_src): ?>
        <p align="center" style="margin-bottom: 20px;">
            <img src="<?php echo $header_src; ?>" width="650" style="max-width:100%; height:auto;">
        </p>
    <?php endif; ?>

    <h2 style="color: #003366; text-transform: uppercase; border-bottom: 2px solid #003366;">Program Overview</h2>
    <table class="bordered">
        <tr><th width="25%">Package Title</th><td><?php echo $data['program']['title']; ?></td></tr>
        <tr><th>Duration</th><td><?php echo $data['program']['duration']; ?></td></tr>
        <tr><th>Hotel Category</th><td><?php echo $data['program']['category']; ?></td></tr>
        <tr><th>Package Cost</th><td><?php echo $data['program']['cost']; ?> (<?php echo $data['program']['pax']; ?> Pax)</td></tr>
        <tr><th>Inclusions</th><td>
            <b>Flights:</b> <?php echo $data['program']['flights']; ?><br>
            <b>Meals:</b> <?php echo $data['program']['meals']; ?><br>
            <b>Transport:</b> <?php echo $data['program']['transport']; ?>
        </td></tr>
    </table>
    <br>

    <?php if(!empty($data['hotels'])): ?>
    <h2 style="color: #d39e00; text-transform: uppercase; border-bottom: 2px solid #d39e00;">Hotels Envisaged</h2>
    <table class="bordered">
        <thead>
            <tr style="background-color: #333; color: #fff;">
                <th>Location</th><th>Hotel Name</th><th>Nights</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach($data['hotels'] as $hotel): ?>
            <tr>
                <td><?php echo $hotel['location']; ?></td>
                <td><?php echo $hotel['name']; ?></td>
                <td><?php echo $hotel['nights']; ?></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    <?php endif; ?>

    <div style="margin-top: 50px;">
        <?php if($footer_src): ?>
            <p align="center"><img src="<?php echo $footer_src; ?>" width="650" style="height:auto;"></p>
        <?php endif; ?>
    </div>

    <br clear="all" style="page-break-before:always; mso-break-type:section-break" />

</div><div class="Section2">

    <?php if(!empty($data['timeline'])): ?>
    <h2 style="color: #198754; text-transform: uppercase; border-bottom: 2px solid #198754;">Detailed Itinerary</h2>
    <br>

    <?php 
    $i = 0;
    foreach($data['timeline'] as $day): 
        $day_img_src = '';
        if(!empty($day['images'][0])) {
            $path = './assets/uploads/itineraries/'.$day['images'][0];
            $day_img_src = imageToBase64($path);
        }
        $clean_desc = strip_tags($day['desc'], '<br><p><b><strong>');
        $is_even = ($i % 2 == 0);
    ?>

    <table style="width: 100%; border-bottom: 1px solid #ccc; margin-bottom: 20px;">
        <tr>
            <?php if($is_even): ?>
                <td width="65%" valign="top" style="padding-right: 15px;">
                    <div class="day-title">Day <?php echo $i + 1; ?>: <?php echo $day['title']; ?></div>
                    <div class="day-text"><?php echo $clean_desc; ?></div>
                </td>
                <td width="35%" valign="top" align="center">
                    <?php if($day_img_src): ?><img src="<?php echo $day_img_src; ?>" width="200" height="140" style="border: 2px solid #eee; object-fit:cover;"><?php endif; ?>
                </td>
            <?php else: ?>
                <td width="35%" valign="top" align="center" style="padding-right: 15px;">
                    <?php if($day_img_src): ?><img src="<?php echo $day_img_src; ?>" width="200" height="140" style="border: 2px solid #eee; object-fit:cover;"><?php endif; ?>
                </td>
                <td width="65%" valign="top">
                    <div class="day-title">Day <?php echo $i + 1; ?>: <?php echo $day['title']; ?></div>
                    <div class="day-text"><?php echo $clean_desc; ?></div>
                </td>
            <?php endif; ?>
        </tr>
    </table>

    <?php $i++; endforeach; ?>
    <?php endif; ?>

    <?php if(!empty($data['sections'])): ?>
    <br>
    <?php foreach($data['sections'] as $sec): ?>
    <div style="border: 1px solid #ffcccc; background-color: #fffafa; padding: 10px; margin-bottom: 15px;">
        <b style="color: #dc3545; text-transform: uppercase;"><?php echo $sec['heading']; ?></b>
        <div style="font-size: 10pt; color: #555; margin-top: 5px;"><?php echo nl2br($sec['content']); ?></div>
    </div>
    <?php endforeach; ?>
    <?php endif; ?>

</div></body>
</html>