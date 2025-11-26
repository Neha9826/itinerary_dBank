<?php 
include 'includes/header.php'; 
include 'config/db.php';

// --- DATA FETCHING (ADMIN ONLY) ---
if($_SESSION['role'] == 'admin') {
    $emp_count = $conn->query("SELECT count(*) as total FROM users WHERE role='employee'")->fetch_assoc()['total'];
    $agent_count = $conn->query("SELECT count(*) as total FROM users WHERE role='agent'")->fetch_assoc()['total'];
    $master_count = $conn->query("SELECT count(*) as total FROM master_itineraries")->fetch_assoc()['total'];
    $sent_count = $conn->query("SELECT count(*) as total FROM sent_itineraries")->fetch_assoc()['total'];
}
?>

<div class="app-content-header">
    <div class="container-fluid">
        <div class="row">
            <div class="col-sm-6"><h3 class="mb-0">Dashboard</h3></div>
        </div>
    </div>
</div>

<div class="app-content">
    <div class="container-fluid">
        
        <?php if($_SESSION['role'] == 'admin'): ?>
        <div class="row">
            <div class="col-lg-3 col-6">
                <div class="small-box text-bg-primary">
                    <div class="inner">
                        <h3><?php echo $emp_count; ?></h3>
                        <p>Total Employees</p>
                    </div>
                    <div class="small-box-icon"><i class="bi bi-people-fill"></i></div>
                    <a href="manage_users.php" class="small-box-footer link-light link-underline-opacity-0 link-underline-opacity-50-hover">
                        Manage Users <i class="bi bi-link-45deg"></i>
                    </a>
                </div>
            </div>
            
            <div class="col-lg-3 col-6">
                <div class="small-box text-bg-warning">
                    <div class="inner">
                        <h3><?php echo $agent_count; ?></h3>
                        <p>Total Agents</p>
                    </div>
                    <div class="small-box-icon"><i class="bi bi-briefcase-fill"></i></div>
                    <a href="manage_users.php" class="small-box-footer link-dark link-underline-opacity-0 link-underline-opacity-50-hover">
                        View Agents <i class="bi bi-link-45deg"></i>
                    </a>
                </div>
            </div>

            <div class="col-lg-3 col-6">
                <div class="small-box text-bg-success">
                    <div class="inner">
                        <h3><?php echo $master_count; ?></h3>
                        <p>Master Itineraries</p>
                    </div>
                    <div class="small-box-icon"><i class="bi bi-file-earmark-richtext"></i></div>
                    <a href="create_itinerary.php" class="small-box-footer link-light link-underline-opacity-0 link-underline-opacity-50-hover">
                        Create New <i class="bi bi-plus-circle"></i>
                    </a>
                </div>
            </div>

            <div class="col-lg-3 col-6">
                <div class="small-box text-bg-danger">
                    <div class="inner">
                        <h3><?php echo $sent_count; ?></h3>
                        <p>Itineraries Sent</p>
                    </div>
                    <div class="small-box-icon"><i class="bi bi-send-fill"></i></div>
                    <a href="#" class="small-box-footer link-light link-underline-opacity-0 link-underline-opacity-50-hover">
                        More info <i class="bi bi-link-45deg"></i>
                    </a>
                </div>
            </div>
        </div>
        
        <div class="row mt-3">
            <div class="col-12">
                <div class="card">
                    <div class="card-header"><h3 class="card-title">Recent System Activity</h3></div>
                    <div class="card-body p-0">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Activity</th>
                                    <th>User</th>
                                    <th>Time</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                // Fetch last 5 sent itineraries as "activity"
                                $act_sql = "SELECT s.custom_title, u.name, s.sent_at 
                                            FROM sent_itineraries s 
                                            JOIN users u ON s.employee_id = u.id 
                                            ORDER BY s.sent_at DESC LIMIT 5";
                                $acts = $conn->query($act_sql);
                                while($a = $acts->fetch_assoc()):
                                ?>
                                <tr>
                                    <td><i class="bi bi-check-circle text-success"></i></td>
                                    <td>Sent Itinerary: <b><?php echo $a['custom_title']; ?></b></td>
                                    <td><?php echo $a['name']; ?></td>
                                    <td><?php echo date('M d, h:i A', strtotime($a['sent_at'])); ?></td>
                                </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        <?php 
            endif; 
            if($_SESSION['role'] == 'employee'): 

            $my_id = $_SESSION['user_id'];
            // 1. Get the LATEST session
            $today_att = $conn->query("SELECT * FROM attendance WHERE user_id=$my_id AND date = CURDATE() ORDER BY id DESC LIMIT 1")->fetch_assoc();
            $att_id = $today_att['id'] ?? 0;
            
            // 2. Get Breaks & Determine Status
            $breaks = $conn->query("SELECT * FROM breaks WHERE attendance_id = $att_id ORDER BY start_time DESC");
            
            $is_on_break = false;
            $total_break_seconds = 0;
            $server_now = time(); // PHP Server Time

            // Calculate total break time (including ongoing ones)
            // We need to loop through this once for calculation, then reset for display
            $break_data = []; 
            while($b = $breaks->fetch_assoc()) {
                $break_data[] = $b; // Store for display loop later
                
                if($b['end_time'] == NULL) {
                    $is_on_break = true; // Found an active break
                    // Add time from Start until NOW
                    $total_break_seconds += ($server_now - strtotime($b['start_time']));
                } else {
                    // Add finished break time
                    $total_break_seconds += (strtotime($b['end_time']) - strtotime($b['start_time']));
                }
            }

            // Pass Login Time to JavaScript
            $login_time_js = $today_att ? strtotime($today_att['login_time']) * 1000 : 0;
        ?>

        <div class="row">
            <div class="col-md-4">
                <div class="card mb-4 <?php echo $is_on_break ? 'card-warning' : 'card-success'; ?> card-outline">
                    <div class="card-header"><h5 class="card-title">Attendance Action</h5></div>
                    <div class="card-body text-center">
                        <p class="fs-4 mb-1">Status: <strong><?php echo $is_on_break ? '☕ On Break' : '✅ Working'; ?></strong></p>
                        
                        <div class="bg-dark text-white rounded p-2 mb-3 shadow-sm">
                            <small>SESSION DURATION</small>
                            <h2 id="liveTimer" class="fw-bold m-0">00:00:00</h2>
                        </div>
                        
                        <p class="small text-muted">Login Time: <?php echo $today_att ? date('h:i A', strtotime($today_att['login_time'])) : 'Not Logged In'; ?></p>
                        
                        <?php if($is_on_break): ?>
                            <form action="actions/time_track.php" method="POST">
                                <input type="hidden" name="action" value="end_break">
                                <button class="btn btn-warning btn-lg w-100 fw-bold">Resume Work</button>
                            </form>
                        <?php else: ?>
                            <button class="btn btn-outline-danger btn-lg w-100" data-bs-toggle="modal" data-bs-target="#breakModal">
                                Take a Break
                            </button>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="card">
                    <div class="card-header bg-secondary text-white">Today's Breaks</div>
                    <ul class="list-group list-group-flush">
                        <?php 
                        if(!empty($break_data)): 
                            foreach($break_data as $b): 
                                $end = $b['end_time'] ? date('h:i A', strtotime($b['end_time'])) : 'Active';
                        ?>
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                <div>
                                    <strong><?php echo $b['reason']; ?></strong><br>
                                    <small class="text-muted"><?php echo date('h:i A', strtotime($b['start_time'])); ?> - <?php echo $end; ?></small>
                                </div>
                                <?php if(!$b['end_time']): ?><span class="badge bg-warning">Ongoing</span><?php endif; ?>
                            </li>
                        <?php endforeach; else: ?>
                            <li class="list-group-item text-muted">No breaks taken today.</li>
                        <?php endif; ?>
                    </ul>
                </div>
            </div>

            <div class="col-md-8">
                <div class="card">
                    <div class="card-header border-0"><h3 class="card-title">My Work History (Last 7 Days)</h3></div>
                    <div class="card-body table-responsive p-0">
                        <table class="table table-striped table-valign-middle">
                            <thead>
                            <tr>
                                <th>Date</th>
                                <th>Login</th>
                                <th>Logout</th>
                                <th>Total Work</th>
                            </tr>
                            </thead>
                            <tbody>
                            <?php 
                                $hist_sql = "SELECT * FROM attendance WHERE user_id=$my_id ORDER BY id DESC LIMIT 7";
                                $hist = $conn->query($hist_sql);
                                while($h = $hist->fetch_assoc()):
                            ?>
                            <tr>
                                <td><?php echo date('M d', strtotime($h['date'])); ?></td>
                                <td><?php echo date('h:i A', strtotime($h['login_time'])); ?></td>
                                <td><?php echo $h['logout_time'] ? date('h:i A', strtotime($h['logout_time'])) : '<span class="badge bg-success">Active</span>'; ?></td>
                                <td class="fw-bold text-primary"><?php echo $h['total_hours']; ?></td>
                            </tr>
                            <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <div class="modal fade" id="breakModal" tabindex="-1">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Take a Break</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <form action="actions/time_track.php" method="POST">
                        <div class="modal-body">
                            <input type="hidden" name="action" value="start_break">
                            <div class="mb-3">
                                <label class="form-label">Select Reason</label>
                                <select name="reason" class="form-select select2-dynamic" style="width:100%">
                                    <option value="Lunch Break">Lunch Break</option>
                                    <option value="Tea/Coffee Break">Tea/Coffee Break</option>
                                    <option value="Personal Emergency">Personal Emergency</option>
                                    <option value="Meeting">Meeting</option>
                                </select>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                            <button type="submit" class="btn btn-warning">Start Break</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Server passing calculated break seconds (including ongoing ones)
            const loginTime = <?php echo $login_time_js; ?>;
            const initialBreakSeconds = <?php echo $total_break_seconds; ?>;
            const isOnBreak = <?php echo $is_on_break ? 'true' : 'false'; ?>;
            
            // We need to track how much break time accumulates while sitting on this page
            let pageLoadTime = Math.floor(new Date().getTime() / 1000);

            function updateTimer() {
                if (loginTime === 0) return;

                const now = new Date().getTime();
                const nowSec = Math.floor(now / 1000);
                
                // Calculate total Seconds since login
                let totalElapsed = nowSec - Math.floor(loginTime / 1000);
                
                // If on break, the "Break Deduction" grows every second
                let currentBreakDeduction = initialBreakSeconds;
                if (isOnBreak) {
                    // Add seconds passed since page loaded
                    currentBreakDeduction += (nowSec - pageLoadTime);
                }

                // WORKED TIME = Total Elapsed - Total Breaks
                let diffInSeconds = totalElapsed - currentBreakDeduction;

                if (diffInSeconds < 0) diffInSeconds = 0;

                const hours = Math.floor(diffInSeconds / 3600);
                const minutes = Math.floor((diffInSeconds % 3600) / 60);
                const seconds = diffInSeconds % 60;

                const formatted = 
                    (hours < 10 ? "0" + hours : hours) + ":" + 
                    (minutes < 10 ? "0" + minutes : minutes) + ":" + 
                    (seconds < 10 ? "0" + seconds : seconds);

                document.getElementById("liveTimer").innerText = formatted;
            }

            setInterval(updateTimer, 1000);
            updateTimer();
        });
        </script>

        <?php endif; ?>

    </div>
</div>

<?php include 'includes/footer.php'; ?>