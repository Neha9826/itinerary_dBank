<?php 
include 'includes/header.php'; 
include 'config/db.php';

if($_SESSION['role'] != 'admin') {
    echo "<script>window.location.href='dashboard.php';</script>"; 
    exit;
}

$filter_date = isset($_GET['date']) ? $_GET['date'] : date('Y-m-d');
?>

<div class="app-content-header">
    <div class="container-fluid">
        <div class="row">
            <div class="col-sm-6"><h3>Daily Attendance Report</h3></div>
            <div class="col-sm-6 text-end">
                <form method="GET" class="d-flex justify-content-end align-items-center">
                    <label class="me-2 fw-bold">Date:</label>
                    <input type="date" name="date" class="form-control form-control-sm w-auto me-2" value="<?php echo $filter_date; ?>">
                    <button type="submit" class="btn btn-primary btn-sm"><i class="bi bi-filter"></i> Filter</button>
                </form>
            </div>
        </div>
    </div>
</div>

<div class="app-content">
    <div class="container-fluid">
        <div class="card card-outline card-info">
            <div class="card-header">
                <h3 class="card-title">Consolidated Report: <?php echo date('F d, Y', strtotime($filter_date)); ?></h3>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-striped table-hover align-middle">
                        <thead class="table-dark">
                            <tr>
                                <th>Employee Name</th>
                                <th>First Login</th>
                                <th>Last Logout</th>
                                <th>Total Work</th>
                                <th>Breaks (Count)</th>
                                <th>Total Break Time</th>
                                <th>Current Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            // 1. Get Distinct Employees present on this date
                            $emp_sql = "SELECT DISTINCT a.user_id, u.name 
                                        FROM attendance a 
                                        JOIN users u ON a.user_id = u.id 
                                        WHERE a.date = '$filter_date' AND u.role = 'employee'";
                            $employees = $conn->query($emp_sql);

                            if($employees->num_rows > 0):
                                while($emp = $employees->fetch_assoc()):
                                    $uid = $emp['user_id'];

                                    // 2. Fetch ALL sessions for this user on this date
                                    $sessions_sql = "SELECT * FROM attendance WHERE user_id = $uid AND date = '$filter_date' ORDER BY id ASC";
                                    $sessions = $conn->query($sessions_sql);

                                    // --- AGGREGATION VARIABLES ---
                                    $first_login = null;
                                    $last_logout = null;
                                    $is_still_working = false;
                                    
                                    $total_work_seconds = 0;
                                    $total_break_seconds = 0;
                                    $total_break_count = 0;
                                    
                                    $current_status = "Logged Out"; // Default
                                    $status_color = "secondary";

                                    // --- LOOP THROUGH SESSIONS ---
                                    while($sess = $sessions->fetch_assoc()) {
                                        $att_id = $sess['id'];
                                        
                                        // Capture First Login
                                        if($first_login == null) $first_login = $sess['login_time'];

                                        // Check if this is the "Active" session (Logout is NULL)
                                        if($sess['logout_time'] == null) {
                                            $is_still_working = true;
                                            $last_logout = null; // Still working
                                            
                                            // Calculate LIVE work duration for this session
                                            // (Current Time - Login) - Breaks taken so far
                                            $live_start = strtotime($sess['login_time']);
                                            $live_now = time();
                                            $live_duration = $live_now - $live_start;
                                            
                                            // Check breaks for this specific session
                                            $b_live_sql = "SELECT start_time, end_time FROM breaks WHERE attendance_id = $att_id";
                                            $b_live_res = $conn->query($b_live_sql);
                                            $live_break_deduction = 0;
                                            while($bl = $b_live_res->fetch_assoc()) {
                                                $bs = strtotime($bl['start_time']);
                                                $be = $bl['end_time'] ? strtotime($bl['end_time']) : time();
                                                $live_break_deduction += ($be - $bs);
                                            }
                                            
                                            $total_work_seconds += ($live_duration - $live_break_deduction);

                                        } else {
                                            // Closed Session: Use the stored 'total_hours' string
                                            // Convert "HH:MM:SS" back to seconds
                                            $last_logout = $sess['logout_time'];
                                            $parts = explode(':', $sess['total_hours']);
                                            if(count($parts) == 3) {
                                                $total_work_seconds += ($parts[0]*3600) + ($parts[1]*60) + $parts[2];
                                            }
                                        }

                                        // --- AGGREGATE BREAKS FOR THIS SESSION ---
                                        $break_sql = "SELECT start_time, end_time FROM breaks WHERE attendance_id = $att_id";
                                        $breaks = $conn->query($break_sql);
                                        $total_break_count += $breaks->num_rows;
                                        
                                        while($b = $breaks->fetch_assoc()) {
                                            $start = strtotime($b['start_time']);
                                            $end = $b['end_time'] ? strtotime($b['end_time']) : time();
                                            $total_break_seconds += ($end - $start);
                                            
                                            // Check if currently on break (Only relevant if session is active)
                                            if($is_still_working && $b['end_time'] == null) {
                                                $current_status = "On Break";
                                                $status_color = "warning";
                                            }
                                        }
                                    }

                                    // --- FINALIZE STATUS ---
                                    if($is_still_working && $current_status != "On Break") {
                                        $current_status = "Working";
                                        $status_color = "success";
                                    }

                                    // --- FORMATTING HELPERS ---
                                    function fmt($seconds) {
                                        $h = floor($seconds / 3600);
                                        $m = floor(($seconds % 3600) / 60);
                                        return sprintf('%02dh %02dm', $h, $m);
                                    }
                            ?>
                            <tr>
                                <td class="fw-bold"><?php echo $emp['name']; ?></td>
                                
                                <td><?php echo date('h:i A', strtotime($first_login)); ?></td>
                                
                                <td>
                                    <?php if($is_still_working): ?>
                                        <span class="badge bg-light text-dark border">-- : --</span>
                                    <?php else: ?>
                                        <?php echo date('h:i A', strtotime($last_logout)); ?>
                                    <?php endif; ?>
                                </td>
                                
                                <td class="text-primary fw-bold"><?php echo fmt($total_work_seconds); ?></td>
                                
                                <td><?php echo $total_break_count; ?></td>
                                
                                <td class="text-danger"><?php echo fmt($total_break_seconds); ?></td>
                                
                                <td>
                                    <span class="badge bg-<?php echo $status_color; ?>"><?php echo $current_status; ?></span>
                                </td>
                                
                                <td>
                                    <a href="emp_attendance_history.php?id=<?php echo $uid; ?>" class="btn btn-sm btn-outline-dark">
                                        <i class="bi bi-eye"></i> Details
                                    </a>
                                </td>
                            </tr>
                            <?php endwhile; else: ?>
                            <tr>
                                <td colspan="8" class="text-center py-5 text-muted">
                                    <i class="bi bi-calendar-x fs-1"></i><br>
                                    No attendance records found for this date.
                                </td>
                            </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>