<?php 
include 'includes/header.php'; 
include 'config/db.php';

if(!isset($_GET['id'])) { header("Location: sent_history.php"); exit; }

$id = $_GET['id'];

// 1. FETCH SENT ITINERARY + MASTER BRANDING
$sql = "SELECT s.*, m.header_image, m.footer_image 
        FROM sent_itineraries s 
        JOIN master_itineraries m ON s.master_itinerary_id = m.id 
        WHERE s.id = $id";

$row = $conn->query($sql)->fetch_assoc();
if(!$row) die("Itinerary not found.");

// Decode JSON Data
$data = json_decode($row['custom_content'], true);

// 2. FETCH AGENTS (For the Forwarding Dropdown)
$agents = $conn->query("SELECT id, name FROM users WHERE role='agent'");

// Asset Paths
$header_img = !empty($row['header_image']) ? './assets/uploads/itineraries/'.$row['header_image'] : '';
$footer_img = !empty($row['footer_image']) ? './assets/uploads/itineraries/'.$row['footer_image'] : '';
?>

<style>
    .formatted-text ul, .formatted-text ol { margin-left: 20px !important; padding-left: 0 !important; }
    .formatted-text li { margin-bottom: 5px; }
</style>

<div class="app-content-header">
    <div class="container-fluid">
        <div class="row">
            <div class="col-sm-6"><h3>View Sent Itinerary</h3></div>
            <div class="col-sm-6 text-end">
                <a href="sent_history.php" class="btn btn-secondary me-2">Back to History</a>
                
                <a href="customize_itinerary.php?sent_id=<?php echo $id; ?>" class="btn btn-warning me-2"><i class="bi bi-pencil-square"></i> Edit & Resend</a>

                <a href="download_custom.php?id=<?php echo $id; ?>" class="btn btn-primary"><i class="bi bi-file-word"></i> Download Doc</a>

            </div>
        </div>
    </div>
</div>

<div class="app-content">
    <div class="container-fluid">
        
        <div class="card card-warning card-outline mb-4">
            <div class="card-header">
                <h5 class="card-title"><i class="bi bi-share-fill"></i> Forward this Itinerary</h5>
            </div>
            <div class="card-body bg-light">
                <form action="actions/forward_itinerary.php" method="POST" class="row align-items-end">
                    <input type="hidden" name="original_sent_id" value="<?php echo $id; ?>">
                    
                    <div class="col-md-5">
                        <label class="form-label">New Title (Optional)</label>
                        <input type="text" name="new_title" class="form-control" value="<?php echo $row['custom_title']; ?>">
                    </div>
                    
                    <div class="col-md-5">
                        <label class="form-label">Select New Agent</label>
                        <select name="new_agent_id" class="form-select" required>
                            <option value="">-- Choose Agent --</option>
                            <?php 
                            // Reset pointer just in case
                            $agents->data_seek(0);
                            while($a = $agents->fetch_assoc()): 
                            ?>
                                <option value="<?php echo $a['id']; ?>"><?php echo $a['name']; ?></option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                    
                    <div class="col-md-2">
                        <button type="submit" class="btn btn-warning w-100 fw-bold">
                            <i class="bi bi-send"></i> Send Copy
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <div class="card mx-auto shadow-lg" style="max-width: 210mm; border: none;">
            <div class="card-body p-0">
                
                <?php if($header_img): ?>
                    <img src="<?php echo $header_img; ?>" style="width: 100%; height: auto; display: block;">
                <?php endif; ?>

                <div class="p-5">
                    <h4 class="text-uppercase fw-bold text-primary mb-3 border-bottom pb-2">Program Overview</h4>
                    <table class="table table-bordered mb-5">
                        <tr><th class="bg-light" style="width: 30%;">Title</th><td><?php echo $data['program']['title']; ?></td></tr>
                        <tr><th class="bg-light">Duration</th><td><?php echo $data['program']['duration']; ?></td></tr>
                        <tr><th class="bg-light">Category</th><td><?php echo $data['program']['category']; ?></td></tr>
                        <tr><th class="bg-light">Cost</th><td class="text-danger fw-bold"><?php echo $data['program']['cost']; ?> (<?php echo $data['program']['pax']; ?> Pax)</td></tr>
                        <tr><th class="bg-light">Inclusions</th><td>
                            <strong>Flights:</strong> <?php echo $data['program']['flights']; ?><br>
                            <strong>Meals:</strong> <?php echo $data['program']['meals']; ?><br>
                            <strong>Transport:</strong> <?php echo $data['program']['transport']; ?>
                        </td></tr>
                    </table>

                    <?php if(!empty($data['hotels'])): ?>
                    <h4 class="text-uppercase fw-bold text-warning mb-3 border-bottom pb-2">Hotels Used</h4>
                    <table class="table table-striped mb-5">
                        <thead class="table-dark"><tr><th>City</th><th>Hotel</th><th>Nights</th></tr></thead>
                        <tbody>
                            <?php foreach($data['hotels'] as $h): ?>
                            <tr><td><?php echo $h['location']; ?></td><td><?php echo $h['name']; ?></td><td><?php echo $h['nights']; ?></td></tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                    <?php endif; ?>

                    <?php if(!empty($data['timeline'])): ?>
                    <h4 class="text-uppercase fw-bold text-success mb-3 border-bottom pb-2">Detailed Itinerary</h4>
                    <?php foreach($data['timeline'] as $i => $day): ?>
                    <div class="row mb-4 border-bottom pb-3">
                        <?php if($i % 2 == 0): ?>
                            <div class="col-md-8">
                                <h5 class="fw-bold text-dark"><span class="badge bg-success me-2">Day <?php echo $i+1; ?></span> <?php echo $day['title']; ?></h5>
                                <div class="formatted-text text-muted"><?php echo $day['desc']; ?></div>
                            </div>
                            <div class="col-md-4">
                                <?php if(!empty($day['images'][0])): ?>
                                    <img src="./assets/uploads/itineraries/<?php echo $day['images'][0]; ?>" class="img-fluid rounded shadow-sm border">
                                <?php endif; ?>
                            </div>
                        <?php else: ?>
                            <div class="col-md-4">
                                <?php if(!empty($day['images'][0])): ?>
                                    <img src="./assets/uploads/itineraries/<?php echo $day['images'][0]; ?>" class="img-fluid rounded shadow-sm border">
                                <?php endif; ?>
                            </div>
                            <div class="col-md-8">
                                <h5 class="fw-bold text-dark"><span class="badge bg-success me-2">Day <?php echo $i+1; ?></span> <?php echo $day['title']; ?></h5>
                                <div class="formatted-text text-muted"><?php echo $day['desc']; ?></div>
                            </div>
                        <?php endif; ?>
                    </div>
                    <?php endforeach; endif; ?>

                    <?php if(!empty($data['sections'])): ?>
                    <div class="mt-5">
                        <?php foreach($data['sections'] as $sec): ?>
                        <div class="alert alert-light border border-danger mb-3">
                            <h5 class="fw-bold text-danger"><?php echo $sec['heading']; ?></h5>
                            <div class="formatted-text"><?php echo nl2br($sec['content']); ?></div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    <?php endif; ?>
                </div>

                <?php if($footer_img): ?>
                    <img src="<?php echo $footer_img; ?>" style="width: 100%; height: auto; display: block;">
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>