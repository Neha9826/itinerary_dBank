<?php 
include 'includes/header.php'; 
include 'config/db.php';

if(!isset($_GET['id'])) { header("Location: view_masters.php"); exit; }

$id = $_GET['id'];
$row = $conn->query("SELECT * FROM master_itineraries WHERE id=$id")->fetch_assoc();
$data = json_decode($row['content'], true);

$header_img = !empty($row['header_image']) ? './assets/uploads/itineraries/'.$row['header_image'] : '';
$footer_img = !empty($row['footer_image']) ? './assets/uploads/itineraries/'.$row['footer_image'] : '';
?>

<style>
    .formatted-text ul, .formatted-text ol {
        margin-left: 20px !important;
        padding-left: 20px !important;
        list-style-position: outside !important;
    }
    .formatted-text ul { list-style-type: disc !important; }
    .formatted-text ol { list-style-type: decimal !important; }
    .formatted-text li { margin-bottom: 5px; }
</style>

<div class="app-content-header">
    <div class="container-fluid">
        <div class="row">
            <div class="col-sm-6"><h3>Itinerary Preview</h3></div>
            <div class="col-sm-6 text-end">
                <a href="view_masters.php" class="btn btn-secondary me-1">Back</a>
                <?php if($_SESSION['role'] == 'admin'): ?>
                    <a href="edit_master_itinerary.php?id=<?php echo $id; ?>" class="btn btn-warning me-1"><i class="bi bi-pencil-square"></i> Edit Master</a>
                <?php endif; ?>
                <a href="download_word.php?id=<?php echo $id; ?>" class="btn btn-primary"><i class="bi bi-file-word"></i> Download Doc</a>
            </div>
        </div>
    </div>
</div>

<div class="app-content">
    <div class="container-fluid">
        <div class="card mx-auto shadow-lg" style="max-width: 210mm; min-height: 297mm; border: none;">
            <div class="card-body p-0">
                
                <?php if($header_img): ?>
                    <img src="<?php echo $header_img; ?>" style="width: 100%; height: auto; display: block;">
                <?php else: ?>
                    <div class="bg-primary text-white p-4 text-center"><h1><?php echo $row['title']; ?></h1></div>
                <?php endif; ?>

                <div class="p-5">
                    
                    <h4 class="text-uppercase fw-bold text-primary mb-3 border-bottom pb-2">Program Overview</h4>
                    <table class="table table-bordered mb-5">
                        <tr><th class="bg-light" style="width: 30%;">Title</th><td><?php echo $data['program']['title']; ?></td></tr>
                        <tr><th class="bg-light">Duration</th><td><?php echo $data['program']['duration']; ?></td></tr>
                        <tr><th class="bg-light">Hotel Category</th><td><?php echo $data['program']['category']; ?></td></tr>
                        <tr><th class="bg-light">Cost</th><td><?php echo $data['program']['cost']; ?> (For <?php echo $data['program']['pax']; ?> Pax)</td></tr>
                        <tr><th class="bg-light">Inclusions</th><td>
                            <strong>Flights:</strong> <?php echo $data['program']['flights']; ?><br>
                            <strong>Meals:</strong> <?php echo $data['program']['meals']; ?><br>
                            <strong>Transport:</strong> <?php echo $data['program']['transport']; ?>
                        </td></tr>
                    </table>

                    <?php if(!empty($data['hotels'])): ?>
                    <h4 class="text-uppercase fw-bold text-warning mb-3 border-bottom pb-2">Hotels Used</h4>
                    <table class="table table-striped mb-5">
                        <thead class="table-dark">
                            <tr><th>Location</th><th>Hotel Name</th><th>Nights</th></tr>
                        </thead>
                        <tbody>
                            <?php foreach($data['hotels'] as $hotel): ?>
                            <tr><td><?php echo $hotel['location']; ?></td><td><?php echo $hotel['name']; ?></td><td><?php echo $hotel['nights']; ?></td></tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                    <?php endif; ?>

                    <?php if(!empty($data['timeline'])): ?>
                    <h4 class="text-uppercase fw-bold text-success mb-3 border-bottom pb-2">Detailed Itinerary</h4>
                    <div class="timeline-box">
                        <?php foreach($data['timeline'] as $index => $day): ?>
                        <div class="mb-4">
                            <h5 class="fw-bold text-dark"><span class="badge bg-success me-2">Day <?php echo $index + 1; ?></span> <?php echo $day['title']; ?></h5>
                            <div class="ps-3 border-start border-3 border-success ms-2">
                                <div class="text-muted mb-2 formatted-text">
                                    <?php echo $day['desc']; ?>
                                </div>
                                <?php if(!empty($day['images'])): ?>
                                <div class="row g-2 mt-2">
                                    <?php foreach($day['images'] as $img): ?>
                                    <div class="col-3"><img src="./assets/uploads/itineraries/<?php echo $img; ?>" class="img-fluid rounded border shadow-sm"></div>
                                    <?php endforeach; ?>
                                </div>
                                <?php endif; ?>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    <?php endif; ?>

                    <?php if(!empty($data['sections'])): ?>
                    <div class="mt-5">
                        <?php foreach($data['sections'] as $sec): ?>
                        <div class="alert alert-light border mb-3">
                            <h5 class="fw-bold text-danger"><?php echo $sec['heading']; ?></h5>
                            <div class="mb-0 formatted-text">
                                <?php echo nl2br($sec['content']); ?>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    <?php endif; ?>

                </div>

                <?php if($footer_img): ?>
                    <img src="<?php echo $footer_img; ?>" style="width: 100%; height: auto; display: block; margin-top: auto;">
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>