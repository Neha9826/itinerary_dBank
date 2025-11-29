<?php 
include 'includes/header.php'; 
include 'config/db.php';

// Security: Agents Only
if($_SESSION['role'] != 'agent') {
    echo "<script>window.location.href='dashboard.php';</script>"; 
    exit;
}

$my_id = $_SESSION['user_id'];

// --- FETCH STATS ---
$count_sql = "SELECT count(*) as total FROM sent_itineraries WHERE agent_id = $my_id";
$total_received = $conn->query($count_sql)->fetch_assoc()['total'];
?>

<div class="app-content-header">
    <div class="container-fluid">
        <div class="row">
            <div class="col-sm-6">
                <h3 class="mb-0">Received Itineraries</h3>
            </div>
        </div>
    </div>
</div>

<div class="app-content">
    <div class="container-fluid">
        <div class="card card-outline card-primary mt-3">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="bi bi-inbox me-2"></i> Inbox
                </h3>
                <div class="card-tools">
                    <button type="button" class="btn btn-tool" data-lte-toggle="card-collapse">
                        <i class="bi bi-dash-lg"></i>
                    </button>
                </div>
            </div>
            
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-striped table-hover align-middle text-nowrap">
                        <thead class="table-dark">
                            <tr>
                                <th>#</th>
                                <th>Itinerary Title</th>
                                <th>Sent By</th>
                                <th>Price Quote</th>
                                <th>Date Received</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            // Fetch Itineraries
                            $sql = "SELECT s.*, u.name as emp_name 
                                    FROM sent_itineraries s 
                                    JOIN users u ON s.employee_id = u.id 
                                    WHERE s.agent_id = $my_id 
                                    ORDER BY s.sent_at DESC";
                            
                            $result = $conn->query($sql);

                            if($result->num_rows > 0):
                                $i = 1;
                                while($row = $result->fetch_assoc()):
                            ?>
                            <tr>
                                <td><?php echo $i++; ?></td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div class="icon-square bg-light text-primary rounded-2 me-2 p-2">
                                            <i class="bi bi-file-earmark-richtext fs-4"></i>
                                        </div>
                                        <div>
                                            <span class="fw-bold text-primary"><?php echo $row['custom_title']; ?></span>
                                            <br>
                                            <small class="text-muted">ID: #<?php echo $row['id']; ?></small>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <span class="badge bg-secondary">
                                        <i class="bi bi-person-fill"></i> <?php echo $row['emp_name']; ?>
                                    </span>
                                </td>
                                <td>
                                    <h5 class="mb-0 text-success fw-bold">
                                        Rs. <?php echo number_format($row['final_price']); ?>
                                    </h5>
                                </td>
                                <td>
                                    <?php echo date('M d, Y', strtotime($row['sent_at'])); ?>
                                    <small class="d-block text-muted"><?php echo date('h:i A', strtotime($row['sent_at'])); ?></small>
                                </td>
                                <td>
                                    <div class="btn-group">
                                        <a href="view_sent_itinerary.php?id=<?php echo $row['id']; ?>" class="btn btn-outline-info" title="Preview Online">
                                            <i class="bi bi-eye"></i>
                                        </a>

                                        <a href="download_custom.php?id=<?php echo $row['id']; ?>" class="btn btn-outline-dark" title="Download Word Doc">
                                            <i class="bi bi-file-word"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                            <?php endwhile; else: ?>
                            <tr>
                                <td colspan="6" class="text-center py-5 text-muted">
                                    <div class="d-flex flex-column align-items-center">
                                        <i class="bi bi-inbox fs-1 mb-2 opacity-50"></i>
                                        <h5>Inbox Empty</h5>
                                        <p>You haven't received any itineraries yet.</p>
                                    </div>
                                </td>
                            </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div> </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>