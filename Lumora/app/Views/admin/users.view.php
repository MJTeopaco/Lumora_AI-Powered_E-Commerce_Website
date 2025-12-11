<?php
// app/Views/admin/users.view.php
?>

<input type="hidden" id="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">

<div class="content-card">
    <h2>
        <span><i class="fas fa-users"></i> Registered Users</span>
        <span class="badge badge-primary"><?= count($users) ?></span>
    </h2>

    <?php if (empty($users)): ?>
        <div class="empty-state">
            <i class="fas fa-user-slash"></i>
            <p>No users found in the system.</p>
        </div>
    <?php else: ?>
        <div class="table-responsive">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>User Info</th>
                        <th>Role(s)</th>
                        <th>Status</th>
                        <th>Joined Date</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($users as $user): ?>
                    <?php 
                        // Status Logic
                        $isLocked = false;
                        $lockoutDateDisplay = '';
                        if (!empty($user['lockout_until'])) {
                            $lockoutTime = new DateTime($user['lockout_until']);
                            $now = new DateTime();
                            if ($lockoutTime > $now) {
                                $isLocked = true;
                                $lockoutDateDisplay = ($lockoutTime->format('Y') == '9999') ?  : $lockoutTime->format('M d, H:i');
                            }
                        }
                        $failedAttempts = (int)($user['failed_login_attempts'] ?? 0);
                    ?>
                    <tr>
                        <td>
                            <div class="user-info-cell">
                                <strong><?= htmlspecialchars($user['username']) ?></strong>
                                <small><?= htmlspecialchars($user['email']) ?></small>
                            </div>
                        </td>
                        <td>
                            <div class="roles-badges">
                                <?php 
                                    $roles = explode(',', $user['roles'] ?? '');
                                    foreach ($roles as $role): 
                                        $role = trim($role);
                                        if (empty($role)) continue;
                                        $badgeClass = match($role) {
                                            'admin' => 'badge-danger',
                                            'seller' => 'badge-warning',
                                            default => 'badge-info'
                                        };
                                ?>
                                    <span class="badge <?= $badgeClass ?>"><?= ucfirst($role) ?></span>
                                <?php endforeach; ?>
                            </div>
                        </td>
                        <td>
                            <div style="display: flex; flex-direction: column; gap: 5px;">
                                <?php if ($isLocked): ?>
                                    <span class="badge badge-danger">
                                        <i class="fas fa-lock"></i> Locked 
                                    </span>
                                <?php else: ?>
                                    <span class="badge badge-success">
                                        <i class="fas fa-check"></i> Active
                                    </span>
                                <?php endif; ?>

                                <?php if ($failedAttempts > 0): ?>
                                    <small class="text-warning">
                                        <i class="fas fa-exclamation-triangle"></i> <?= $failedAttempts ?> failed attempt(s)
                                    </small>
                                <?php endif; ?>
                            </div>
                        </td>
                        <td>
                            <span class="date-badge">
                                <?= date('M d, Y', strtotime($user['created_at'])) ?>
                            </span>
                        </td>
                        <td>
                            <div class="action-buttons">
                                <?php if ($isLocked || $failedAttempts > 0): ?>
                                    <button type="button" 
                                            class="btn btn-warning btn-sm open-unlock-modal" 
                                            data-userid="<?= $user['user_id'] ?>" 
                                            data-username="<?= htmlspecialchars($user['username']) ?>">
                                        <i class="fas fa-unlock"></i> Unlock
                                    </button>
                                <?php endif; ?>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</div>

<div id="unlockModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h3><i class="fas fa-unlock-alt"></i> Unlock Account</h3>
            <button class="close-modal">&times;</button>
        </div>
        <div class="modal-body">
            <p>Are you sure you want to unlock the account for <strong id="modalUsername" style="color: #D4AF37;"></strong>?</p>
            
            <div style="background: #fffcf5; padding: 15px; border-radius: 8px; margin: 15px 0; border: 1px solid #faeacc;">
                <p style="margin:0; font-size: 0.9rem; color: #856404;">
                    <i class="fas fa-info-circle"></i> This action will:
                </p>
                <ul style="margin: 5px 0 0 20px; font-size: 0.9rem; color: #555;">
                    <li>Reset failed login attempts to 0</li>
                    <li>Remove any active lockout timers</li>
                    <li>Allow the user to log in immediately</li>
                </ul>
            </div>

            <form id="unlockForm" action="/admin/users/unlock" method="POST">
                <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
                <input type="hidden" name="user_id" id="modalUserId">
                
                <div style="display: flex; justify-content: flex-end; gap: 10px; margin-top: 20px;">
                    <button type="button" class="btn btn-secondary close-modal-btn">Cancel</button>
                    <button type="submit" class="btn btn-warning">Confirm Unlock</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Get Elements
        const modal = document.getElementById('unlockModal');
        const modalUserId = document.getElementById('modalUserId');
        const modalUsername = document.getElementById('modalUsername');
        
        // Open Modal Buttons
        const openBtns = document.querySelectorAll('.open-unlock-modal');
        openBtns.forEach(btn => {
            btn.addEventListener('click', () => {
                const userId = btn.getAttribute('data-userid');
                const username = btn.getAttribute('data-username');
                
                // Pass data to modal
                modalUserId.value = userId;
                modalUsername.textContent = username;
                
                // Show modal
                modal.classList.add('active');
            });
        });

        // Close Modal Logic (X button, Cancel button, Outside click)
        const closeBtns = document.querySelectorAll('.close-modal, .close-modal-btn');
        closeBtns.forEach(btn => {
            btn.addEventListener('click', (e) => {
                e.preventDefault();
                modal.classList.remove('active');
            });
        });

        window.addEventListener('click', (e) => {
            if (e.target === modal) {
                modal.classList.remove('active');
            }
        });
    });
</script>