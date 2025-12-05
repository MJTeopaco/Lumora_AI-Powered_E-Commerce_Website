<div class="dashboard-container">
    <div class="dashboard-header">
        <div>
            <h1 class="dashboard-title">
                <i class="fas fa-star"></i> Product Reviews
            </h1>
            <p class="dashboard-subtitle">Manage and respond to customer reviews</p>
        </div>
    </div>

    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-icon bg-yellow">
                <i class="fas fa-star"></i>
            </div>
            <div class="stat-content">
                <div class="stat-value"><?= number_format($stats['average_rating'] ?? 0, 1) ?></div>
                <div class="stat-label">Average Rating</div>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-icon bg-green">
                <i class="fas fa-comments"></i>
            </div>
            <div class="stat-content">
                <div class="stat-value"><?= number_format($stats['total_reviews'] ?? 0) ?></div>
                <div class="stat-label">Total Reviews</div>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-icon bg-blue">
                <i class="fas fa-reply"></i>
            </div>
            <div class="stat-content">
                <div class="stat-value"><?= number_format($stats['responded'] ?? 0) ?></div>
                <div class="stat-label">Responded</div>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-icon bg-red">
                <i class="fas fa-clock"></i>
            </div>
            <div class="stat-content">
                <div class="stat-value"><?= number_format($stats['pending_response'] ?? 0) ?></div>
                <div class="stat-label">Needs Response</div>
            </div>
        </div>
    </div>

    <div class="filters-section">
        <div class="review-controls" style="width: 100%;">
            <div class="review-filter-buttons">
                <button class="btn btn-secondary filter-btn active" onclick="filterReviews('all')">All Reviews</button>
                <button class="btn btn-secondary filter-btn" onclick="filterReviews('responded')">Responded</button>
                <button class="btn btn-secondary filter-btn" onclick="filterReviews('pending')">Needs Response</button>
                <button class="btn btn-secondary filter-btn" onclick="filterReviews('5')">5 Stars</button>
                <button class="btn btn-secondary filter-btn" onclick="filterReviews('low')">Low Rated (1-3★)</button>
            </div>
        </div>
    </div>

    <div class="table-container">
        <?php if (empty($reviews)): ?>
            <div class="empty-state">
                <i class="fas fa-star-half-alt empty-icon"></i>
                <h3>No Reviews Yet</h3>
                <p>Customer reviews will appear here once they start reviewing your products.</p>
            </div>
        <?php else: ?>
            <div class="reviews-list-container" style="padding: 1.5rem;">
                <?php foreach ($reviews as $review): ?>
                    <div class="review-card seller-review-card" data-review-id="<?= $review['review_id'] ?>" style="border-bottom: 1px solid #eee; padding-bottom: 2rem; margin-bottom: 2rem;">
                        
                        <div style="background: #f8f9fa; padding: 0.75rem 1rem; border-radius: 6px; margin-bottom: 1.5rem; display: flex; justify-content: space-between; align-items: center;">
                            <a href="/products/<?= htmlspecialchars($review['product_slug']) ?>" 
                               style="color: #333; text-decoration: none; font-weight: 600; display: flex; align-items: center; gap: 0.5rem;" target="_blank">
                                <i class="fas fa-box" style="color: #888;"></i> <?= htmlspecialchars($review['product_name']) ?>
                            </a>
                            <span style="color: #666; font-size: 0.9rem;">
                                <?= date('M d, Y', strtotime($review['created_at'])) ?>
                            </span>
                        </div>

                        <div class="review-header" style="display: flex; justify-content: space-between; margin-bottom: 1rem;">
                            <div class="reviewer-info" style="display: flex; gap: 1rem; align-items: center;">
                                <div class="reviewer-avatar" style="width: 40px; height: 40px; background: #eee; border-radius: 50%; display: flex; align-items: center; justify-content: center; color: #666;">
                                    <i class="fas fa-user"></i>
                                </div>
                                <div class="reviewer-details">
                                    <h4 style="margin: 0; font-size: 1rem; color: #333;"><?= htmlspecialchars($review['username']) ?></h4>
                                    <?php if ($review['is_verified_purchase']): ?>
                                        <span class="verified-purchase-badge" style="color: #28a745; font-size: 0.8rem; display: flex; align-items: center; gap: 4px;">
                                            <i class="fas fa-check-circle"></i> Verified Purchase
                                        </span>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <div class="review-rating" style="color: #ffc107;">
                                <?php for ($i = 1; $i <= 5; $i++): ?>
                                    <i class="<?= $i <= $review['rating'] ? 'fas' : 'far' ?> fa-star"></i>
                                <?php endfor; ?>
                            </div>
                        </div>

                        <div class="review-content" style="margin-bottom: 1.5rem; padding-left: 56px;">
                            <?php if ($review['title']): ?>
                                <h3 class="review-title" style="margin: 0 0 0.5rem 0; font-size: 1.1rem; color: #1a1a1a;"><?= htmlspecialchars($review['title']) ?></h3>
                            <?php endif; ?>
                            <p class="review-text" style="color: #555; line-height: 1.6; margin: 0;"><?= nl2br(htmlspecialchars($review['comment'])) ?></p>
                        </div>

                        <div style="padding-left: 56px;">
                            <?php if ($review['response_text']): ?>
                                <div class="seller-response" style="background: #f8f9fa; padding: 1.25rem; border-radius: 8px; border-left: 3px solid #D4AF37;">
                                    <div class="response-header" style="display: flex; align-items: center; margin-bottom: 0.75rem; gap: 0.5rem;">
                                        <i class="fas fa-store" style="color: #D4AF37;"></i>
                                        <strong style="color: #333;">Your Response</strong>
                                        <span style="margin-left: auto; color: #999; font-size: 0.85rem;">
                                            <?= date('M d, Y', strtotime($review['response_date'])) ?>
                                        </span>
                                    </div>
                                    <p class="response-text" style="margin: 0 0 1rem 0; color: #444; line-height: 1.5;"><?= nl2br(htmlspecialchars($review['response_text'])) ?></p>
                                    <button class="btn btn-secondary" style="padding: 0.4rem 0.8rem; font-size: 0.85rem;" onclick="editResponse(<?= $review['review_id'] ?>)">
                                        <i class="fas fa-edit"></i> Edit Response
                                    </button>
                                </div>
                            <?php else: ?>
                                <div>
                                    <button class="btn btn-primary" 
                                            onclick="showResponseForm(<?= $review['review_id'] ?>)">
                                        <i class="fas fa-reply"></i> Respond to Review
                                    </button>
                                </div>
                            <?php endif; ?>

                            <div class="response-form" id="responseForm<?= $review['review_id'] ?>" style="display: none; margin-top: 1.5rem; background: #fff; border: 1px solid #eee; padding: 1.5rem; border-radius: 8px; box-shadow: 0 2px 8px rgba(0,0,0,0.05);">
                                <h4 style="margin-bottom: 1rem; color: #333; font-size: 1rem;">
                                    <i class="fas fa-reply" style="color: #D4AF37;"></i> Your Response
                                </h4>
                                <textarea 
                                    id="responseText<?= $review['review_id'] ?>" 
                                    placeholder="Write a professional and helpful response to this review..."
                                    style="width: 100%; min-height: 120px; padding: 1rem; border: 1px solid #ddd; border-radius: 6px; font-family: inherit; resize: vertical; margin-bottom: 1rem;"
                                    maxlength="1000"
                                ><?= $review['response_text'] ?? '' ?></textarea>
                                <div style="display: flex; gap: 0.75rem; justify-content: flex-end;">
                                    <button class="btn btn-secondary" onclick="hideResponseForm(<?= $review['review_id'] ?>)">
                                        Cancel
                                    </button>
                                    <button class="btn btn-primary" onclick="submitResponse(<?= $review['review_id'] ?>)">
                                        <i class="fas fa-paper-plane"></i> Submit Response
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</div>

<input type="hidden" id="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
<script>
    const csrfToken = document.getElementById('csrf_token').value;

    function showResponseForm(reviewId) {
        document.querySelectorAll('.response-form').forEach(form => form.style.display = 'none');
        const form = document.getElementById('responseForm' + reviewId);
        form.style.display = 'block';
        form.scrollIntoView({ behavior: 'smooth', block: 'center' });
        setTimeout(() => document.getElementById('responseText' + reviewId).focus(), 300);
    }

    function hideResponseForm(reviewId) {
        document.getElementById('responseForm' + reviewId).style.display = 'none';
    }

    function editResponse(reviewId) {
        showResponseForm(reviewId);
    }

    function submitResponse(reviewId) {
        const responseText = document.getElementById('responseText' + reviewId).value.trim();

        if (responseText.length < 10) {
            showToast('Response must be at least 10 characters', 'error');
            return;
        }

        const formData = new FormData();
        formData.append('csrf_token', csrfToken);
        formData.append('review_id', reviewId);
        formData.append('response_text', responseText);

        fetch('/reviews/seller-response', {
            method: 'POST',
            body: new URLSearchParams(formData)
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showToast(data.message, 'success');
                setTimeout(() => location.reload(), 1500);
            } else {
                showToast(data.message, 'error');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showToast('An error occurred', 'error');
        });
    }

    function showToast(message, type = 'info') {
        const toast = document.createElement('div');
        toast.className = `toast-notification toast-${type}`;
        toast.style.cssText = `
            position: fixed;
            bottom: 2rem;
            right: 2rem;
            background: #333;
            color: #fff;
            padding: 1rem 1.5rem;
            border-radius: 8px;
            z-index: 10000;
            display: flex;
            align-items: center;
            gap: 10px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
            animation: slideIn 0.3s ease-out;
        `;
        
        const icon = type === 'success' ? 'check-circle' : (type === 'error' ? 'exclamation-circle' : 'info-circle');
        const color = type === 'success' ? '#2ecc71' : (type === 'error' ? '#e74c3c' : '#3498db');
        
        toast.innerHTML = `<i class="fas fa-${icon}" style="color: ${color}"></i> <span>${message}</span>`;
        document.body.appendChild(toast);
        setTimeout(() => {
            toast.style.opacity = '0';
            toast.style.transform = 'translateY(20px)';
            toast.style.transition = 'all 0.3s ease-in';
            setTimeout(() => toast.remove(), 300);
        }, 3000);
    }

    function filterReviews(filter) {
        document.querySelectorAll('.filter-btn').forEach(b => b.classList.remove('active'));
        event.target.classList.add('active');
        
        const cards = document.querySelectorAll('.review-card');
        cards.forEach(card => {
            let show = true;
            if (filter === 'responded') show = card.querySelector('.seller-response') !== null;
            else if (filter === 'pending') show = card.querySelector('.seller-response') === null;
            else if (filter === '5') show = card.querySelectorAll('.review-rating .fas.fa-star').length === 5;
            else if (filter === 'low') show = card.querySelectorAll('.review-rating .fas.fa-star').length <= 3;
            card.style.display = show ? 'block' : 'none';
        });
    }
</script>