<?php 
include 'includes/header.php'; 
include 'config/db.php';

$id = $_GET['id'];
$master = $conn->query("SELECT * FROM master_itineraries WHERE id=$id")->fetch_assoc();

// Get agents list
$agents = $conn->query("SELECT id, name FROM users WHERE role='agent'");
?>

<div class="app-content-header"><h3>Customize: <?php echo $master['title']; ?></h3></div>

<div class="app-content">
    <div class="container-fluid">
        <form action="actions/save_custom.php" method="POST">
            <input type="hidden" name="master_id" value="<?php echo $master['id']; ?>">
            
            <div class="card">
                <div class="card-body">
                    <div class="mb-3">
                        <label>Custom Title</label>
                        <input type="text" name="custom_title" class="form-control" value="Custom: <?php echo $master['title']; ?>">
                    </div>
                    
                    <div class="mb-3">
                        <label>Select Agent to Send To</label>
                        <select name="agent_id" class="form-control" required>
                            <option value="">-- Select Agent --</option>
                            <?php while($agent = $agents->fetch_assoc()): ?>
                                <option value="<?php echo $agent['id']; ?>"><?php echo $agent['name']; ?></option>
                            <?php endwhile; ?>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label>Edit Content (This will NOT change the Master DB)</label>
                        <textarea name="custom_content" class="form-control" rows="15"><?php echo htmlspecialchars($master['content']); ?></textarea>
                    </div>
                    
                    <div class="mb-3">
                        <label>Final Price</label>
                        <input type="number" name="final_price" class="form-control" value="<?php echo $master['base_price']; ?>">
                    </div>
                </div>
                <div class="card-footer">
                    <button type="submit" class="btn btn-primary">Send to Agent</button>
                </div>
            </div>
        </form>
    </div>
</div>

<?php include 'includes/footer.php'; ?>