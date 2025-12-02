<?php 
include 'includes/header.php'; 
include 'config/db.php';

// Only Admin
if($_SESSION['role'] != 'admin') { echo "<script>window.location.href='dashboard.php';</script>"; exit; }

$id = $_GET['id'];
$master = $conn->query("SELECT * FROM master_itineraries WHERE id=$id")->fetch_assoc();
$data = json_decode($master['content'], true);
?>

<div class="app-content-header">
    <div class="container-fluid">
        <h3>Edit Master Itinerary: <span class="text-primary"><?php echo $master['title']; ?></span></h3>
    </div>
</div>

<div class="app-content">
    <div class="container-fluid">
        <form action="actions/update_master_itinerary.php" method="POST" enctype="multipart/form-data">
            <input type="hidden" name="id" value="<?php echo $master['id']; ?>">
            
            <div class="card card-outline card-primary mb-4">
                <div class="card-header"><h5 class="card-title">Part 0: PDF Branding</h5></div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <label class="form-label">Header Image</label>
                            <?php if($master['header_image']): ?>
                                <div class="mb-2"><img src="./assets/uploads/itineraries/<?php echo $master['header_image']; ?>" height="60"></div>
                            <?php endif; ?>
                            <input type="file" name="header_image" class="form-control" accept="image/*">
                            <input type="hidden" name="old_header_image" value="<?php echo $master['header_image']; ?>">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Footer Image</label>
                            <?php if($master['footer_image']): ?>
                                <div class="mb-2"><img src="./assets/uploads/itineraries/<?php echo $master['footer_image']; ?>" height="60"></div>
                            <?php endif; ?>
                            <input type="file" name="footer_image" class="form-control" accept="image/*">
                            <input type="hidden" name="old_footer_image" value="<?php echo $master['footer_image']; ?>">
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
                            <label>Cost Per Person</label>
                            <input type="text" name="cost" class="form-control" value="<?php echo $data['program']['cost']; ?>">
                        </div>
                        <div class="col-md-4">
                            <label>Pax Size</label>
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
                                <?php $hCount = 0; if(!empty($data['hotels'])): foreach($data['hotels'] as $hotel): $hCount++; ?>
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
                    <?php $dCount = 0; if(!empty($data['timeline'])): foreach($data['timeline'] as $day): $dCount++; ?>
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
                                <small>Current Images:</small><br>
                                <?php foreach($day['images'] as $img): ?>
                                    <img src="./assets/uploads/itineraries/<?php echo $img; ?>" height="50" class="me-1 border">
                                    <input type="hidden" name="days[<?php echo $dCount; ?>][existing_images][]" value="<?php echo $img; ?>">
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>

                        <div class="mb-2">
                            <label>Add More Images</label>
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
                    <?php $sCount = 0; if(!empty($data['sections'])): foreach($data['sections'] as $sec): $sCount++; ?>
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
                <a href="preview_itinerary.php?id=<?php echo $id; ?>" class="btn btn-secondary me-2">Cancel</a>
                <button type="submit" class="btn btn-success btn-lg"><i class="bi bi-check-circle"></i> Update Itinerary</button>
            </div>
            <br><br><br>

        </form>
    </div>
</div>

<?php include 'includes/footer.php'; ?>

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
                <div class="mb-2"><label>New Images</label><input type="file" name="day_images_${dayCount}[]" class="form-control" multiple></div>
            </div>`;
            $('#itineraryContainer').append(html);
            $(`#dayRow_${dayCount} .summernote`).summernote({ height: 150 });
        });

        $('#addSectionBtn').click(function() {
            sectionCount++;
            let html = `
            <div class="row mb-3" id="secRow_${sectionCount}">
                <div class="col-md-4">
                    <input type="text" name="sections[${sectionCount}][heading]" class="form-control fw-bold" placeholder="Heading">
                </div>
                <div class="col-md-7">
                    <textarea name="sections[${sectionCount}][content]" class="form-control summernote" rows="2"></textarea>
                </div>
                <div class="col-md-1">
                    <button type="button" class="btn btn-danger btn-sm" onclick="$('#secRow_${sectionCount}').remove()"><i class="bi bi-trash"></i></button>
                </div>
            </div>`;
            $('#sectionContainer').append(html);
            
            // INITIALIZE EDITOR
            $(`#secRow_${sectionCount} .summernote`).summernote({
                height: 100,
                toolbar: [['style', ['bold', 'italic', 'underline', 'clear']], ['para', ['ul', 'ol', 'paragraph']]]
            });
        });
    });
</script>
