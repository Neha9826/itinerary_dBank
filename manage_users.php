<?php 
include 'includes/header.php'; 
include 'config/db.php';

// Security: Only Admin
if(!isset($_SESSION['role']) || $_SESSION['role'] != 'admin') {
    echo "<script>window.location.href='dashboard.php';</script>"; 
    exit;
}

// Handle Delete Request
if(isset($_GET['delete_id'])) {
    $del_id = $_GET['delete_id'];
    $conn->query("DELETE FROM users WHERE id=$del_id");
    echo "<script>window.location.href='manage_users.php';</script>";
}
?>

<div class="app-content-header">
    <div class="container-fluid">
        <div class="row">
            <div class="col-sm-6"><h3 class="mb-0">Manage Users</h3></div>
            <div class="col-sm-6 text-end">
                <a href="register.php" class="btn btn-primary"><i class="bi bi-person-plus"></i> Add New User</a>
            </div>
        </div>
    </div>
</div>

<div class="app-content">
    <div class="container-fluid">
        <div class="card">
            <div class="card-body p-0">
                <table class="table table-hover table-bordered align-middle">
                    <thead class="table-dark">
                        <tr>
                            <th>Profile Pic</th>
                            <th>Name / Role</th>
                            <th>Dept / Profile</th> <th>Contact</th>
                            <th>Joined</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $sql = "SELECT * FROM users ORDER BY role ASC, created_at DESC";
                        $result = $conn->query($sql);
                        
                        if($result->num_rows > 0):
                            
                        while($row = $result->fetch_assoc()):
                            $pic = !empty($row['profile_pic']) ? './assets/uploads/'.$row['profile_pic'] : 'https://via.placeholder.com/50';
                        ?>
                        <tr>
                            <td class="text-center">
                                <img src="<?php echo $pic; ?>" class="rounded-circle" width="40" height="40" style="object-fit:cover;">
                            </td>
                            <td>
                                <strong><?php echo $row['name']; ?></strong><br>
                                <?php 
                                    if($row['role']=='admin') echo '<span class="badge text-bg-danger">Admin</span>';
                                    elseif($row['role']=='employee') echo '<span class="badge text-bg-primary">Employee</span>';
                                    else echo '<span class="badge text-bg-warning">Agent</span>';
                                ?>
                            </td>
                            <td>
                                <div class="fw-bold"><?php echo $row['department'] ?? '-'; ?></div>
                                <div class="text-muted small"><?php echo $row['profile'] ?? '-'; ?></div>
                            </td>
                            <td>
                                <div><?php echo $row['email']; ?></div>
                                <small class="text-muted"><?php echo $row['phone'] ?? ''; ?></small>
                            </td>
                            <td><?php echo $row['joining_date'] ? date('M d, Y', strtotime($row['joining_date'])) : '-'; ?></td>
                            <td>
                                <a href="view_user.php?id=<?php echo $row['id']; ?>" class="btn btn-secondary btn-sm"><i class="bi bi-eye"></i></a>
                                <a href="edit_user.php?id=<?php echo $row['id']; ?>" class="btn btn-info btn-sm text-white"><i class="bi bi-pencil-square"></i></a>
                                <?php if($row['role'] != 'admin'): ?>
                                <a href="manage_users.php?delete_id=<?php echo $row['id']; ?>" class="btn btn-danger btn-sm" onclick="return confirm('Delete user?');"><i class="bi bi-trash"></i></a>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endwhile; 
                        else: ?>
                        <tr><td colspan="6" class="text-center">No users found</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>