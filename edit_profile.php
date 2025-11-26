<?php 
include 'includes/header.php'; 
include 'config/db.php';

$id = $_SESSION['user_id'];
$user = $conn->query("SELECT * FROM users WHERE id=$id")->fetch_assoc();
?>

<div class="app-content-header">
    <div class="container-fluid">
        <h3>Edit Profile</h3>
    </div>
</div>

<div class="app-content">
    <div class="container-fluid">
        <form action="actions/update_my_profile.php" method="POST" enctype="multipart/form-data">
            <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
            
            <div class="row">
                <div class="col-md-8">
                    <div class="card card-warning card-outline">
                        <div class="card-header"><h5 class="card-title">General Information</h5></div>
                        <div class="card-body">
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label class="form-label text-muted">Full Name (Contact Admin to change)</label>
                                    <input type="text" class="form-control bg-light" value="<?php echo $user['name']; ?>" readonly>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label text-muted">Email (Login ID)</label>
                                    <input type="email" class="form-control bg-light" value="<?php echo $user['email']; ?>" readonly>
                                </div>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Phone Number</label>
                                <input type="text" name="phone" class="form-control" value="<?php echo $user['phone']; ?>" required>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Residential Address</label>
                                <textarea name="address" class="form-control" rows="3" required><?php echo $user['address']; ?></textarea>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="card card-secondary">
                        <div class="card-header"><h5 class="card-title">Uploads</h5></div>
                        <div class="card-body">
                            <div class="mb-3">
                                <label class="form-label">Profile Picture</label>
                                <?php if($user['profile_pic']): ?>
                                    <div class="mb-2"><img src="./assets/uploads/<?php echo $user['profile_pic']; ?>" width="50" class="rounded"></div>
                                <?php endif; ?>
                                <input type="file" name="profile_pic" class="form-control">
                                <input type="hidden" name="old_profile_pic" value="<?php echo $user['profile_pic']; ?>">
                            </div>

                            <div class="mb-3">
                                <label class="form-label">ID Proof (PDF/Image)</label>
                                <div class="form-text mb-2">Please upload your Aadhar, Passport, or DL.</div>
                                <input type="file" name="id_proof" class="form-control">
                                <input type="hidden" name="old_id_proof" value="<?php echo $user['id_proof']; ?>">
                            </div>
                            
                            <hr>
                            <button type="submit" class="btn btn-success w-100">Save Changes</button>
                            <a href="profile.php" class="btn btn-outline-secondary w-100 mt-2">Cancel</a>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>

<?php include 'includes/footer.php'; ?>