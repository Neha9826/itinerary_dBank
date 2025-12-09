<?php
// 1. LOAD LIBRARIES & DB
require_once('assets/tcpdf/tcpdf.php'); 
include 'config/db.php';

if(!isset($_GET['id'])) { exit("No ID specified"); }

$id = $_GET['id'];
$row = $conn->query("SELECT * FROM master_itineraries WHERE id=$id")->fetch_assoc();
$data = json_decode($row['content'], true);

// Define Image Paths
$header_path = __DIR__ . '/assets/uploads/itineraries/' . $row['header_image'];
$footer_path = __DIR__ . '/assets/uploads/itineraries/' . $row['footer_image'];

// --- 2. EXTEND TCPDF ---
class ItineraryPDF extends TCPDF {
    public $header_file;
    public $footer_file;

    // Helper to detect type
    private function get_image_type($file) {
        if(!file_exists($file)) return '';
        $info = getimagesize($file);
        if ($info[2] == IMAGETYPE_JPEG) return 'JPG';
        if ($info[2] == IMAGETYPE_PNG) return 'PNG';
        return '';
    }

    // Custom Header
    public function Header() {
        if (file_exists($this->header_file)) {
            $type = $this->get_image_type($this->header_file);
            // Image(file, x, y, w, h, type, link, align, resize, dpi, align, stretch, ismask, imgmask, border, fitbox, hidden, fitonpage)
            $this->Image($this->header_file, 0, 0, 210, '', $type, '', 'T', false, 300, '', false, false, 0, false, false, false);
        }
    }

    // Custom Footer
    public function Footer() {
        if (file_exists($this->footer_file)) {
            $type = $this->get_image_type($this->footer_file);
            $this->SetY(-35); 
            $this->Image($this->footer_file, 0, $this->GetY(), 210, '', $type, '', 'T', false, 300, '', false, false, 0, false, false, false);
        }
        $this->SetY(-15);
        $this->SetFont('helvetica', 'I', 8);
        $this->Cell(0, 10, 'Page '.$this->getAliasNumPage().'/'.$this->getAliasNbPages(), 0, false, 'R', 0, '', 0, false, 'T', 'M');
    }
}

// 3. SETUP PDF
$pdf = new ItineraryPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
$pdf->header_file = $header_path;
$pdf->footer_file = $footer_path;

$pdf->SetCreator(PDF_CREATOR);
$pdf->SetTitle($row['title']);

// Margins
$pdf->SetMargins(15, 85, 15); 
$pdf->SetAutoPageBreak(TRUE, 40); 
$pdf->SetFont('helvetica', '', 10);


// --- 4. GENERATE CONTENT ---
$pdf->AddPage(); 

// CSS Styles
$css = '
<style>
    /* Headings */
    h2 { font-size: 16pt; text-transform: uppercase; font-weight: bold; margin: 0; padding-bottom: 5px; }
    
    /* Theme Colors */
    .blue-bar { color: #003366; border-bottom: 2px solid #003366; margin-bottom: 15px; }
    
    /* Green Bar for Itinerary */
    .green-bar { color: #28a745; border-bottom: 2px solid #28a745; margin-bottom: 15px; }
    
    /* Red Bar for Sections */
    .red-bar { color: #dc3545; border-bottom: 2px solid #dc3545; margin-bottom: 15px; }

    /* Tables */
    table { width: 100%; border-collapse: collapse; padding: 8px; }
    td { vertical-align: top; }

    /* Program Overview Table */
    .program-tbl th { background-color: #f0f4f8; border: 1px solid #ccc; color: #003366; font-weight: bold; width: 30%; }
    .program-tbl td { border: 1px solid #ccc; }

    /* HOTELS TABLE - SPECIFIC STYLING */
    .hotel-tbl { width: 100%; border: 1px solid #000; }
    .hotel-tbl th { 
        background-color: #333; 
        color: #ffffff; 
        font-weight: bold; 
        border: 1px solid #000; 
        text-align: center; /* Center Heading */
    }
    .hotel-tbl td { 
        border: 1px solid #000; /* Visible Border */
        text-align: center;     /* Center Content */
        color: #333;
        vertical-align: middle;
    }
    .hotel-row-even { background-color: #ffffff; }
    .hotel-row-odd { background-color: #f2f2f2; }

    /* Itinerary Layout */
    .day-box { margin-bottom: 15px; }
    .day-header { 
        background-color: #e8f5e9; 
        color: #006600;
        font-weight: bold; 
        padding: 5px 10px; 
        border-left: 5px solid #28a745;
        font-size: 11pt;
    }
    
    /* Images */
    img.day-img { border: 1px solid #ddd; padding: 2px; }
    
    /* Lists */
    ul { margin-left: 15px; padding: 0; }
    li { margin-bottom: 3px; }
</style>';


// --- A. PROGRAM OVERVIEW ---
$html = $css;
$html .= '<h2 class="blue-bar">Program Overview</h2>';
$html .= '<table class="program-tbl" width="100%">
    <tr><th>Title</th><td>'.$data['program']['title'].'</td></tr>
    <tr><th>Duration</th><td>'.$data['program']['duration'].'</td></tr>
    <tr><th>Category</th><td>'.$data['program']['category'].'</td></tr>
    <tr><th>Total Cost</th><td style="color:#c00000; font-weight:bold;">'.$data['program']['cost'].' ('.$data['program']['pax'].' Pax)</td></tr>
    <tr><th>Inclusions</th><td>
        <b>Flights:</b> '.$data['program']['flights'].'<br>
        <b>Meals:</b> '.$data['program']['meals'].'<br>
        <b>Transport:</b> '.$data['program']['transport'].'
    </td></tr>
</table><br><br>';

$pdf->writeHTML($html, true, false, true, false, '');


// --- B. HOTELS USED (With Explicit Styles) ---
if(!empty($data['hotels'])) {
    // Inline style for Heading Color to guarantee it works
    $html = '<h2 style="color: #d39e00; border-bottom: 2px solid #d39e00; margin-bottom: 15px;">Hotels Envisaged</h2>';
    
    $html .= '<table class="hotel-tbl" cellspacing="0" cellpadding="8" border="1">
        <thead>
            <tr>
                <th width="30%">Location</th>
                <th width="50%">Hotel Name</th>
                <th width="20%">Nights</th>
            </tr>
        </thead>
        <tbody>';
    
    $row_count = 0;
    foreach($data['hotels'] as $hotel) {
        $class = ($row_count % 2 == 0) ? 'hotel-row-even' : 'hotel-row-odd';
        $html .= '<tr class="'.$class.'">
            <td width="30%">'.$hotel['location'].'</td>
            <td width="50%"><strong>'.$hotel['name'].'</strong></td>
            <td width="20%">'.$hotel['nights'].'</td>
        </tr>';
        $row_count++;
    }
    $html .= '</tbody></table><br>';
    
    $pdf->writeHTML($html, true, false, true, false, '');
}


// --- 5. ITINERARY (New Page for Days) ---
if(!empty($data['timeline'])) {
    $pdf->AddPage();
    
    $html = '<h2 class="green-bar">Detailed Itinerary</h2><br>';
    
    $i = 0;
    foreach($data['timeline'] as $day) {
        $i++;
        
        // Image Handling
        $img_tag = '';
        if(!empty($day['images'][0])) {
            $img_path = __DIR__ . '/assets/uploads/itineraries/'.$day['images'][0];
            if(file_exists($img_path)) {
                $info = getimagesize($img_path);
                $type = ($info[2] == IMAGETYPE_PNG) ? 'PNG' : 'JPG';
                $img_tag = '<img src="'.$img_path.'" width="160" height="110" style="border:1px solid #ccc;">';
            }
        }

        // Clean Description
        $clean_desc = strip_tags($day['desc'], '<br><p><b><strong><ul><ol><li>');
        
        // Zig-Zag Layout
        if($i % 2 != 0) { // Odd: Text Left
            $html .= '
            <table border="0" cellpadding="5">
                <tr>
                    <td width="70%" style="border-right:1px dashed #ccc; padding-right:10px;">
                        <div class="day-header">Day '.$i.': '.$day['title'].'</div>
                        <div style="text-align:justify;">'.$clean_desc.'</div>
                    </td>
                    <td width="30%" align="center" valign="middle">'.$img_tag.'</td>
                </tr>
            </table>';
        } else { // Even: Image Left
            $html .= '
            <table border="0" cellpadding="5">
                <tr>
                    <td width="30%" align="center" valign="middle">'.$img_tag.'</td>
                    <td width="70%" style="border-left:1px dashed #ccc; padding-left:10px;">
                        <div class="day-header">Day '.$i.': '.$day['title'].'</div>
                        <div style="text-align:justify;">'.$clean_desc.'</div>
                    </td>
                </tr>
            </table>';
        }
        $html .= '<hr style="color:#eee; margin:10px 0;"><br>';
    }
    $pdf->writeHTML($html, true, false, true, false, '');
}

// --- 6. SECTIONS (Inclusions, etc.) ---
if(!empty($data['sections'])) {
    $pdf->AddPage(); 
    
    foreach($data['sections'] as $sec) {
        $html = '
        <div style="border: 2px solid #f5c6cb; background-color: #fffafa; padding: 15px; margin-bottom: 20px;">
            <h2 class="red-bar" style="font-size:12pt; border:none; margin-bottom:5px;">'.$sec['heading'].'</h2>
            <div style="font-size: 10pt; color: #444;">
                '.nl2br($sec['content']).'
            </div>
        </div><br>';
        
        $pdf->writeHTML($html, true, false, true, false, '');
    }
}

$pdf->Output($row['title'] . '.pdf', 'D');
?>