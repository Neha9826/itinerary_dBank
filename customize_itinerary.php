<?php 
include 'includes/header.php'; 
include 'config/db.php';

// Check Employee/Admin Access
if($_SESSION['role'] != 'employee' && $_SESSION['role'] != 'admin') { 
    echo "<script>window.location.href='dashboard.php';</script>"; exit; 
}

$data = [];
$master = [];
$prefill_title = "";
$prefill_agent = "";
$is_edit_mode = false;

// --- LOGIC 1: EDITING A PREVIOUSLY SENT ITINERARY ---
if (isset($_GET['sent_id'])) {
    $sent_id = $_GET['sent_id'];
    $sent_row = $conn->query("SELECT * FROM sent_itineraries WHERE id=$sent_id")->fetch_assoc();
    
    if(!$sent_row) die("Itinerary not found");

    // Get the original Master ID (needed for database linkage)
    $master_id = $sent_row['master_itinerary_id'];
    $master = $conn->query("SELECT * FROM master_itineraries WHERE id=$master_id")->fetch_assoc();
    
    // Load the CUSTOM content (The edited version)
    $data = json_decode($sent_row['custom_content'], true);
    
    $prefill_title = $sent_row['custom_title'] . " (Rev)";
    $prefill_agent = $sent_row['agent_id'];
    $is_edit_mode = true;

// --- LOGIC 2: CREATING NEW FROM MASTER ---
} elseif (isset($_GET['id'])) {
    $id = $_GET['id'];
    $master = $conn->query("SELECT * FROM master_itineraries WHERE id=$id")->fetch_assoc();
    
    // Load the MASTER content
    $data = json_decode($master['content'], true);
    
    $prefill_title = "Quote: " . $master['title'];
    $prefill_agent = "";
} else {
    header("Location: view_masters.php"); exit;
}

// Fetch Agents
$agents = $conn->query("SELECT id, name FROM users WHERE role='agent'");
?>

<div class="app-content-header">
    <div class="container-fluid">
        <h3>
            <?php echo $is_edit_mode ? 'Edit Quote: ' : 'Customize: '; ?> 
            <span class="text-primary"><?php echo $master['title']; ?></span>
        </h3>
    </div>
</div>

<div class="app-content">
    <div class="container-fluid">
        <form action="actions/save_custom_complex.php" method="POST" enctype="multipart/form-data">
            <input type="hidden" name="master_id" value="<?php echo $master['id']; ?>">
            
            <div class="card card-outline card-primary mb-4">
                <div class="card-header"><h5 class="card-title">Quote Details</h5></div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label>Custom Title</label>
                            <input type="text" name="custom_title" class="form-control" value="<?php echo $prefill_title; ?>">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label>Select Agent</label>
                            <select name="agent_id" class="form-control" required>
                                <option value="">-- Select Agent --</option>
                                <?php 
                                $agents->data_seek(0);
                                while($agent = $agents->fetch_assoc()): 
                                    $selected = ($agent['id'] == $prefill_agent) ? 'selected' : '';
                                ?>
                                    <option value="<?php echo $agent['id']; ?>" <?php echo $selected; ?>><?php echo $agent['name']; ?></option>
                                <?php endwhile; ?>
                            </select>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card card-outline card-info mb-4">
                <div class="card-header"><h5 class="card-title">Part 1: Program Overview</h5></div>
                <div class="card-body">
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label>Program Title</label>
                            <input type="text" name="program_title" class="form-control" value="<?php echo $data['program']['title']; ?>" required>
                        </div>
                        <div class="col-md-3">
                            <label>Hotel Category</label>
                            <input type="text" name="hotel_category" class="form-control" value="<?php echo $data['program']['category']; ?>">
                        </div>
                        <div class="col-md-3">
                            <label>Duration</label>
                            <input type="text" name="duration" class="form-control" value="<?php echo $data['program']['duration']; ?>">
                        </div>
                    </div>
                    
                    <div class="row mb-3">
                        <div class="col-md-4">
                            <label>Final Cost</label>
                            <input type="text" name="cost" class="form-control fw-bold text-success" value="<?php echo $data['program']['cost']; ?>">
                        </div>
                        <div class="col-md-4">
                            <label>For Pax Size</label>
                            <input type="number" name="pax_size" class="form-control" value="<?php echo $data['program']['pax']; ?>">
                        </div>
                        <div class="col-md-4">
                            <label>Flights</label>
                            <input type="text" name="flights" class="form-control" value="<?php echo $data['program']['flights']; ?>">
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <label>Meals</label>
                            <input type="text" name="meals" class="form-control" value="<?php echo $data['program']['meals']; ?>">
                        </div>
                        <div class="col-md-6">
                            <label>Transport Used</label>
                            <input type="text" name="transport" class="form-control" value="<?php echo $data['program']['transport']; ?>">
                        </div>
                    </div>
                </div>
            </div>

            <div class="card card-outline card-warning mb-4">
                <div class="card-header d-flex justify-content-between">
                    <h5 class="card-title">Part 2: Hotels Used</h5>
                    <button type="button" class="btn btn-sm btn-dark" id="addHotelBtn"><i class="bi bi-plus"></i> Add Hotel</button>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered">
                            <thead><tr><th>Location</th><th>Hotel Name</th><th>Nights</th><th>Action</th></tr></thead>
                            <tbody id="hotelContainer">
                                <?php 
                                $hCount = 0;
                                if(!empty($data['hotels'])): foreach($data['hotels'] as $hotel): $hCount++; 
                                ?>
                                <tr id="hotelRow_<?php echo $hCount; ?>">
                                    <td><input type="text" name="hotels[<?php echo $hCount; ?>][location]" class="form-control" value="<?php echo $hotel['location']; ?>"></td>
                                    <td><input type="text" name="hotels[<?php echo $hCount; ?>][name]" class="form-control" value="<?php echo $hotel['name']; ?>"></td>
                                    <td><input type="text" name="hotels[<?php echo $hCount; ?>][nights]" class="form-control" value="<?php echo $hotel['nights']; ?>"></td>
                                    <td><button type="button" class="btn btn-danger btn-sm" onclick="$('#hotelRow_<?php echo $hCount; ?>').remove()"><i class="bi bi-trash"></i></button></td>
                                </tr>
                                <?php endforeach; endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <div class="card card-outline card-success mb-4">
                <div class="card-header d-flex justify-content-between">
                    <h5 class="card-title">Part 3: Detailed Itinerary</h5>
                    <button type="button" class="btn btn-sm btn-dark" id="addDayBtn"><i class="bi bi-plus-circle"></i> Add Day</button>
                </div>
                <div class="card-body" id="itineraryContainer">
                    <?php 
                    $dCount = 0;
                    if(!empty($data['timeline'])): foreach($data['timeline'] as $day): $dCount++; 
                    ?>
                    <div class="border rounded p-3 mb-3 bg-light position-relative" id="dayRow_<?php echo $dCount; ?>">
                        <button type="button" class="btn btn-danger btn-sm position-absolute top-0 end-0 m-2" onclick="$('#dayRow_<?php echo $dCount; ?>').remove()">X</button>
                        <div class="mb-2">
                            <label class="fw-bold">Day Title</label>
                            <input type="text" name="days[<?php echo $dCount; ?>][title]" class="form-control" value="<?php echo $day['title']; ?>">
                        </div>
                        <div class="mb-2">
                            <label>Description</label>
                            <textarea name="days[<?php echo $dCount; ?>][desc]" class="summernote"><?php echo $day['desc']; ?></textarea>
                        </div>
                        
                        <?php if(!empty($day['images'])): ?>
                            <div class="mb-2">
                                <small>Keep Existing Images:</small><br>
                                <?php foreach($day['images'] as $img): ?>
                                    <img src="./assets/uploads/itineraries/<?php echo $img; ?>" height="50" class="me-1 border">
                                    <input type="hidden" name="days[<?php echo $dCount; ?>][existing_images][]" value="<?php echo $img; ?>">
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>

                        <div class="mb-2">
                            <label>Add New Images</label>
                            <input type="file" name="day_images_<?php echo $dCount; ?>[]" class="form-control" multiple>
                        </div>
                    </div>
                    <?php endforeach; endif; ?>
                </div>
            </div>

            <div class="card card-outline card-danger mb-4">
                <div class="card-header d-flex justify-content-between">
                    <h5 class="card-title">Part 4: Inclusions & Notes</h5>
                    <button type="button" class="btn btn-sm btn-dark" id="addSectionBtn"><i class="bi bi-plus"></i> Add Section</button>
                </div>
                <div class="card-body" id="sectionContainer">
                    <?php 
                    $sCount = 0;
                    if(!empty($data['sections'])): foreach($data['sections'] as $sec): $sCount++; 
                    ?>
                    <div class="row mb-3" id="secRow_<?php echo $sCount; ?>">
                        <div class="col-md-4">
                            <input type="text" name="sections[<?php echo $sCount; ?>][heading]" class="form-control fw-bold" value="<?php echo $sec['heading']; ?>">
                        </div>
                        <div class="col-md-7">
                            <textarea name="sections[<?php echo $sCount; ?>][content]" class="form-control summernote" rows="2"><?php echo $sec['content']; ?></textarea>
                        </div>
                        <div class="col-md-1">
                            <button type="button" class="btn btn-danger btn-sm" onclick="$('#secRow_<?php echo $sCount; ?>').remove()"><i class="bi bi-trash"></i></button>
                        </div>
                    </div>
                    <?php endforeach; endif; ?>
                </div>
            </div>

            <div class="fixed-bottom bg-white p-3 shadow border-top text-end">
                <a href="<?php echo $is_edit_mode ? 'sent_history.php' : 'view_masters.php'; ?>" class="btn btn-secondary me-2">Cancel</a>
                <button type="submit" class="btn btn-primary btn-lg"><i class="bi bi-send-fill"></i> <?php echo $is_edit_mode ? 'Send New Version' : 'Send to Agent'; ?></button>
            </div>
            <br><br><br>

        </form>
    </div>
</div>

<?php include 'includes/footer.php'; ?>

<link href="https://cdn.jsdelivr.net/npm/summernote@0.8.18/dist/summernote-lite.min.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/summernote@0.8.18/dist/summernote-lite.min.js"></script>
<script>
    $(document).ready(function() {
        $('.summernote').summernote({ height: 150 });

        let hotelCount = <?php echo $hCount; ?>;
        let dayCount = <?php echo $dCount; ?>;
        let sectionCount = <?php echo $sCount; ?>;

        $('#addHotelBtn').click(function() {
            hotelCount++;
            let html = `<tr id="hotelRow_${hotelCount}">
                <td><input type="text" name="hotels[${hotelCount}][location]" class="form-control"></td>
                <td><input type="text" name="hotels[${hotelCount}][name]" class="form-control"></td>
                <td><input type="text" name="hotels[${hotelCount}][nights]" class="form-control"></td>
                <td><button type="button" class="btn btn-danger btn-sm" onclick="$('#hotelRow_${hotelCount}').remove()"><i class="bi bi-trash"></i></button></td>
            </tr>`;
            $('#hotelContainer').append(html);
        });

        $('#addDayBtn').click(function() {
            dayCount++;
            let html = `<div class="border rounded p-3 mb-3 bg-light position-relative" id="dayRow_${dayCount}">
                <button type="button" class="btn btn-danger btn-sm position-absolute top-0 end-0 m-2" onclick="$('#dayRow_${dayCount}').remove()">X</button>
                <div class="mb-2"><label class="fw-bold">Day Title</label><input type="text" name="days[${dayCount}][title]" class="form-control"></div>
                <div class="mb-2"><label>Description</label><textarea name="days[${dayCount}][desc]" class="summernote"></textarea></div>
                <div class=\"mb-2\"><label>New Images</label><input type=\"file\" name=\"day_images_${dayCount}[]\" class=\"form-control\" multiple></div>
            </div>`;
            $('#itineraryContainer').append(html);
            $(`#dayRow_${dayCount} .summernote`).summernote({ height: 150 });
        });

        $('#addSectionBtn').click(function() {
            sectionCount++;
            let html = `<div class="row mb-3" id="secRow_${sectionCount}">
                <div class="col-md-4"><input type="text" name="sections[${sectionCount}][heading]" class="form-control fw-bold"></div>
                <div class="col-md-7"><textarea name="sections[${sectionCount}][content]" class="form-control summernote" rows="2"></textarea></div>
                <div class="col-md-1"><button type="button" class="btn btn-danger btn-sm" onclick="$('#secRow_${sectionCount}').remove()"><i class="bi bi-trash"></i></button></div>
            </div>`;
            $('#sectionContainer').append(html);
            $(`#secRow_${sectionCount} .summernote`).summernote({ height: 100 });
        });
    });
</script>