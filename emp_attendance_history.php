<?php 
include 'includes/header.php'; 
include 'config/db.php';

if($_SESSION['role'] != 'admin') { echo "<script>window.location.href='dashboard.php';</script>"; exit; }
if(!isset($_GET['id'])) { header("Location: attendance_report.php"); exit; }

$user_id = $_GET['id'];
$user = $conn->query("SELECT name, email, profile_pic FROM users WHERE id=$user_id")->fetch_assoc();
?>

<div class="app-content-header">
    <div class="container-fluid">
        <div class="row">
            <div class="col-sm-6">
                <h3>Attendance History: <span class="text-primary"><?php echo $user['name']; ?></span></h3>
            </div>
            <div class="col-sm-6 text-end">
                <a href="attendance_report.php" class="btn btn-secondary">Back to Reports</a>
            </div>
        </div>
    </div>
</div>

<div class="app-content">
    <div class="container-fluid">
        
        <div class="card mb-4 card-outline card-primary">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <?php $pic = !empty($user['profile_pic']) ? './assets/uploads/'.$user['profile_pic'] : 'https://via.placeholder.com/60'; ?>
                    <img src="<?php echo $pic; ?>" class="rounded-circle me-3 border" width="60" height="60" style="object-fit:cover;">
                    <div>
                        <h5 class="mb-0"><?php echo $user['name']; ?></h5>
                        <p class="text-muted mb-0"><?php echo $user['email']; ?></p>
                    </div>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-header bg-dark text-white">
                <h3 class="card-title">All Time Logs</h3>
            </div>
            <div class="card-body p-0">
                <table class="table table-hover table-striped">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Login</th>
                            <th>Logout</th>
                            <th>Work Duration</th>
                            <th>Break Duration</th>
                            <th>Breaks Taken</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $sql = "SELECT * FROM attendance WHERE user_id = $user_id ORDER BY date DESC";
                        $result = $conn->query($sql);

                        if($result->num_rows > 0):
                            while($row = $result->fetch_assoc()):
                                $att_id = $row['id'];
                                
                                // Calc Breaks
                                $break_sec = 0;
                                $break_count = 0;
                                $b_res = $conn->query("SELECT start_time, end_time FROM breaks WHERE attendance_id = $att_id");
                                while($b = $b_res->fetch_assoc()) {
                                    $break_count++;
                                    if($b['end_time']) {
                                        $break_sec += (strtotime($b['end_time']) - strtotime($b['start_time']));
                                    }
                                }
                                $bh = floor($break_sec / 3600);
                                $bm = floor(($break_sec % 3600) / 60);
                        ?>
                        <tr>
                            <td><?php echo date('M d, Y', strtotime($row['date'])); ?></td>
                            <td><span class="text-success"><?php echo date('h:i A', strtotime($row['login_time'])); ?></span></td>
                            <td>
                                <?php echo $row['logout_time'] ? date('h:i A', strtotime($row['logout_time'])) : '<span class="badge bg-warning">Active</span>'; ?>
                            </td>
                            <td class="fw-bold"><?php echo $row['total_hours']; ?></td>
                            <td class="text-danger"><?php echo sprintf('%02dh %02dm', $bh, $bm); ?></td>
                            <td>
                                <?php if($break_count > 0): ?>
                                    <span class="badge bg-secondary"><?php echo $break_count; ?> Breaks</span>
                                    <button class="btn btn-xs btn-link" data-bs-toggle="collapse" data-bs-target="#breaks_<?php echo $att_id; ?>">View</button>
                                    
                                    <div class="collapse mt-2" id="breaks_<?php echo $att_id; ?>">
                                        <small class="text-muted">
                                            <?php 
                                            // Reset pointer to list reasons
                                            $b_res->data_seek(0);
                                            while($bd = $b_res->fetch_assoc()) { /* You would need to fetch reason in the SQL above if you want to show it here */ }
                                            echo "Detailed breakdown available in daily view."; 
                                            ?>
                                        </small>
                                    </div>
                                <?php else: ?>
                                    -
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endwhile; else: ?>
                        <tr><td colspan="6" class="text-center">No history found.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>