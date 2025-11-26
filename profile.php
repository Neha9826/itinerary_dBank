<?php 
include 'includes/header.php'; 
include 'config/db.php';

// Get Current User ID
$id = $_SESSION['user_id'];
$user = $conn->query("SELECT * FROM users WHERE id=$id")->fetch_assoc();

// Image Logic
$pic = !empty($user['profile_pic']) ? './assets/uploads/'.$user['profile_pic'] : 'https://via.placeholder.com/150';
?>

<div class="app-content-header">
    <div class="container-fluid">
        <div class="row">
            <div class="col-sm-6"><h3>My Profile</h3></div>
            <div class="col-sm-6 text-end">
                <a href="edit_profile.php" class="btn btn-primary"><i class="bi bi-pencil-square"></i> Edit My Details</a>
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
                                 style="width: 150px; height: 150px; object-fit: cover; border: 3px solid #0d6efd;">
                        </div>

                        <h3 class="profile-username text-center"><?php echo $user['name']; ?></h3>
                        <p class="text-muted text-center"><?php echo ucfirst($user['role']); ?></p>

                        <ul class="list-group list-group-unbordered mb-3 text-start">
                            <li class="list-group-item">
                                <b>Department</b> <span class="float-end"><?php echo $user['department'] ?? '-'; ?></span>
                            </li>
                            <li class="list-group-item">
                                <b>Date Joined</b> <span class="float-end"><?php echo $user['joining_date'] ? date('M d, Y', strtotime($user['joining_date'])) : '-'; ?></span>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>

            <div class="col-md-8">
                <div class="card">
                    <div class="card-header p-2">
                        <h5 class="card-title p-2">About Me</h5>
                    </div>
                    <div class="card-body">
                        <div class="row mb-3 border-bottom pb-2">
                            <div class="col-sm-3 fw-bold">Email</div>
                            <div class="col-sm-9 text-muted"><?php echo $user['email']; ?></div>
                        </div>
                        <div class="row mb-3 border-bottom pb-2">
                            <div class="col-sm-3 fw-bold">Phone Number</div>
                            <div class="col-sm-9"><?php echo $user['phone'] ?? 'Not Updated'; ?></div>
                        </div>
                        <div class="row mb-3 border-bottom pb-2">
                            <div class="col-sm-3 fw-bold">Address</div>
                            <div class="col-sm-9"><?php echo nl2br($user['address'] ?? 'Not Updated'); ?></div>
                        </div>
                        
                        <div class="row mb-3">
                            <div class="col-sm-3 fw-bold">My Documents</div>
                            <div class="col-sm-9">
                                <?php if(!empty($user['id_proof'])): ?>
                                    <a href="./assets/uploads/<?php echo $user['id_proof']; ?>" target="_blank" class="btn btn-sm btn-outline-info">
                                        <i class="bi bi-file-earmark-check"></i> View Uploaded ID Proof
                                    </a>
                                <?php else: ?>
                                    <span class="text-danger">No ID Proof Uploaded</span>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>