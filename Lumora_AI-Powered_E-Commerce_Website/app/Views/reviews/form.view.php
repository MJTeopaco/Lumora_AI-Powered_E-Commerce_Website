<?php
// app/Views/reviews/form.view.php
?>

<div class="review-form-container">
    <div class="review-form-header">
        <h2>Write Your Review</h2>
        <p>Share your experience with this product</p>
    </div>

    <div class="product-info-mini">
        <div class="product-mini-image">
            <?php if (!empty($product['cover_picture'])): ?>
                <img src="<?= base_url($product['cover_picture']) ?>" alt="<?= htmlspecialchars($product['name']) ?>">
            <?php else: ?>
                <i class="fas fa-image"></i>
            <?php endif; ?>
        </div>
        <div class="product-mini-details">
            <h3><?= htmlspecialchars($product['name']) ?></h3>
            <?php if ($hasPurchased): ?>
                <p style="color: #28a745;">
                    <i class="fas fa-check-circle"></i> Verified Purchase
                </p>
            <?php else: ?>
                <p style="color: #666;">
                    <i class="fas fa-info-circle"></i> You haven't purchased this product yet
                </p>
            <?php endif; ?>
        </div>
    </div>

    <form id="reviewForm" enctype="multipart/form-data">
        <input type="hidden" name="csrf_token" value="<?= \App\Core\Session::get('csrf_token') ?>">
        <input type="hidden" name="product_id" value="<?= $product['id'] ?>">
        <input type="hidden" name="rating" id="ratingInput" value="0">

        <div class="star-rating-input">
            <label>Your Rating <span style="color: red;">*</span></label>
            <div class="stars-input-container">
                <i class="far fa-star star-input" data-rating="1"></i>
                <i class="far fa-star star-input" data-rating="2"></i>
                <i class="far fa-star star-input" data-rating="3"></i>
                <i class="far fa-star star-input" data-rating="4"></i>
                <i class="far fa-star star-input" data-rating="5"></i>
            </div>
            <p id="ratingError" style="color: red; font-size: 0.9rem; margin-top: 0.5rem; display: none;">
                Please select a rating
            </p>
        </div>

        <div class="form-group">
            <label for="reviewTitle">Review Title (Optional)</label>
            <input 
                type="text" 
                id="reviewTitle" 
                name="title" 
                placeholder="Sum up your experience in a few words"
                maxlength="200"
            >
        </div>

        <div class="form-group">
            <label for="reviewComment">Your Review <span style="color: red;">*</span></label>
            <textarea 
                id="reviewComment" 
                name="comment" 
                placeholder="Tell us about your experience with this product. What did you like or dislike? How did it meet your expectations?"
                required
                minlength="10"
                maxlength="2000"
            ></textarea>
            <div class="char-count">
                <span id="charCount">0</span> / 2000 characters (minimum 10)
            </div>
        </div>

        <div class="form-group image-upload-section">
            <label>Add Photos (Optional)</label>
            <div class="image-upload-box" onclick="document.getElementById('imageUpload').click()">
                <i class="fas fa-camera"></i>
                <p>Click to upload images</p>
                <small>Up to 5 images • JPG, PNG, GIF, WEBP</small>
                <input 
                    type="file" 
                    id="imageUpload" 
                    name="images[]" 
                    accept="image/*" 
                    multiple
                    onchange="previewImages(event)"
                >
            </div>
            <div class="uploaded-images-preview" id="imagePreview"></div>
        </div>

        <div class="form-actions">
            <button type="button" class="btn-cancel" onclick="window.history.back()">
                Cancel
            </button>
            <button type="submit" class="btn-submit-review" id="submitBtn">
                <i class="fas fa-paper-plane"></i> Submit Review
            </button>
        </div>
    </form>
</div>

<script>
// Star Rating Functionality
const starInputs = document.querySelectorAll('.star-input');
const ratingInput = document.getElementById('ratingInput');
const ratingError = document.getElementById('ratingError');

starInputs.forEach(star => {
    star.addEventListener('click', function() {
        const rating = parseInt(this.dataset.rating);
        ratingInput.value = rating;
        ratingError.style.display = 'none';
        
        // Update star display
        starInputs.forEach((s, index) => {
            if (index < rating) {
                s.classList.remove('far');
                s.classList.add('fas', 'active');
            } else {
                s.classList.remove('fas', 'active');
                s.classList.add('far');
            }
        });
    });

    // Hover effect
    star.addEventListener('mouseenter', function() {
        const rating = parseInt(this.dataset.rating);
        starInputs.forEach((s, index) => {
            if (index < rating) {
                s.classList.add('active');
            }
        });
    });

    star.addEventListener('mouseleave', function() {
        const currentRating = parseInt(ratingInput.value);
        starInputs.forEach((s, index) => {
            if (index >= currentRating) {
                s.classList.remove('active');
            }
        });
    });
});

// Character Counter
const commentTextarea = document.getElementById('reviewComment');
const charCount = document.getElementById('charCount');

commentTextarea.addEventListener('input', function() {
    charCount.textContent = this.value.length;
});

// Image Preview
function previewImages(event) {
    const files = event.target.files;
    const preview = document.getElementById('imagePreview');
    preview.innerHTML = '';

    if (files.length > 5) {
        showToast('Maximum 5 images allowed', 'warning');
        event.target.value = '';
        return;
    }

    Array.from(files).forEach((file, index) => {
        const reader = new FileReader();
        reader.onload = function(e) {
            const div = document.createElement('div');
            div.className = 'preview-image-item';
            div.innerHTML = `
                <img src="${e.target.result}" alt="Preview ${index + 1}">
                <button type="button" class="remove-image-btn" onclick="removeImage(${index})">
                    <i class="fas fa-times"></i>
                </button>
            `;
            preview.appendChild(div);
        };
        reader.readAsDataURL(file);
    });
}

function removeImage(index) {
    const input = document.getElementById('imageUpload');
    const dt = new DataTransfer();
    const files = input.files;

    for (let i = 0; i < files.length; i++) {
        if (i !== index) {
            dt.items.add(files[i]);
        }
    }

    input.files = dt.files;
    previewImages({ target: input });
}

// Form Submission
document.getElementById('reviewForm').addEventListener('submit', function(e) {
    e.preventDefault();

    // Validation
    const rating = parseInt(ratingInput.value);
    const comment = commentTextarea.value.trim();

    if (rating === 0) {
        ratingError.style.display = 'block';
        document.querySelector('.stars-input-container').scrollIntoView({ behavior: 'smooth', block: 'center' });
        return;
    }

    if (comment.length < 10) {
        showToast('Review must be at least 10 characters long', 'error');
        commentTextarea.focus();
        return;
    }

    // Submit form
    const submitBtn = document.getElementById('submitBtn');
    const originalText = submitBtn.innerHTML;
    submitBtn.disabled = true;
    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Submitting...';

    const formData = new FormData(this);

    fetch('<?= base_url('/reviews/submit') ?>', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showToast(data.message, 'success');
            setTimeout(() => {
                if (data.redirect) {
                    window.location.href = data.redirect;
                } else {
                    window.history.back();
                }
            }, 1500);
        } else {
            showToast(data.message, 'error');
            submitBtn.disabled = false;
            submitBtn.innerHTML = originalText;
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showToast('An error occurred. Please try again.', 'error');
        submitBtn.disabled = false;
        submitBtn.innerHTML = originalText;
    });
});

// Standard Toast Function matching other pages
function showToast(message, type = 'info') {
    // Remove existing toast if any
    const existingToast = document.querySelector('.toast-notification');
    if (existingToast) {
        existingToast.remove();
    }
    
    // Create toast element
    const toast = document.createElement('div');
    toast.className = `toast-notification toast-${type}`;
    
    // Icon based on type
    let icon = 'fa-info-circle';
    if (type === 'success') icon = 'fa-check-circle';
    if (type === 'error') icon = 'fa-exclamation-circle';
    if (type === 'warning') icon = 'fa-exclamation-triangle';
    
    toast.innerHTML = `
        <i class="fas ${icon}"></i>
        <span>${message}</span>
    `;
    
    // Add to body
    document.body.appendChild(toast);
    
    // Trigger animation
    setTimeout(() => toast.classList.add('show'), 10);
    
    // Remove after 3 seconds
    setTimeout(() => {
        toast.classList.remove('show');
        setTimeout(() => toast.remove(), 300);
    }, 3000);
}
</script>