<?php 
include 'includes/header.php'; 
include 'config/db.php';

if($_SESSION['role'] != 'admin') { echo "<script>window.location.href='dashboard.php';</script>"; exit; }
if(!isset($_GET['id'])) { header("Location: manage_users.php"); exit; }

$id = $_GET['id'];
$user = $conn->query("SELECT * FROM users WHERE id=$id")->fetch_assoc();

// --- FETCH DROPDOWN OPTIONS ---
$dept_result = $conn->query("SELECT name FROM departments ORDER BY name ASC");
$prof_result = $conn->query("SELECT name FROM profiles ORDER BY name ASC");
?>

<div class="app-content-header">
    <div class="container-fluid">
        <h3>Edit User Details: <?php echo $user['name']; ?></h3>
    </div>
</div>

<div class="app-content">
    <div class="container-fluid">
        <form action="actions/update_user.php" method="POST" enctype="multipart/form-data">
            <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
            
            <div class="row">
                <div class="col-md-8">
                    <div class="card card-primary card-outline mb-4">
                        <div class="card-header"><h5 class="card-title">Professional Details</h5></div>
                        <div class="card-body">
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label class="form-label">Full Name</label>
                                    <input type="text" name="name" class="form-control" value="<?php echo $user['name']; ?>" required>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Email</label>
                                    <input type="email" name="email" class="form-control" value="<?php echo $user['email']; ?>" readonly>
                                </div>
                            </div>

                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label class="form-label">Department</label>
                                    <select class="form-select select2-dynamic" name="department">
                                        <option></option> <?php if(!empty($user['department'])): ?>
                                            <option value="<?php echo $user['department']; ?>" selected><?php echo $user['department']; ?></option>
                                        <?php endif; ?>

                                        <?php 
                                        // Reset pointer to start just in case
                                        $dept_result->data_seek(0); 
                                        while($d = $dept_result->fetch_assoc()): 
                                            // Don't repeat if it matches the current user's dept
                                            if($d['name'] == $user['department']) continue; 
                                        ?>
                                            <option value="<?php echo $d['name']; ?>"><?php echo $d['name']; ?></option>
                                        <?php endwhile; ?>
                                    </select>
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label">Job Profile / Designation</label>
                                    <select class="form-select select2-dynamic" name="profile">
                                        <option></option>
                                        <?php if(!empty($user['profile'])): ?>
                                            <option value="<?php echo $user['profile']; ?>" selected><?php echo $user['profile']; ?></option>
                                        <?php endif; ?>

                                        <?php 
                                        $prof_result->data_seek(0);
                                        while($p = $prof_result->fetch_assoc()): 
                                            if($p['name'] == $user['profile']) continue; 
                                        ?>
                                            <option value="<?php echo $p['name']; ?>"><?php echo $p['name']; ?></option>
                                        <?php endwhile; ?>
                                    </select>
                                </div>
                            </div>
                            
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label class="form-label">Phone Number</label>
                                    <input type="text" name="phone" class="form-control" value="<?php echo $user['phone']; ?>">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Salary (Monthly)</label>
                                    <input type="number" step="0.01" name="salary" class="form-control" value="<?php echo $user['salary']; ?>">
                                </div>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Address</label>
                                <textarea name="address" class="form-control" rows="2"><?php echo $user['address']; ?></textarea>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Date of Joining</label>
                                <input type="date" name="joining_date" class="form-control" value="<?php echo $user['joining_date']; ?>">
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="card card-secondary mb-4">
                        <div class="card-header"><h5 class="card-title">Account & Files</h5></div>
                        <div class="card-body">
                            <div class="mb-3">
                                <label class="form-label">Role</label>
                                <select name="role" class="form-select">
                                    <option value="admin" <?php if($user['role']=='admin') echo 'selected'; ?>>Admin</option>
                                    <option value="employee" <?php if($user['role']=='employee') echo 'selected'; ?>>Employee</option>
                                    <option value="agent" <?php if($user['role']=='agent') echo 'selected'; ?>>Agent</option>
                                </select>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Profile Picture</label>
                                <?php if($user['profile_pic']): ?>
                                    <div class="mb-2"><img src="./assets/uploads/<?php echo $user['profile_pic']; ?>" width="50" class="rounded"></div>
                                <?php endif; ?>
                                <input type="file" name="profile_pic" class="form-control">
                                <input type="hidden" name="old_profile_pic" value="<?php echo $user['profile_pic']; ?>">
                            </div>

                            <div class="mb-3">
                                <label class="form-label">ID Proof</label>
                                <?php if($user['id_proof']): ?>
                                    <div class="mb-2"><a href="./assets/uploads/<?php echo $user['id_proof']; ?>" target="_blank" class="badge text-bg-info">View ID</a></div>
                                <?php endif; ?>
                                <input type="file" name="id_proof" class="form-control">
                                <input type="hidden" name="old_id_proof" value="<?php echo $user['id_proof']; ?>">
                            </div>
                        </div>
                    </div>
                    
                    <button type="submit" class="btn btn-success w-100 btn-lg">Update User</button>
                    <a href="manage_users.php" class="btn btn-outline-secondary w-100 mt-2">Cancel</a>
                </div>
            </div>
        </form>
    </div>
</div>

<?php include 'includes/footer.php'; ?>