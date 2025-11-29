<?php 
include 'includes/header.php'; 
include 'config/db.php';

// Security: Only Admin
if(!isset($_SESSION['role']) || $_SESSION['role'] != 'admin') {
    echo "<script>window.location.href='dashboard.php';</script>"; 
    exit;
}

if(isset($_GET['delete_id'])) {
    $del_id = $_GET['delete_id'];
    $conn->query("DELETE FROM users WHERE id=$del_id");
    echo "<script>window.history.back();</script>";
}

// Filter Logic
$role_filter = isset($_GET['role']) ? $conn->real_escape_string($_GET['role']) : '';
$where_clause = "";
$page_title = "Manage Users";

if($role_filter == 'employee') {
    $where_clause = "WHERE role = 'employee'";
    $page_title = "Manage Employees";
} elseif($role_filter == 'agent') {
    $where_clause = "WHERE role = 'agent'";
    $page_title = "Manage Agents";
}
?>

<div class="app-content-header">
    <div class="container-fluid">
        <div class="row">
            <div class="col-sm-6"><h3 class="mb-0"><?php echo $page_title; ?></h3></div>
            <div class="col-sm-6 text-end">
                <a href="register.php" class="btn btn-primary"><i class="bi bi-person-plus"></i> Add New</a>
            </div>
        </div>
    </div>
</div>

<div class="app-content">
    <div class="container-fluid">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">List of <?php echo $role_filter ? ucfirst($role_filter) . 's' : 'All Users'; ?></h3>
                <div class="card-tools">
                    <a href="manage_users.php" class="btn btn-sm <?php echo !$role_filter?'btn-dark':'btn-outline-dark';?>">All</a>
                    <a href="manage_users.php?role=employee" class="btn btn-sm <?php echo $role_filter=='employee'?'btn-primary':'btn-outline-primary';?>">Employees</a>
                    <a href="manage_users.php?role=agent" class="btn btn-sm <?php echo $role_filter=='agent'?'btn-warning':'btn-outline-warning';?>">Agents</a>
                </div>
            </div>
            
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover table-bordered align-middle text-nowrap">
                        <thead class="table-dark">
                            <tr>
                                <th>Profile</th>
                                <th>Name / Email</th>
                                <th>Role</th>
                                <th>Dept / Profile</th>
                                <th>Contact</th>
                                <th>Joined</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $sql = "SELECT * FROM users $where_clause ORDER BY created_at DESC";
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
                                    <small class="text-muted"><?php echo $row['email']; ?></small>
                                </td>
                                <td>
                                    <?php 
                                        if($row['role']=='admin') echo '<span class="badge text-bg-danger">Admin</span>';
                                        elseif($row['role']=='employee') echo '<span class="badge text-bg-primary">Employee</span>';
                                        else echo '<span class="badge text-bg-warning">Agent</span>';
                                    ?>
                                </td>
                                <td>
                                    <div class="fw-bold"><?php echo $row['department'] ?? '-'; ?></div>
                                    <small class="text-muted"><?php echo $row['profile'] ?? '-'; ?></small>
                                </td>
                                <td><?php echo $row['phone'] ?? '-'; ?></td>
                                <td><?php echo $row['joining_date'] ? date('M d, Y', strtotime($row['joining_date'])) : '-'; ?></td>
                                <td>
                                    <a href="view_user.php?id=<?php echo $row['id']; ?>" class="btn btn-secondary btn-sm" title="View"><i class="bi bi-eye"></i></a>
                                    <a href="edit_user.php?id=<?php echo $row['id']; ?>" class="btn btn-info btn-sm text-white" title="Edit"><i class="bi bi-pencil-square"></i></a>
                                    <?php if($row['role'] != 'admin'): ?>
                                    <a href="manage_users.php?delete_id=<?php echo $row['id']; ?>" 
                                       class="btn btn-danger btn-sm" 
                                       onclick="return confirm('Delete this user?');" title="Delete"><i class="bi bi-trash"></i></a>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <?php endwhile; 
                            else: ?>
                            <tr><td colspan="7" class="text-center py-4">No records found.</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
                </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>