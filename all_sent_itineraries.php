<?php 
include 'includes/header.php'; 
include 'config/db.php';

// Security: Admin Only
if($_SESSION['role'] != 'admin') {
    echo "<script>window.location.href='dashboard.php';</script>"; 
    exit;
}
?>

<div class="app-content-header">
    <div class="container-fluid">
        <div class="row">
            <div class="col-sm-6"><h3>All Sent Itineraries</h3></div>
            <div class="col-sm-6 text-end">
                <a href="dashboard.php" class="btn btn-secondary">Back to Dashboard</a>
            </div>
        </div>
    </div>
</div>

<div class="app-content">
    <div class="container-fluid">
        <div class="card card-outline card-danger">
            <div class="card-header">
                <h3 class="card-title">Complete History Log</h3>
                <div class="card-tools">
                    </div>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-striped table-hover align-middle">
                        <thead class="table-dark">
                            <tr>
                                <th>#</th>
                                <th>Quote Title</th>
                                <th>Sent By (Emp)</th>
                                <th>Sent To (Agent)</th>
                                <th>Price Quoted</th>
                                <th>Date Sent</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            // Advanced Query: Join Users table TWICE (once for Employee, once for Agent)
                            $sql = "SELECT s.*, 
                                           e.name as emp_name, 
                                           a.name as agent_name 
                                    FROM sent_itineraries s 
                                    JOIN users e ON s.employee_id = e.id 
                                    JOIN users a ON s.agent_id = a.id 
                                    ORDER BY s.sent_at DESC";
                            
                            $result = $conn->query($sql);

                            if($result->num_rows > 0):
                                $i = 1;
                                while($row = $result->fetch_assoc()):
                            ?>
                            <tr>
                                <td><?php echo $i++; ?></td>
                                <td>
                                    <span class="fw-bold text-primary"><?php echo $row['custom_title']; ?></span>
                                </td>
                                <td>
                                    <span class="badge bg-info text-dark"><i class="bi bi-person"></i> <?php echo $row['emp_name']; ?></span>
                                </td>
                                <td>
                                    <span class="badge bg-warning text-dark"><i class="bi bi-briefcase"></i> <?php echo $row['agent_name']; ?></span>
                                </td>
                                <td class="text-success fw-bold">
                                    Rs. <?php echo number_format($row['final_price']); ?>/-
                                </td>
                                <td>
                                    <?php echo date('M d, Y h:i A', strtotime($row['sent_at'])); ?>
                                </td>
                                <td>
                                    <a href="download_custom.php?id=<?php echo $row['id']; ?>" class="btn btn-sm btn-outline-dark">
                                        <i class="bi bi-file-word"></i> Download
                                    </a>
                                </td>
                            </tr>
                            <?php endwhile; else: ?>
                            <tr>
                                <td colspan="7" class="text-center py-5 text-muted">
                                    <i class="bi bi-inbox-fill fs-1"></i><br>
                                    No itineraries have been sent yet.
                                </td>
                            </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>