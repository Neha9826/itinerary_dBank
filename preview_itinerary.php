<?php 
include 'includes/header.php'; 
include 'config/db.php';

if(!isset($_GET['id'])) { header("Location: view_masters.php"); exit; }

$id = $_GET['id'];
$row = $conn->query("SELECT * FROM master_itineraries WHERE id=$id")->fetch_assoc();
$data = json_decode($row['content'], true);

// Image Helper
function getBase64Image($path) {
    if (file_exists($path)) {
        $type = pathinfo($path, PATHINFO_EXTENSION);
        $data = file_get_contents($path);
        return 'data:image/' . $type . ';base64,' . base64_encode($data);
    }
    return '';
}

$header_path = './assets/uploads/itineraries/' . $row['header_image'];
$footer_path = './assets/uploads/itineraries/' . $row['footer_image'];
$header_src = !empty($row['header_image']) ? getBase64Image($header_path) : '';
$footer_src = !empty($row['footer_image']) ? getBase64Image($footer_path) : '';
?>

<div class="app-content-header">
    <div class="container-fluid">
        <div class="row">
            <div class="col-sm-6"><h3>Itinerary Preview</h3></div>
            <div class="col-sm-6 text-end">
                <a href="view_masters.php" class="btn btn-secondary me-1">Back</a>
                
                <?php if($_SESSION['role'] == 'admin'): ?>
                    <a href="edit_master_itinerary.php?id=<?php echo $id; ?>" class="btn btn-warning me-1">
                        <i class="bi bi-pencil-square"></i> Edit Master
                    </a>
                <?php endif; ?>

                <a href="customize_itinerary.php?id=<?php echo $id; ?>" class="btn btn-warning me-2">
                    <i class="bi bi-pencil-square"></i> Edit & Send to Agent
                </a>

                <a href="download_word.php?id=<?php echo $id; ?>" class="btn btn-primary">
                    <i class="bi bi-file-word"></i> Download Doc
                </a>
            </div>
        </div>
    </div>
</div>

<div class="app-content">
    <div class="container-fluid">
        <div class="card mx-auto shadow-lg" style="max-width: 210mm; border: none;">
            <div class="card-body p-0">
                
                <?php if($header_src): ?>
                    <img src="<?php echo $header_src; ?>" style="width: 100%; height: auto; display: block;">
                <?php endif; ?>
                
                <div class="p-5">
                    <h2 class="text-uppercase fw-bold text-primary border-bottom pb-2">Program Overview</h2>
                    <table class="table table-bordered mb-4">
                        <tr><th class="bg-light" width="30%">Title</th><td><?php echo $data['program']['title']; ?></td></tr>
                        <tr><th class="bg-light">Duration</th><td><?php echo $data['program']['duration']; ?></td></tr>
                        <tr><th class="bg-light">Category</th><td><?php echo $data['program']['category']; ?></td></tr>
                        <tr><th class="bg-light">Cost</th><td><?php echo $data['program']['cost']; ?> (<?php echo $data['program']['pax']; ?> Pax)</td></tr>
                        <tr><th class="bg-light">Inclusions</th><td>
                            <strong>Flights:</strong> <?php echo $data['program']['flights']; ?><br>
                            <strong>Meals:</strong> <?php echo $data['program']['meals']; ?><br>
                            <strong>Transport:</strong> <?php echo $data['program']['transport']; ?>
                        </td></tr>
                    </table>

                    <?php if(!empty($data['hotels'])): ?>
                    <h4 class="text-uppercase fw-bold text-warning border-bottom pb-2">Hotels Used</h4>
                    <table class="table table-striped mb-4">
                        <thead class="table-dark"><tr><th>City</th><th>Hotel</th><th>Nights</th></tr></thead>
                        <tbody>
                            <?php foreach($data['hotels'] as $h): ?>
                            <tr><td><?php echo $h['location']; ?></td><td><?php echo $h['name']; ?></td><td><?php echo $h['nights']; ?></td></tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                    <?php endif; ?>

                    <?php if(!empty($data['timeline'])): ?>
                    <h4 class="text-uppercase fw-bold text-success border-bottom pb-2">Detailed Itinerary</h4>
                    <?php foreach($data['timeline'] as $i => $day): ?>
                        <div class="mb-3 p-3 bg-light border rounded">
                            <h5 class="fw-bold text-dark">Day <?php echo $i+1; ?>: <?php echo $day['title']; ?></h5>
                            <div class="text-muted small"><?php echo $day['desc']; ?></div>
                        </div>
                    <?php endforeach; endif; ?>

                    <?php if(!empty($data['sections'])): ?>
                    <div class="mt-4">
                        <?php foreach($data['sections'] as $sec): ?>
                        <div class="alert alert-light border border-danger mb-3">
                            <h5 class="text-danger fw-bold"><?php echo $sec['heading']; ?></h5>
                            <div><?php echo nl2br($sec['content']); ?></div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    <?php endif; ?>

                </div>

                <?php if($footer_src): ?>
                    <img src="<?php echo $footer_src; ?>" style="width: 100%; height: auto; display: block;">
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>