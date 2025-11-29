<?php 
include 'includes/header.php'; 
include 'config/db.php';

// Security: Employees Only
if($_SESSION['role'] != 'employee') {
    echo "<script>window.location.href='dashboard.php';</script>"; 
    exit;
}

$my_id = $_SESSION['user_id'];
?>

<div class="app-content-header">
    <div class="container-fluid">
        <div class="row">
            <div class="col-sm-6"><h3>My Sent Itineraries</h3></div>
            <div class="col-sm-6 text-end">
                <a href="view_masters.php" class="btn btn-primary">
                    <i class="bi bi-plus-lg"></i> Send New
                </a>
            </div>
        </div>
    </div>
</div>

<div class="app-content">
    <div class="container-fluid">
        <div class="card card-outline card-success">
            <div class="card-header">
                <h3 class="card-title">History Log</h3>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-striped table-hover align-middle">
                        <thead class="table-dark">
                            <tr>
                                <th>#</th>
                                <th>Quote Title</th>
                                <th>Sent To (Agent)</th>
                                <th>Price Quoted</th>
                                <th>Date Sent</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            // Join with Users table to get Agent Name
                            $sql = "SELECT s.*, u.name as agent_name 
                                    FROM sent_itineraries s 
                                    JOIN users u ON s.agent_id = u.id 
                                    WHERE s.employee_id = $my_id 
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
                                    <i class="bi bi-person-badge"></i> <?php echo $row['agent_name']; ?>
                                </td>
                                <td class="text-success fw-bold">
                                    Rs. <?php echo number_format($row['final_price']); ?>/-
                                </td>
                                <td>
                                    <?php echo date('M d, Y h:i A', strtotime($row['sent_at'])); ?>
                                </td>
                                <td>
                                    <a href="download_custom.php?id=<?php echo $row['id']; ?>" class="btn btn-sm btn-outline-dark">
                                        <i class="bi bi-file-word"></i> Download Doc
                                    </a>
                                </td>
                            </tr>
                            <?php endwhile; else: ?>
                            <tr>
                                <td colspan="6" class="text-center py-4 text-muted">
                                    <i class="bi bi-inbox fs-1"></i><br>
                                    No itineraries sent yet.
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