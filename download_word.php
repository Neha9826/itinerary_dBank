<?php
include 'config/db.php';

if(!isset($_GET['id'])) { exit("No ID specified"); }

$id = $_GET['id'];
$row = $conn->query("SELECT * FROM master_itineraries WHERE id=$id")->fetch_assoc();
$data = json_decode($row['content'], true);

// --- IMAGE HELPER ---
function imageToBase64($path) {
    if (file_exists($path)) {
        $type = pathinfo($path, PATHINFO_EXTENSION);
        $data = file_get_contents($path);
        return 'data:image/' . $type . ';base64,' . base64_encode($data);
    }
    return '';
}

$header_src = !empty($row['header_image']) ? imageToBase64('./assets/uploads/itineraries/'.$row['header_image']) : '';
$footer_src = !empty($row['footer_image']) ? imageToBase64('./assets/uploads/itineraries/'.$row['footer_image']) : '';

// --- REUSABLE BLOCKS (COMPACT) ---
// Max-height restricted to 80px to save vertical space
$HEADER_BLOCK = $header_src ? '<div style="margin-bottom:10px; text-align:center;"><img src="'.$header_src.'" width="650" height="auto" style="width:100%; max-height:80px;"></div>' : '';
$FOOTER_BLOCK = $footer_src ? '<div style="margin-top:10px; text-align:center;"><img src="'.$footer_src.'" width="650" height="auto" style="width:100%; max-height:80px;"></div>' : '';
$PAGE_BREAK   = '<br clear="all" style="page-break-before:always" />';

// --- FILE HEADERS ---
$filename = preg_replace('/[^A-Za-z0-9\-]/', '_', $row['title']) . "_Itinerary.doc";
header("Content-Type: application/vnd.ms-word");
header("Expires: 0");
header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
header("content-disposition: attachment;filename=$filename");

echo "<html xmlns:o='urn:schemas-microsoft-com:office:office' xmlns:w='urn:schemas-microsoft-com:office:word' xmlns='http://www.w3.org/TR/REC-html40'>";
echo "<head><meta charset='utf-8'><title>" . $row['title'] . "</title>";
?>

<style>
    /* COMPACT FONTS */
    body { font-family: 'Arial', sans-serif; font-size: 10pt; color: #333; line-height: 1.3; }
    
    /* TABLES */
    table { width: 100%; border-collapse: collapse; margin-bottom: 8px; }
    td { vertical-align: top; padding: 4px; }
    
    /* PRETTY TABLES */
    table.gridtable { border: 1px solid #000; font-size: 9pt; }
    table.gridtable th { background-color: #f0f0f0; border: 1px solid #000; padding: 5px; font-weight: bold; text-align: left; }
    table.gridtable td { border: 1px solid #000; padding: 5px; }

    /* LAYOUT HELPERS */
    .day-header { 
        background-color: #e8f5e9; 
        border-left: 5px solid #28a745; 
        padding: 4px 8px; 
        font-weight: bold; 
        text-transform: uppercase;
        margin-bottom: 4px;
        font-size: 11pt;
    }
    
    /* FIXED IMAGE SIZE for itinerary (prevent layout break) */
    /* Use a reasonable width and max-height so Word doesn't expand images */
    img.content-img { 
        width: 160px;       /* forced width for consistent layout */
        height: auto; 
        max-height: 100px;  /* limit height to avoid big images */
        object-fit: cover; 
        border: 1px solid #ccc; 
        display: block;
    }
    
    /* LISTS */
    ul, ol { margin: 0 0 0 25px; padding: 0; }
    li { margin-bottom: 2px; }

    /* PAGE MARGINS */
    @page { 
        size: 8.5in 11.0in; 
        margin: 0.4in 0.5in 0.4in 0.5in; 
    }
    
    .no-break { page-break-inside: avoid; }
</style>
</head>
<body>

<?php echo $HEADER_BLOCK; ?>

<h2 style="color: #003366; text-transform: uppercase; border-bottom: 2px solid #003366; font-size: 14pt; margin-top:0; margin-bottom: 8px;">Program Overview</h2>
<table class="gridtable">
    <tr><th width="25%">Title</th><td><?php echo $data['program']['title']; ?></td></tr>
    <tr><th>Duration</th><td><?php echo $data['program']['duration']; ?></td></tr>
    <tr><th>Category</th><td><?php echo $data['program']['category']; ?></td></tr>
    <tr><th>Cost</th><td><?php echo $data['program']['cost']; ?> (<?php echo $data['program']['pax']; ?> Pax)</td></tr>
    <tr><th>Inclusions</th><td>
        <b>Flights:</b> <?php echo $data['program']['flights']; ?><br>
        <b>Meals:</b> <?php echo $data['program']['meals']; ?><br>
        <b>Transport:</b> <?php echo $data['program']['transport']; ?>
    </td></tr>
</table>

<?php if(!empty($data['hotels'])): ?>
<h2 style="color: #d39e00; text-transform: uppercase; border-bottom: 2px solid #d39e00; font-size: 14pt; margin-top: 10px; margin-bottom: 8px;">Hotels Envisaged</h2>
<table class="gridtable">
    <thead>
        <tr style="background-color: #333; color: #fff;">
            <th style="color:#fff;">Location</th><th style="color:#fff;">Hotel Name</th><th style="color:#fff;">Nights</th>
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

<?php echo $FOOTER_BLOCK; ?>
<?php echo $PAGE_BREAK; ?>


<?php 
if(!empty($data['timeline'])): 
    // Chunk days into groups of 2
    $chunks = array_chunk($data['timeline'], 2, true); 
    
    $globalDayCounter = 0; // track correct global day number

    foreach($chunks as $pageIndex => $daysOnPage): 
        // START PAGE WITH HEADER
        echo $HEADER_BLOCK; 
        
        // Title only on first itinerary page
        if($pageIndex === 0) {
            echo '<h2 style="color: #198754; text-transform: uppercase; border-bottom: 2px solid #198754; font-size: 14pt; margin-top:0; margin-bottom:10px;">Detailed Itinerary</h2>';
        } else {
            echo '<div style="height: 5px;"></div>';
        }

        foreach($daysOnPage as $i => $day):
            // increment global counter (so Day numbers are continuous across pages)
            $globalDayCounter++;
            
            // Image Prep
            $day_img_src = '';
            if(!empty($day['images'][0])) {
                $path = './assets/uploads/itineraries/'.$day['images'][0];
                $day_img_src = imageToBase64($path);
            }
            $clean_desc = strip_tags($day['desc'], '<br><p><b><strong><ul><ol><li>');
            $is_even = ($globalDayCounter % 2 == 0); // use global index for parity
?>
        <table class="no-break" style="width: 100%; border-bottom: 1px dashed #ccc; margin-bottom: 10px;">
            <tr>
                <?php if($is_even): // Text Left ?>
                    <td width="70%" valign="top" style="padding-right: 10px;">
                        <div class="day-header">Day <?php echo $globalDayCounter; ?>: <?php echo $day['title']; ?></div>
                        <div style="text-align: justify; font-size: 10pt;"><?php echo $clean_desc; ?></div>
                    </td>
                    <td width="160px" valign="top" align="center">
                        <?php if($day_img_src): ?><img src="<?php echo $day_img_src; ?>" class="content-img" style="width:160px; max-height:100px; height:auto;"><?php endif; ?>
                    </td>
                <?php else: // Image Left ?>
                    <td width="160px" valign="top" align="center" style="padding-right: 10px;">
                        <?php if($day_img_src): ?><img src="<?php echo $day_img_src; ?>" class="content-img" style="width:160px; max-height:100px; height:auto;"><?php endif; ?>
                    </td>
                    <td width="70%" valign="top">
                        <div class="day-header">Day <?php echo $globalDayCounter; ?>: <?php echo $day['title']; ?></div>
                        <div style="text-align: justify; font-size: 10pt;"><?php echo $clean_desc; ?></div>
                    </td>
                <?php endif; ?>
            </tr>
        </table>
<?php 
        endforeach; // End Loop for this page

        // END PAGE WITH FOOTER
        echo $FOOTER_BLOCK;
        echo $PAGE_BREAK;
    endforeach; 
endif; 
?>


<?php if(!empty($data['sections'])): ?>
    
    <?php foreach($data['sections'] as $sec): ?>
        
        <?php echo $HEADER_BLOCK; ?>
        
        <br>
        <div style="border: 2px solid #f5c6cb; background-color: #fffafa; padding: 15px; min-height: 400px;">
            <h2 style="color: #dc3545; text-transform: uppercase; border-bottom: 1px solid #dc3545; padding-bottom: 8px; font-size: 14pt; margin-top:0;">
                <?php echo $sec['heading']; ?>
            </h2>
            <div style="font-size: 10pt; color: #333; margin-top: 10px;">
                <?php echo nl2br($sec['content']); ?>
            </div>
        </div>

        <?php echo $FOOTER_BLOCK; ?>
        <?php echo $PAGE_BREAK; ?>

    <?php endforeach; ?>

<?php endif; ?>

</body>
</html>
