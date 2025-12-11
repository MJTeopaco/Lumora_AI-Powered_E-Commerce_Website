<?php
// app/Views/admin/support.view.php
?>

<div class="content-card">
    <h2>
        <span><i class="fas fa-headset"></i> Support Requests</span>
    </h2>

    <?php if (empty($tickets)): ?>
        <div class="empty-state">
            <i class="fas fa-inbox"></i>
            <p>No support requests found.</p>
        </div>
    <?php else: ?>
        <div class="table-responsive">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>User Identifier</th>
                        <th>Message</th>
                        <th>Status</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($tickets as $ticket): ?>
                    <tr>
                        <td>
                            <span class="date-badge">
                                <?= date('M d, H:i', strtotime($ticket['created_at'])) ?>
                            </span>
                        </td>
                        <td>
                            <strong><?= htmlspecialchars($ticket['user_identifier']) ?></strong>
                            <div style="font-size: 12px; color: #777;"><?= htmlspecialchars($ticket['subject']) ?></div>
                        </td>
                        <td>
                            <p style="font-size: 14px; max-width: 400px; line-height: 1.4;">
                                <?= nl2br(htmlspecialchars($ticket['message'])) ?>
                            </p>
                        </td>
                        <td>
                            <?php if ($ticket['status'] === 'OPEN'): ?>
                                <span class="badge badge-warning">Open</span>
                            <?php else: ?>
                                <span class="badge badge-success">Resolved</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php if ($ticket['status'] === 'OPEN'): ?>
                                <form action="/admin/support/resolve" method="POST">
                                    <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
                                    <input type="hidden" name="ticket_id" value="<?= $ticket['ticket_id'] ?>">
                                    <button type="submit" class="btn btn-sm btn-success" title="Mark as Resolved">
                                        <i class="fas fa-check"></i> Resolve
                                    </button>
                                </form>
                            <?php else: ?>
                                <span class="text-muted"><i class="fas fa-check-circle"></i> Done</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</div>