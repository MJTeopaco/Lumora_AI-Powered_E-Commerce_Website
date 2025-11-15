<?php
// Assuming session is already started and user is authenticated
// require_once 'config.php';
// require_once 'auth_check.php';
namespace App\Views\Layouts\Admin;

$locked_users = 0; // Sample static value
$recent_users = []; // Sample empty array

// Current admin info
$admin_name = $_SESSION['username'] ?? 'Admin';
$admin_role = $_SESSION['role'] ?? 'Administrator';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Lumora DB</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #f5f7fa;
            color: #333;
        }

        .container {
            display: flex;
            min-height: 100vh;
        }

        /* Sidebar */
        .sidebar {
            width: 260px;
            background: #1e4d3d;
            color: #ecf0f1;
            padding: 20px 0;
            position: fixed;
            height: 100vh;
            overflow-y: auto;
        }

        .sidebar-header {
            padding: 0 20px 20px;
            border-bottom: 1px solid #34495e;
        }

        .sidebar-header h2 {
            color: #ffffffff;
            font-size: 24px;
            margin-bottom: 5px;
        }

        .sidebar-header p {
            font-size: 12px;
            color: #95a5a6;
        }

        .sidebar-menu {
            list-style: none;
            padding: 20px 0;
        }

        .sidebar-menu li {
            margin: 5px 0;
        }

        .sidebar-menu a {
            display: flex;
            align-items: center;
            padding: 12px 20px;
            color: #ecf0f1;
            text-decoration: none;
            transition: all 0.3s;
        }

        .sidebar-menu a:hover,
        .sidebar-menu a.active {
            background: #34495e;
            border-left: 4px solid #3498db;
            padding-left: 16px;
        }

        .sidebar-menu i {
            width: 25px;
            margin-right: 10px;
        }

        /* Main Content */
        .main-content {
            flex: 1;
            margin-left: 260px;
            padding: 20px;
        }

        /* Header */
        .header {
            background: white;
            padding: 20px 30px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
        }

        .header h1 {
            font-size: 28px;
            color: #2c3e50;
        }

        .user-info {
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .user-avatar {
            width: 45px;
            height: 45px;
            border-radius: 50%;
            background: #3498db;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: bold;
            font-size: 18px;
        }

        .user-details h3 {
            font-size: 14px;
            color: #2c3e50;
        }

        .user-details p {
            font-size: 12px;
            color: #7f8c8d;
        }

        /* Stats Cards */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }

        .stat-card {
            background: white;
            padding: 25px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            display: flex;
            justify-content: space-between;
            align-items: center;
            transition: transform 0.3s;
        }

        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 4px 20px rgba(0,0,0,0.1);
        }

        .stat-info h3 {
            font-size: 14px;
            color: #7f8c8d;
            margin-bottom: 8px;
            text-transform: uppercase;
            font-weight: 600;
        }

        .stat-info p {
            font-size: 32px;
            font-weight: bold;
            color: #2c3e50;
        }

        .stat-icon {
            width: 60px;
            height: 60px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 28px;
        }

        .stat-card.blue .stat-icon {
            background: #e3f2fd;
            color: #2196f3;
        }

        .stat-card.green .stat-icon {
            background: #e8f5e9;
            color: #4caf50;
        }

        .stat-card.orange .stat-icon {
            background: #fff3e0;
            color: #ff9800;
        }

        .stat-card.red .stat-icon {
            background: #ffebee;
            color: #f44336;
        }

        /* Content Sections */
        .content-grid {
            display: grid;
            grid-template-columns: 2fr 1fr;
            gap: 20px;
        }

        .content-card {
            background: white;
            padding: 25px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        }

        .content-card h2 {
            font-size: 20px;
            color: #2c3e50;
            margin-bottom: 20px;
            padding-bottom: 15px;
            border-bottom: 2px solid #ecf0f1;
        }

        /* Table */
        .data-table {
            width: 100%;
            border-collapse: collapse;
        }

        .data-table thead {
            background: #f8f9fa;
        }

        .data-table th {
            text-align: left;
            padding: 12px;
            font-weight: 600;
            color: #2c3e50;
            font-size: 14px;
        }

        .data-table td {
            padding: 12px;
            border-bottom: 1px solid #ecf0f1;
            font-size: 14px;
        }

        .data-table tr:hover {
            background: #f8f9fa;
        }

        .badge {
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 500;
        }

        .badge.success {
            background: #d4edda;
            color: #155724;
        }

        .badge.danger {
            background: #f8d7da;
            color: #721c24;
        }

        .badge.warning {
            background: #fff3cd;
            color: #856404;
        }

        /* Quick Actions */
        .quick-actions {
            display: flex;
            flex-direction: column;
            gap: 15px;
        }

        .action-btn {
            display: flex;
            align-items: center;
            padding: 15px;
            border: 2px solid #ecf0f1;
            border-radius: 8px;
            text-decoration: none;
            color: #2c3e50;
            transition: all 0.3s;
            background: white;
            cursor: pointer;
        }

        .action-btn:hover {
            border-color: #3498db;
            background: #f8f9fa;
            transform: translateX(5px);
        }

        .action-btn i {
            margin-right: 12px;
            font-size: 20px;
            color: #3498db;
        }

        .action-btn-text h4 {
            font-size: 14px;
            margin-bottom: 3px;
        }

        .action-btn-text p {
            font-size: 12px;
            color: #7f8c8d;
        }

        .empty-state {
            text-align: center;
            padding: 40px 20px;
            color: #7f8c8d;
        }

        .empty-state i {
            font-size: 48px;
            margin-bottom: 15px;
            color: #bdc3c7;
        }

        @media (max-width: 1024px) {
            .content-grid {
                grid-template-columns: 1fr;
            }
        }

        @media (max-width: 768px) {
            .sidebar {
                width: 70px;
            }

            .sidebar-menu span {
                display: none;
            }

            .sidebar-header h2,
            .sidebar-header p {
                display: none;
            }

            .main-content {
                margin-left: 70px;
            }

            .stats-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- Sidebar -->
        <aside class="sidebar">
            <div class="sidebar-header">
                <h2><i class="fas fa-database"></i> Lumora</h2>
                <p>Admin Panel</p>
            </div>
            <ul class="sidebar-menu">
                <li><a href="admin/dashboard" class="active"><i class="fas fa-home"></i><span>Dashboard</span></a></li>
                <li><a href="admin/users"><i class="fas fa-users"></i><span>Users</span></a></li>
                <li><a href="admin/sellers"><i class="fas fa-user-tag"></i><span>Sellers</span></a></li>
                <li><a href="admin/settings"><i class="fas fa-cog"></i><span>Configure Settings</span></a></li>
                <li><a href="admin/reports"><i class="fas fa-chart-bar"></i><span>Reports</span></a></li>
                <li><a href="/logout"><i class="fas fa-sign-out-alt"></i><span>Logout</span></a></li>
            </ul>
        </aside>

        <!-- Main Content -->
        <main class="main-content">
            <!-- Header -->
            <div class="header">
                <h1>Dashboard Overview</h1>
                <div class="user-info">
                    <div class="user-avatar"><?php echo strtoupper(substr($admin_name, 0, 1)); ?></div>
                    <div class="user-details">
                        <h3><?php echo htmlspecialchars($admin_name); ?></h3>
                        <p><?php echo htmlspecialchars($admin_role); ?></p>
                    </div>
                </div>
            </div>

            <!-- Stats Cards -->
            <div class="stats-grid">
                <div class="stat-card blue">
                    <div class="stat-info">
                        <h3>Total Users</h3>
                        <p><?= htmlspecialchars($total_users) ?></p>
                    </div>
                    <div class="stat-icon">
                        <i class="fas fa-users"></i>
                    </div>
                </div>

                <div class="stat-card green">
                    <div class="stat-info">
                        <h3>Total Buyers</h3>
                        <p><?= htmlspecialchars($total_buyers) ?></p>
                    </div>
                    <div class="stat-icon">
                        <i class="fas fa-user-check"></i>
                    </div>
                </div>

                <div class="stat-card orange">
                    <div class="stat-info">
                        <h3>Total Sellers</h3>
                        <p><?= htmlspecialchars($total_sellers) ?></p>
                    </div>
                    <div class="stat-icon">
                        <i class="fas fa-user-tag"></i>
                    </div>
                </div>

                <div class="stat-card red">
                    <div class="stat-info">
                        <h3>Total Admins</h3>
                        <p><?= htmlspecialchars($total_admins) ?></p>
                    </div>
                    <div class="stat-icon">
                        <i class="fas fa-user-lock"></i>
                    </div>
                </div>
            </div>

            <!-- Content Grid -->
            <div class="content-grid">
                <!-- Recent Users Table -->
                <div class="content-card">
                    <h2><i class="fas fa-clock"></i> Recent Users</h2>
                    <?php if (empty($recent_users)): ?>
                        <div class="empty-state">
                            <i class="fas fa-users"></i>
                            <p>No users found. Add your first user to get started!</p>
                        </div>
                    <?php else: ?>
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th>Username</th>
                                    <th>Email</th>
                                    <th>Status</th>
                                    <th>Created</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($recent_users as $user): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($user['username']); ?></td>
                                    <td><?php echo htmlspecialchars($user['email']); ?></td>
                                    <td>
                                        <?php if ($user['lockout_until']): ?>
                                            <span class="badge danger">Locked</span>
                                        <?php else: ?>
                                            <span class="badge success">Active</span>
                                        <?php endif; ?>
                                    </td>
                                    <td><?php echo date('M d, Y', strtotime($user['created_at'])); ?></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    <?php endif; ?>
                </div>

                <!-- Quick Actions -->
                <div class="content-card">
                    <h2><i class="fas fa-bolt"></i> Quick Actions</h2>
                    <div class="quick-actions">

                        <a href="admin/seller" class="action-btn">
                            <i class="fas fa-user-plus"></i>
                            <div class="action-btn-text">
                                <h4>Approve New Seller</h4>
                                <p>Approve user account that applied</p>
                            </div>
                        </a>
                        
                        <a href="admin/settings" class="action-btn">
                            <i class="fas fa-plus-circle"></i>
                            <div class="action-btn-text">
                                <h4>Add New Category</h4>
                                <p>Add or edit categories of website</p>
                            </div>
                        </a>
                        
                        <a href="admin/users" class="action-btn">
                            <i class="fas fa-link"></i>
                            <div class="action-btn-text">
                                <h4>Locked Users</h4>
                                <p>Locked or unlocked user accounts</p>
                            </div>
                        </a>
                        
                        <a href="reports.php" class="action-btn">
                            <i class="fas fa-file-export"></i>
                            <div class="action-btn-text">
                                <h4>Generate Report</h4>
                                <p>Export system reports</p>
                            </div>
                        </a>
                        
                        <a href="settings.php" class="action-btn">
                            <i class="fas fa-tools"></i>
                            <div class="action-btn-text">
                                <h4>System Settings</h4>
                                <p>Configure application</p>
                            </div>
                        </a>
                    </div>
                </div>
            </div>
        </main>
    </div>
</body>
</html>