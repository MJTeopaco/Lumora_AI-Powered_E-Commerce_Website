<?php if ($integrity['status'] === 'VALID'): ?>
    <div class="alert success">
        <i class="fas fa-check-circle"></i>
        <span><strong>System Integrity Verified:</strong> The audit chain is intact.</span>
    </div>
<?php else: ?>
    <div class="alert error">
        <i class="fas fa-exclamation-triangle"></i>
        <span><strong>Security Alert:</strong> Integrity compromised at Log ID #<?= $integrity['log_id'] ?>.</span>
    </div>
<?php endif; ?>

<div class="content-card">
    <h2>
        <span><i class="fas fa-list-ul"></i> Audit Trails</span>
        <a href="/admin/audit-logs" class="btn btn-sm btn-secondary" style="text-decoration: none;">
            <i class="fas fa-sync"></i> Refresh
        </a>
    </h2>

    <div style="margin-bottom: 20px; background: #f8f9fa; padding: 15px; border-radius: 8px;">
        <form method="GET" action="/admin/audit-logs" style="display: flex; gap: 15px; align-items: flex-end;">
            <div class="form-group" style="margin-bottom: 0; flex: 1;">
                <label>Filter by Action</label>
                <select name="action_type">
                    <option value="">All Actions</option>
                    <option value="LOGIN" <?= (isset($_GET['action_type']) && $_GET['action_type'] == 'LOGIN') ? 'selected' : '' ?>>Login</option>
                    <option value="LOGOUT" <?= (isset($_GET['action_type']) && $_GET['action_type'] == 'LOGOUT') ? 'selected' : '' ?>>Logout</option>
                    <option value="UPDATE_SETTINGS" <?= (isset($_GET['action_type']) && $_GET['action_type'] == 'UPDATE_SETTINGS') ? 'selected' : '' ?>>Settings Update</option>
                    <option value="APPROVE_SELLER" <?= (isset($_GET['action_type']) && $_GET['action_type'] == 'APPROVE_SELLER') ? 'selected' : '' ?>>Seller Approval</option>
                </select>
            </div>
            <button type="submit" class="btn btn-primary">Filter</button>
        </form>
    </div>

    <table class="data-table">
        <thead>
            <tr>
                <th>ID</th>
                <th>User</th>
                <th>Action</th>
                <th>Details</th>
                <th>IP Address</th>
                <th>Date</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($logs)): ?>
                <tr><td colspan="6" class="empty-state"><i class="fas fa-search"></i><br>No logs found.</td></tr>
            <?php else: ?>
                <?php foreach ($logs as $log): 
                    // Map actions to admin.css badge classes
                    $badgeClass = 'warning'; // Default (Yellow)
                    if ($log['action_type'] == 'LOGIN') $badgeClass = 'success'; // Gold/Greenish
                    if ($log['action_type'] == 'LOGOUT') $badgeClass = 'secondary'; // Gray
                    if (strpos($log['action_type'], 'DELETE') !== false) $badgeClass = 'danger'; // Red
                ?>
                <tr>
                    <td><code>#<?= $log['log_id'] ?></code></td>
                    <td>
                        <strong><?= htmlspecialchars($log['user_name'] ?? 'System') ?></strong>
                        <br><span style="font-size: 11px; color: #95a5a6;">ID: <?= $log['user_id'] ?></span>
                    </td>
                    <td><span class="badge <?= $badgeClass ?>"><?= htmlspecialchars($log['action_type']) ?></span></td>
                    <td>
                        <span style="font-family: monospace; font-size: 12px; color: #555;">
                            <?= htmlspecialchars(substr($log['details'], 0, 60)) . (strlen($log['details']) > 60 ? '...' : '') ?>
                        </span>
                    </td>
                    <td><?= htmlspecialchars($log['ip_address']) ?></td>
                    <td><?= date('M d, Y H:i', strtotime($log['created_at'])) ?></td>
                </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>

    <?php if ($totalPages > 1): ?>
    <div style="margin-top: 20px; display: flex; gap: 5px; justify-content: flex-end;">
        <?php for($i=1; $i <= $totalPages; $i++): ?>
            <a href="?page=<?= $i ?><?= isset($_GET['action_type']) ? '&action_type='.htmlspecialchars($_GET['action_type']) : '' ?>" 
               class="btn btn-sm <?= $i == $currentPage ? 'btn-primary' : 'btn-secondary' ?>">
                <?= $i ?>
            </a>
        <?php endfor; ?>
    </div>
    <?php endif; ?>
</div>