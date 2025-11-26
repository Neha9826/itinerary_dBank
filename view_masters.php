<?php 
include 'includes/header.php'; 
include 'config/db.php';
?>

<div class="app-content-header">
    <div class="container-fluid">
        <div class="row">
            <div class="col-sm-6"><h3>Master Itineraries Data Bank</h3></div>
            <?php if($_SESSION['role'] == 'admin'): ?>
            <div class="col-sm-6 text-end">
                <a href="create_itinerary.php" class="btn btn-primary"><i class="bi bi-plus-lg"></i> Create New</a>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<div class="app-content">
    <div class="container-fluid">
        <div class="row">
            <?php
            $sql = "SELECT * FROM master_itineraries ORDER BY created_at DESC";
            $result = $conn->query($sql);

            if($result->num_rows > 0):
                while($row = $result->fetch_assoc()):
                    // 1. DECODE THE JSON
                    $data = json_decode($row['content'], true);
                    
                    // 2. EXTRACT KEY DETAILS (Handle both old text data and new JSON data)
                    $duration = $data['program']['duration'] ?? 'N/A';
                    $category = $data['program']['category'] ?? 'Standard';
                    
                    // 3. IMAGE LOGIC
                    $img = !empty($row['header_image']) ? './assets/uploads/itineraries/'.$row['header_image'] : './assets/img/default-tour.jpg';
            ?>
            <div class="col-md-4">
                <div class="card mb-4 shadow-sm card-outline card-primary h-100">
                    <div style="height: 180px; overflow: hidden; background: #f0f0f0;">
                        <img src="<?php echo $img; ?>" class="card-img-top" alt="Header" style="width: 100%; height: 100%; object-fit: cover;">
                    </div>
                    
                    <div class="card-body d-flex flex-column">
                        <h5 class="card-title fw-bold text-truncate" title="<?php echo $row['title']; ?>">
                            <?php echo $row['title']; ?>
                        </h5>
                        
                        <div class="mt-2 mb-3">
                            <span class="badge bg-info text-dark"><i class="bi bi-clock"></i> <?php echo $duration; ?></span>
                            <span class="badge bg-secondary"><i class="bi bi-building"></i> <?php echo $category; ?></span>
                        </div>
                        
                        <p class="card-text text-muted small">
                            Base Price: <strong class="text-success fs-6">$<?php echo number_format($row['base_price']); ?></strong><br>
                            Destination: <?php echo $row['destination']; ?>
                        </p>

                        <div class="mt-auto d-flex gap-2">
                            <a href="preview_itinerary.php?id=<?php echo $row['id']; ?>" class="btn btn-outline-primary flex-fill">
                                <i class="bi bi-eye"></i> Preview
                            </a>
                            
                            <a href="customize_itinerary.php?id=<?php echo $row['id']; ?>" class="btn btn-success flex-fill">
                                <i class="bi bi-send"></i> Send Quote
                            </a>
                        </div>
                    </div>
                </div>
            </div>
            <?php endwhile; else: ?>
                <div class="col-12 text-center py-5 text-muted">
                    <i class="bi bi-folder-x fs-1"></i>
                    <p>No Itineraries found in the Data Bank.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>