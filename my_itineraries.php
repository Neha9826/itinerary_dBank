<?php 
include 'includes/header.php'; 
include 'config/db.php';

$my_id = $_SESSION['user_id'];
// Get itineraries sent specifically to this agent
$sql = "SELECT si.*, u.name as emp_name 
        FROM sent_itineraries si 
        JOIN users u ON si.employee_id = u.id 
        WHERE si.agent_id = $my_id 
        ORDER BY sent_at DESC";
$result = $conn->query($sql);
?>

<div class="app-content-header"><h3>My Received Itineraries</h3></div>

<div class="app-content">
    <div class="container-fluid">
        <table class="table table-bordered table-hover bg-white">
            <thead>
                <tr>
                    <th>Title</th>
                    <th>From Employee</th>
                    <th>Price</th>
                    <th>Date</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php while($row = $result->fetch_assoc()): ?>
                <tr>
                    <td><?php echo $row['custom_title']; ?></td>
                    <td><?php echo $row['emp_name']; ?></td>
                    <td>$<?php echo $row['final_price']; ?></td>
                    <td><?php echo $row['sent_at']; ?></td>
                    <td>
                        <a href="generate_doc.php?id=<?php echo $row['id']; ?>" class="btn btn-primary btn-sm">
                            <i class="bi bi-file-word"></i> Download Word
                        </a>
                    </td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</div>

<?php include 'includes/footer.php'; ?>