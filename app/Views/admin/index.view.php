<?php
// app/Views/admin/index.view.php
?>
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
                        <td><?= htmlspecialchars($user['username']) ?></td>
                        <td><?= htmlspecialchars($user['email']) ?></td>
                        <td>
                            <?php if ($user['lockout_until']): ?>
                                <span class="badge danger">Locked</span>
                            <?php else: ?>
                                <span class="badge success">Active</span>
                            <?php endif; ?>
                        </td>
                        <td><?= date('M d, Y', strtotime($user['created_at'])) ?></td>
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
            <a href="/admin/sellers" class="action-btn">
                <i class="fas fa-user-plus"></i>
                <div class="action-btn-text">
                    <h4>Approve New Seller</h4>
                    <p>Approve user account that applied</p>
                </div>
            </a>
            
            <a href="/admin/settings" class="action-btn">
                <i class="fas fa-plus-circle"></i>
                <div class="action-btn-text">
                    <h4>Add New Category</h4>
                    <p>Add or edit categories of website</p>
                </div>
            </a>
            
            <a href="/admin/users" class="action-btn">
                <i class="fas fa-link"></i>
                <div class="action-btn-text">
                    <h4>Locked Users</h4>
                    <p>Locked or unlocked user accounts</p>
                </div>
            </a>
            
            <a href="/admin/reports" class="action-btn">
                <i class="fas fa-file-export"></i>
                <div class="action-btn-text">
                    <h4>Generate Report</h4>
                    <p>Export system reports</p>
                </div>
            </a>
            
            <a href="/admin/settings" class="action-btn">
                <i class="fas fa-tools"></i>
                <div class="action-btn-text">
                    <h4>System Settings</h4>
                    <p>Configure application</p>
                </div>
            </a>
        </div>
    </div>
</div>