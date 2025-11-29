<?php
if (session_status() === PHP_SESSION_NONE) { session_start(); }
// Simple check to ensure user is logged in
if (!isset($_SESSION['user_id']) && basename($_SERVER['PHP_SELF']) != 'login.php' && basename($_SERVER['PHP_SELF']) != 'register.php') {
    header("Location: login.php");
    exit();
}
?>
<!doctype html>
<html lang="en">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <title>Itinerary Data Bank</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@fontsource/source-sans-3@5.0.12/index.css" />
    
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/overlayscrollbars@2.11.0/styles/overlayscrollbars.min.css" />
    
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css" />

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/summernote@0.8.18/dist/summernote-lite.min.css">

    <link rel="stylesheet" href="./css/adminlte.css" />
</head>
<body class="layout-fixed sidebar-expand-lg sidebar-open bg-body-tertiary">
    <div class="app-wrapper">
        <nav class="app-header navbar navbar-expand bg-body">
            <div class="container-fluid">
                <ul class="navbar-nav">
                    <li class="nav-item">
                        <a class="nav-link" data-lte-toggle="sidebar" href="#" role="button"><i class="bi bi-list"></i></a>
                    </li>
                </ul>
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item dropdown user-menu">
                        <a href="#" class="nav-link dropdown-toggle" data-bs-toggle="dropdown">
                            <span class="d-none d-md-inline">Welcome, <?php echo $_SESSION['name'] ?? 'User'; ?></span>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-lg dropdown-menu-end">
                            <li class="user-header text-bg-primary">
                                <p>
                                    <?php echo $_SESSION['name']; ?>
                                    <small><?php echo ucfirst($_SESSION['role']); ?></small>
                                </p>
                            </li>
                            <li class="user-footer">
                                <a href="profile.php" class="btn btn-default btn-flat">Profile</a>
                                <a href="actions/logout.php" class="btn btn-default btn-flat float-end">Sign out</a>
                            </li>
                        </ul>
                    </li>
                </ul>
            </div>
        </nav>
        
        <aside class="app-sidebar bg-body-secondary shadow" data-bs-theme="dark">
            <div class="sidebar-brand">
                <a href="dashboard.php" class="brand-link">
                    <span class="brand-text fw-light">ItinerarySys</span>
                </a>
            </div>
            <div class="sidebar-wrapper">
                <nav class="mt-2">
                    <ul class="nav sidebar-menu flex-column" data-lte-toggle="treeview" role="navigation">
                        <li class="nav-item">
                            <a href="dashboard.php" class="nav-link">
                                <i class="nav-icon bi bi-speedometer"></i> <p>Dashboard</p>
                            </a>
                        </li>
                        
                        <?php if(isset($_SESSION['role']) && $_SESSION['role'] == 'admin'): ?>
                        <li class="nav-item">
                            <a href="create_itinerary.php" class="nav-link">
                                <i class="nav-icon bi bi-plus-circle"></i> <p>Create Itinerary</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="attendance_report.php" class="nav-link">
                                <i class="nav-icon bi bi-calendar-check"></i> <p>Attendance Reports</p>
                            </a>
                        </li>
                        <?php endif; ?>

                        <?php if(isset($_SESSION['role']) && $_SESSION['role'] == 'employee'): ?>
                            <li class="nav-item">
                                <a href="view_masters.php" class="nav-link">
                                    <i class="nav-icon bi bi-files"></i> <p>Browse Itineraries</p>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a href="sent_history.php" class="nav-link">
                                    <i class="nav-icon bi bi-clock-history"></i> <p>Sent History</p>
                                </a>
                            </li>
                        <?php endif; ?>
                        
                         <?php if(isset($_SESSION['role']) && $_SESSION['role'] == 'agent'): ?>
                        <li class="nav-item">
                            <a href="my_itineraries.php" class="nav-link">
                                <i class="nav-icon bi bi-folder"></i> <p>My Itineraries</p>
                            </a>
                        </li>
                        <?php endif; ?>
                    </ul>
                </nav>
            </div>
        </aside>

        <main class="app-main">