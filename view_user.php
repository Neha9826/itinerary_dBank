<?php 
include 'includes/header.php'; 
include 'config/db.php';

// Security
if(!isset($_SESSION['role']) || $_SESSION['role'] != 'admin') {
    echo "<script>window.location.href='dashboard.php';</script>"; 
    exit;
}

if(!isset($_GET['id'])) { header("Location: manage_users.php"); exit; }

$id = $_GET['id'];
$user = $conn->query("SELECT * FROM users WHERE id=$id")->fetch_assoc();

if(!$user) { echo "User not found"; exit; }

// Image Handling
$pic = !empty($user['profile_pic']) ? './assets/uploads/'.$user['profile_pic'] : 'https://via.placeholder.com/150';
?>

<div class="app-content-header">
    <div class="container-fluid">
        <div class="row">
            <div class="col-sm-6"><h3>User Profile</h3></div>
            <div class="col-sm-6 text-end">
                <a href="manage_users.php" class="btn btn-secondary">Back to List</a>
                <a href="edit_user.php?id=<?php echo $user['id']; ?>" class="btn btn-info text-white">Edit Profile</a>
            </div>
        </div>
    </div>
</div>

<div class="app-content">
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-4">
                <div class="card card-primary card-outline">
                    <div class="card-body box-profile text-center">
                        <div class="text-center mb-3">
                            <img class="profile-user-img img-fluid img-circle rounded-circle"
                                 src="<?php echo $pic; ?>"
                                 alt="User profile picture" 
                                 style="width: 150px; height: 150px; object-fit: cover; border: 3px solid #adb5bd;">
                        </div>

                        <h3 class="profile-username text-center"><?php echo $user['name']; ?></h3>
                        <p class="text-muted text-center"><?php echo ucfirst($user['role']); ?></p>

                        <ul class="list-group list-group-unbordered mb-3 text-start">
                            <li class="list-group-item">
                                <b>Email</b> <span class="float-end text-primary"><?php echo $user['email']; ?></span>
                            </li>
                            <li class="list-group-item">
                                <b>Department</b> <span class="float-end"><?php echo $user['department'] ?? 'N/A'; ?></span>
                            </li>
                            <li class="list-group-item">
                                <b>Profile</b> <span class="float-end fw-bold"><?php echo $user['profile'] ?? 'N/A'; ?></span>
                            </li>
                            <li class="list-group-item">
                                <b>Phone</b> <span class="float-end"><?php echo $user['phone'] ?? 'N/A'; ?></span>
                            </li>
                        </ul>
                    </div>
                </div>
                
                <div class="card mt-3">
                    <div class="card-header bg-dark text-white">
                        <h3 class="card-title">ID Proof Document</h3>
                    </div>
                    <div class="card-body text-center">
                        <?php if(!empty($user['id_proof'])): ?>
                            <i class="bi bi-file-earmark-person fs-1 text-primary"></i>
                            <p class="mt-2 text-muted"><?php echo $user['id_proof']; ?></p>
                            <a href="./assets/uploads/<?php echo $user['id_proof']; ?>" target="_blank" class="btn btn-sm btn-outline-primary">
                                <i class="bi bi-download"></i> View / Download ID
                            </a>
                        <?php else: ?>
                            <p class="text-muted">No ID Proof Uploaded</p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <div class="col-md-8">
                <div class="card">
                    <div class="card-header p-2">
                        <ul class="nav nav-pills">
                            <li class="nav-item"><a class="nav-link active" href="#details" data-bs-toggle="tab">Full Details</a></li>
                        </ul>
                    </div>
                    <div class="card-body">
                        <div class="tab-content">
                            <div class="tab-pane active" id="details">
                                <div class="row mb-3">
                                    <label class="col-sm-3 col-form-label fw-bold">Full Name:</label>
                                    <div class="col-sm-9 pt-2"><?php echo $user['name']; ?></div>
                                </div>
                                <div class="row mb-3 bg-light p-2 rounded">
                                    <label class="col-sm-3 col-form-label fw-bold">Address:</label>
                                    <div class="col-sm-9 pt-2"><?php echo nl2br($user['address'] ?? 'No address provided'); ?></div>
                                </div>
                                <div class="row mb-3">
                                    <label class="col-sm-3 col-form-label fw-bold">Date of Joining:</label>
                                    <div class="col-sm-9 pt-2"><?php echo $user['joining_date'] ? date('F d, Y', strtotime($user['joining_date'])) : 'N/A'; ?></div>
                                </div>
                                
                                <?php if($user['role'] == 'employee'): ?>
                                <div class="row mb-3 bg-light p-2 rounded">
                                    <label class="col-sm-3 col-form-label fw-bold">Monthly Salary:</label>
                                    <div class="col-sm-9 pt-2 text-success fw-bold">
                                        <?php echo $user['salary'] > 0 ? '$' . number_format($user['salary'], 2) : 'Not Set'; ?>
                                    </div>
                                </div>
                                <?php endif; ?>

                                <div class="row mb-3">
                                    <label class="col-sm-3 col-form-label fw-bold">Account Created:</label>
                                    <div class="col-sm-9 pt-2"><?php echo date('M d, Y H:i A', strtotime($user['created_at'])); ?></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <?php if($user['role'] == 'employee'): ?>
                <div class="card mt-3">
                    <div class="card-header bg-warning text-dark">
                        <h3 class="card-title">Recent Activity (Last 5 Itineraries Sent)</h3>
                    </div>
                    <div class="card-body p-0">
                        <table class="table table-striped mb-0">
                            <thead>
                                <tr>
                                    <th>Title</th>
                                    <th>Sent To Agent</th>
                                    <th>Date</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php 
                                $act_sql = "SELECT s.custom_title, s.sent_at, u.name as agent_name 
                                            FROM sent_itineraries s
                                            JOIN users u ON s.agent_id = u.id
                                            WHERE s.employee_id = $id ORDER BY s.sent_at DESC LIMIT 5";
                                $acts = $conn->query($act_sql);
                                if($acts->num_rows > 0):
                                    while($a = $acts->fetch_assoc()):
                                ?>
                                <tr>
                                    <td><?php echo $a['custom_title']; ?></td>
                                    <td><?php echo $a['agent_name']; ?></td>
                                    <td><?php echo date('M d, Y', strtotime($a['sent_at'])); ?></td>
                                </tr>
                                <?php endwhile; else: ?>
                                    <tr><td colspan="3" class="text-center p-3">No recent activity</td></tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>