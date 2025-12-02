<?php 
include 'includes/header.php'; 
// Only Admin Access
if($_SESSION['role'] != 'admin') { echo "<script>window.location.href='dashboard.php';</script>"; exit; }
?>

<div class="app-content-header">
    <div class="container-fluid">
        <h3>Create Professional Itinerary</h3>
    </div>
</div>

<div class="app-content">
    <div class="container-fluid">
        <form action="actions/save_itinerary_complex.php" method="POST" enctype="multipart/form-data">
            
            <div class="card card-outline card-primary mb-4">
                <div class="card-header"><h5 class="card-title">Part 0: PDF Branding</h5></div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <label class="form-label">Header Image (Top of PDF)</label>
                            <input type="file" name="header_image" class="form-control" accept="image/*">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Footer Image (Bottom of PDF)</label>
                            <input type="file" name="footer_image" class="form-control" accept="image/*">
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
                            <input type="text" name="program_title" class="form-control" placeholder="e.g. Nepal Mukhtinath Tour" required>
                        </div>
                        <div class="col-md-3">
                            <label>Hotel Category</label>
                            <input type="text" name="hotel_category" class="form-control" placeholder="e.g. Deluxe/Super Deluxe">
                        </div>
                        <div class="col-md-3">
                            <label>Duration</label>
                            <input type="text" name="duration" class="form-control" placeholder="e.g. 7 Nights 8 Days">
                        </div>
                    </div>
                    
                    <div class="row mb-3">
                        <div class="col-md-4">
                            <label>Cost Per Person</label>
                            <input type="text" name="cost" class="form-control" placeholder="e.g. Rs. 49,900/-">
                        </div>
                        <div class="col-md-4">
                            <label>For Pax Size</label>
                            <input type="number" name="pax_size" class="form-control" placeholder="e.g. 6">
                        </div>
                        <div class="col-md-4">
                            <label>Flights</label>
                            <input type="text" name="flights" class="form-control" placeholder="e.g. KTM-POK-KTM Included">
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <label>Meals</label>
                            <input type="text" name="meals" class="form-control" placeholder="e.g. Daily Breakfast & Dinner">
                        </div>
                        <div class="col-md-6">
                            <label>Transport Used</label>
                            <input type="text" name="transport" class="form-control" placeholder="e.g. AC Jeep/Scorpio">
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
                        <table class="table table-bordered" id="hotelTable">
                            <thead>
                                <tr>
                                    <th>Location</th>
                                    <th>Hotel Name</th>
                                    <th>Nights</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody id="hotelContainer">
                                </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <div class="card card-outline card-success mb-4">
                <div class="card-header d-flex justify-content-between">
                    <h5 class="card-title">Part 3: Detailed Itinerary (Day Wise)</h5>
                    <button type="button" class="btn btn-sm btn-dark" id="addDayBtn"><i class="bi bi-plus-circle"></i> Add Day</button>
                </div>
                <div class="card-body" id="itineraryContainer">
                    </div>
            </div>

            <div class="card card-outline card-danger mb-4">
                <div class="card-header d-flex justify-content-between">
                    <h5 class="card-title">Part 4: Inclusions, Exclusions & Notes</h5>
                    <button type="button" class="btn btn-sm btn-dark" id="addSectionBtn"><i class="bi bi-plus"></i> Add Section</button>
                </div>
                <div class="card-body" id="sectionContainer">
                    </div>
            </div>

            <div class="fixed-bottom bg-white p-3 shadow border-top text-end">
                <a href="dashboard.php" class="btn btn-secondary me-2">Cancel</a>
                <button type="submit" class="btn btn-success btn-lg"><i class="bi bi-save"></i> Save Itinerary</button>
            </div>
            <br><br><br> </form>
    </div>
</div>

<?php include 'includes/footer.php'; ?>

<script src="https://cdn.jsdelivr.net/npm/summernote@0.8.18/dist/summernote-lite.min.js"></script>

<script>
    $(document).ready(function() {
        // --- 1. DYNAMIC HOTELS ---
        let hotelCount = 0;
        $('#addHotelBtn').click(function() {
            hotelCount++;
            let html = `
            <tr id="hotelRow${hotelCount}">
                <td><input type="text" name="hotels[${hotelCount}][location]" class="form-control" placeholder="e.g. Kathmandu"></td>
                <td><input type="text" name="hotels[${hotelCount}][name]" class="form-control" placeholder="Hotel Name"></td>
                <td><input type="text" name="hotels[${hotelCount}][nights]" class="form-control" placeholder="e.g. 2 Nights"></td>
                <td><button type="button" class="btn btn-danger btn-sm" onclick="$('#hotelRow${hotelCount}').remove()"><i class="bi bi-trash"></i></button></td>
            </tr>`;
            $('#hotelContainer').append(html);
        });

        // --- 2. DYNAMIC ITINERARY DAYS ---
        let dayCount = 0;
        $('#addDayBtn').click(function() {
            dayCount++;
            let html = `
            <div class="border rounded p-3 mb-3 bg-light position-relative" id="dayRow${dayCount}">
                <button type="button" class="btn btn-danger btn-sm position-absolute top-0 end-0 m-2" onclick="$('#dayRow${dayCount}').remove()">X</button>
                <div class="mb-2">
                    <label class="fw-bold">Day Title</label>
                    <input type="text" name="days[${dayCount}][title]" class="form-control" placeholder="e.g. Day ${dayCount}: Arrival in Kathmandu">
                </div>
                <div class="mb-2">
                    <label>Description</label>
                    <textarea name="days[${dayCount}][desc]" class="summernote"></textarea>
                </div>
                <div class="mb-2">
                    <label>Images (Optional)</label>
                    <input type="file" name="day_images_${dayCount}[]" class="form-control" multiple>
                </div>
            </div>`;
            $('#itineraryContainer').append(html);
            
            // Initialize Editor for the new text area
            $(`#dayRow${dayCount} .summernote`).summernote({
                height: 150,
                toolbar: [
                    ['style', ['bold', 'italic', 'underline', 'clear']],
                    ['para', ['ul', 'ol', 'paragraph']],
                ]
            });
        });

        // --- 3. DYNAMIC OTHER SECTIONS (Part 4) ---
        let sectionCount = 0;
        $('#addSectionBtn').click(function() {
            sectionCount++;
            let html = `
            <div class="row mb-3" id="secRow${sectionCount}">
                <div class="col-md-4">
                    <input type="text" name="sections[${sectionCount}][heading]" class="form-control fw-bold" placeholder="Heading (e.g. Exclusions)">
                </div>
                <div class="col-md-7">
                    <textarea name="sections[${sectionCount}][content]" class="form-control summernote" rows="2" placeholder="Details..."></textarea>
                </div>
                <div class="col-md-1">
                    <button type="button" class="btn btn-danger btn-sm" onclick="$('#secRow${sectionCount}').remove()"><i class="bi bi-trash"></i></button>
                </div>
            </div>`;
            $('#sectionContainer').append(html);

            // INITIALIZE EDITOR FOR THE NEW FIELD
            $(`#secRow${sectionCount} .summernote`).summernote({
                height: 100, // Slightly smaller height for sections
                toolbar: [['style', ['bold', 'italic', 'underline', 'clear']], ['para', ['ul', 'ol', 'paragraph']]]
            });
        });

        // Trigger one hotel and one day on load
        $('#addHotelBtn').click();
        $('#addDayBtn').click();
    });
</script>