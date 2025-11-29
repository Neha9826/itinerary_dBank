<?php 
include 'includes/header.php'; 
include 'config/db.php';

// --- 1. DATA FETCHING (ADMIN) ---
if($_SESSION['role'] == 'admin') {
    $emp_count = $conn->query("SELECT count(*) as total FROM users WHERE role='employee'")->fetch_assoc()['total'];
    $agent_count = $conn->query("SELECT count(*) as total FROM users WHERE role='agent'")->fetch_assoc()['total'];
    $master_count = $conn->query("SELECT count(*) as total FROM master_itineraries")->fetch_assoc()['total'];
    $sent_count = $conn->query("SELECT count(*) as total FROM sent_itineraries")->fetch_assoc()['total'];
}

// --- 2. DATA FETCHING (AGENT) ---
if($_SESSION['role'] == 'agent') {
    $my_id = $_SESSION['user_id'];
    $total_received = $conn->query("SELECT count(*) as total FROM sent_itineraries WHERE agent_id = $my_id")->fetch_assoc()['total'];
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
                    <div class="inner"><h3><?php echo $emp_count; ?></h3><p>Total Employees</p></div>
                    <div class="small-box-icon"><i class="bi bi-people-fill"></i></div>
                    <a href="manage_users.php?role=employee" class="small-box-footer link-light">Manage Emp <i class="bi bi-arrow-right-circle"></i></a>
                </div>
            </div>
            <div class="col-lg-3 col-6">
                <div class="small-box text-bg-warning">
                    <div class="inner"><h3><?php echo $agent_count; ?></h3><p>Total Agents</p></div>
                    <div class="small-box-icon"><i class="bi bi-briefcase-fill"></i></div>
                    <a href="manage_users.php?role=agent" class="small-box-footer link-dark">View Agents <i class="bi bi-arrow-right-circle"></i></a>
                </div>
            </div>
            <div class="col-lg-3 col-6">
                <div class="small-box text-bg-success">
                    <div class="inner"><h3><?php echo $master_count; ?></h3><p>Master Itineraries</p></div>
                    <div class="small-box-icon"><i class="bi bi-file-earmark-richtext"></i></div>
                    <a href="view_masters.php" class="small-box-footer link-light">View All <i class="bi bi-arrow-right-circle"></i></a>
                </div>
            </div>
            <div class="col-lg-3 col-6">
                <div class="small-box text-bg-danger">
                    <div class="inner"><h3><?php echo $sent_count; ?></h3><p>Itineraries Sent</p></div>
                    <div class="small-box-icon"><i class="bi bi-send-fill"></i></div>
                    <a href="all_sent_itineraries.php" class="small-box-footer link-light">More info <i class="bi bi-arrow-right-circle"></i></a>
                </div>
            </div>
        </div>
        
        <div class="row mt-3">
            <div class="col-12">
                <div class="card">
                    <div class="card-header"><h3 class="card-title">Recent System Activity</h3></div>
                    <div class="card-body p-0">
                        <table class="table table-striped">
                            <thead><tr><th>#</th><th>Activity</th><th>User</th><th>Time</th></tr></thead>
                            <tbody>
                                <?php
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
        <?php endif; ?>


        <?php if($_SESSION['role'] == 'agent'): ?>
        <div class="row">
            <div class="col-lg-3 col-6">
                <div class="small-box text-bg-warning">
                    <div class="inner">
                        <h3><?php echo $total_received; ?></h3>
                        <p>Itineraries Received</p>
                    </div>
                    <div class="small-box-icon"><i class="bi bi-folder2-open"></i></div>
                    <a href="my_itineraries.php" class="small-box-footer link-dark">
                        View All <i class="bi bi-arrow-right-circle"></i>
                    </a>
                </div>
            </div>
            </div>

        <div class="row mt-3">
            <div class="col-12">
                <div class="card card-outline card-primary">
                    <div class="card-header">
                        <h3 class="card-title">Recently Received</h3>
                        <div class="card-tools">
                            <a href="my_itineraries.php" class="btn btn-tool btn-sm">View Full Inbox</a>
                        </div>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover align-middle text-nowrap">
                                <thead class="table-light">
                                    <tr>
                                        <th>Title</th>
                                        <th>From</th>
                                        <th>Price</th>
                                        <th>Date</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    $my_id = $_SESSION['user_id'];
                                    $sql = "SELECT s.*, u.name as emp_name 
                                            FROM sent_itineraries s 
                                            JOIN users u ON s.employee_id = u.id 
                                            WHERE s.agent_id = $my_id 
                                            ORDER BY s.sent_at DESC LIMIT 5";
                                    $result = $conn->query($sql);

                                    if($result->num_rows > 0):
                                        while($row = $result->fetch_assoc()):
                                    ?>
                                    <tr>
                                        <td class="fw-bold text-primary"><?php echo $row['custom_title']; ?></td>
                                        <td><?php echo $row['emp_name']; ?></td>
                                        <td class="text-success fw-bold">Rs. <?php echo number_format($row['final_price']); ?>/-</td>
                                        <td><?php echo date('M d, h:i A', strtotime($row['sent_at'])); ?></td>
                                        <td>
                                            <a href="view_sent_itinerary.php?id=<?php echo $row['id']; ?>" class="btn btn-sm btn-outline-info" title="View"><i class="bi bi-eye"></i></a>
                                            <a href="download_custom.php?id=<?php echo $row['id']; ?>" class="btn btn-sm btn-outline-dark" title="Download"><i class="bi bi-download"></i></a>
                                        </td>
                                    </tr>
                                    <?php endwhile; else: ?>
                                    <tr><td colspan="5" class="text-center text-muted py-4">No recent itineraries.</td></tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <?php endif; ?>


        <?php if($_SESSION['role'] == 'employee'): ?>
            
        <?php
            $my_id = $_SESSION['user_id'];
            
            // 1. Timer Logic
            $latest_session = $conn->query("SELECT * FROM attendance WHERE user_id=$my_id ORDER BY id DESC LIMIT 1")->fetch_assoc();
            $is_active_session = ($latest_session && $latest_session['logout_time'] == NULL);
            $current_att_id = $latest_session['id'] ?? 0;
            
            $current_break_check = $conn->query("SELECT * FROM breaks WHERE attendance_id = $current_att_id AND end_time IS NULL LIMIT 1");
            $is_on_break = ($current_break_check->num_rows > 0);

            // Timer Calculation
            $session_breaks = $conn->query("SELECT start_time, end_time FROM breaks WHERE attendance_id = $current_att_id");
            $session_break_seconds = 0;
            $server_now = time();

            while($sb = $session_breaks->fetch_assoc()) {
                if($sb['end_time'] == NULL) {
                    $session_break_seconds += ($server_now - strtotime($sb['start_time']));
                } else {
                    $session_break_seconds += (strtotime($sb['end_time']) - strtotime($sb['start_time']));
                }
            }

            $login_time_js = ($is_active_session && $latest_session) ? strtotime($latest_session['login_time']) * 1000 : 0;
            $server_time_js = time() * 1000;

            // 2. Break List
            $today_break_sql = "SELECT b.* FROM breaks b 
                                JOIN attendance a ON b.attendance_id = a.id 
                                WHERE a.user_id = $my_id AND a.date = CURDATE() 
                                ORDER BY b.start_time DESC";
            $all_todays_breaks = $conn->query($today_break_sql);
        ?>

        <div class="row">
            <div class="col-md-4">
                <div class="card mb-4 <?php echo $is_on_break ? 'card-warning' : 'card-success'; ?> card-outline">
                    <div class="card-header"><h5 class="card-title">Attendance Action</h5></div>
                    <div class="card-body text-center">
                        <p class="fs-4 mb-1">Status: <strong><?php echo $is_on_break ? '☕ On Break' : ($is_active_session ? '✅ Working' : '🔴 Signed Out'); ?></strong></p>
                        
                        <div class="bg-dark text-white rounded p-2 mb-3 shadow-sm">
                            <small>SESSION DURATION</small>
                            <h2 id="liveTimer" class="fw-bold m-0">00:00:00</h2>
                        </div>
                        
                        <p class="small text-muted">Login Time: <?php echo ($latest_session && $is_active_session) ? date('h:i A', strtotime($latest_session['login_time'])) : '--:--'; ?></p>
                        
                        <?php if($is_active_session): ?>
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
                        <?php else: ?>
                            <div class="alert alert-secondary py-1">Session Closed</div>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="card">
                    <div class="card-header bg-secondary text-white">All Breaks Today</div>
                    <div class="card-body p-0" style="max-height: 300px; overflow-y: auto;">
                        <ul class="list-group list-group-flush">
                            <?php if($all_todays_breaks->num_rows > 0): while($b = $all_todays_breaks->fetch_assoc()): 
                                $end = $b['end_time'] ? date('h:i A', strtotime($b['end_time'])) : 'Active';
                            ?>
                                <li class="list-group-item d-flex justify-content-between align-items-center">
                                    <div>
                                        <strong><?php echo htmlspecialchars($b['reason']); ?></strong><br>
                                        <small class="text-muted"><?php echo date('h:i A', strtotime($b['start_time'])); ?> - <?php echo $end; ?></small>
                                    </div>
                                    <?php if(!$b['end_time']): ?>
                                        <span class="badge bg-warning">Ongoing</span>
                                    <?php else: ?>
                                        <span class="badge bg-success">Done</span>
                                    <?php endif; ?>
                                </li>
                            <?php endwhile; else: ?>
                                <li class="list-group-item text-muted text-center p-3">No breaks taken today.</li>
                            <?php endif; ?>
                        </ul>
                    </div>
                </div>
            </div>

            <div class="col-md-8">
                <div class="card">
                    <div class="card-header border-0"><h3 class="card-title">My Work History (Last 7 Days)</h3></div>
                    <div class="card-body table-responsive p-0">
                        <table class="table table-striped table-valign-middle">
                            <thead><tr><th>Date</th><th>Login</th><th>Logout</th><th>Total Work</th></tr></thead>
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
                                <select name="reason" class="form-select" style="width:100%" required>
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
            const loginTime = <?php echo $login_time_js; ?>;
            const initialBreakSeconds = <?php echo $session_break_seconds; ?>;
            const isOnBreak = <?php echo $is_on_break ? 'true' : 'false'; ?>;
            const serverTimeAtLoad = <?php echo $server_time_js; ?>;
            const clientTimeAtLoad = new Date().getTime();
            const timeOffset = serverTimeAtLoad - clientTimeAtLoad;
            let pageLoadTimeSec = Math.floor(serverTimeAtLoad / 1000);

            function updateTimer() {
                if (loginTime === 0) return;
                const clientNow = new Date().getTime();
                const serverNow = clientNow + timeOffset;
                const serverNowSec = Math.floor(serverNow / 1000);
                let totalElapsed = serverNowSec - Math.floor(loginTime / 1000);
                let currentBreakDeduction = initialBreakSeconds;
                if (isOnBreak) { currentBreakDeduction += (serverNowSec - pageLoadTimeSec); }
                let diffInSeconds = totalElapsed - currentBreakDeduction;
                if (diffInSeconds < 0) diffInSeconds = 0;
                const hours = Math.floor(diffInSeconds / 3600);
                const minutes = Math.floor((diffInSeconds % 3600) / 60);
                const seconds = diffInSeconds % 60;
                const formatted = (hours < 10 ? "0" + hours : hours) + ":" + (minutes < 10 ? "0" + minutes : minutes) + ":" + (seconds < 10 ? "0" + seconds : seconds);
                const timerEl = document.getElementById("liveTimer");
                if(timerEl) timerEl.innerText = formatted;
            }
            setInterval(updateTimer, 1000);
            updateTimer(); 
        });
        </script>

        <?php endif; ?>

    </div>
</div>

<?php include 'includes/footer.php'; ?>