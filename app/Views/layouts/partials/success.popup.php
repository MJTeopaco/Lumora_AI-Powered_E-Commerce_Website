<!-- Add this to your homepage (index.view.php or similar) -->
<!-- Place it near the top of the body or where you have other session messages -->

<?php if (isset($_SESSION['seller_registration_success'])): ?>
<div id="successModal" class="modal-overlay">
    <div class="modal-content">
        <div class="modal-icon">
            <i class="fas fa-check-circle"></i>
        </div>
        <h2>Application Submitted Successfully!</h2>
        <p><?= htmlspecialchars($_SESSION['seller_registration_success']); ?></p>
        <button onclick="closeSuccessModal()" class="btn-close-modal">Got it!</button>
    </div>
</div>

<style>
.modal-overlay {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0, 0, 0, 0.6);
    display: flex;
    align-items: center;
    justify-content: center;
    z-index: 9999;
    animation: fadeIn 0.3s ease;
}

@keyframes fadeIn {
    from { opacity: 0; }
    to { opacity: 1; }
}

.modal-content {
    background: white;
    padding: 40px;
    border-radius: 20px;
    max-width: 500px;
    width: 90%;
    text-align: center;
    box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
    animation: slideUp 0.3s ease;
}

@keyframes slideUp {
    from {
        opacity: 0;
        transform: translateY(30px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

@keyframes fadeOut {
    from { opacity: 1; }
    to { opacity: 0; }
}

.modal-icon {
    font-size: 64px;
    color: #2ecc71;
    margin-bottom: 20px;
}

.modal-content h2 {
    color: #333;
    margin-bottom: 15px;
    font-size: 24px;
}

.modal-content p {
    color: #666;
    line-height: 1.6;
    margin-bottom: 25px;
    font-size: 16px;
}

.btn-close-modal {
    background: linear-gradient(135deg, #1e4d3d 0%, #2d5a4a 100%);
    color: white;
    border: none;
    padding: 14px 40px;
    border-radius: 10px;
    font-size: 16px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.3s ease;
    box-shadow: 0 4px 15px rgba(30, 77, 61, 0.3);
}

.btn-close-modal:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 20px rgba(30, 77, 61, 0.4);
}

.btn-close-modal:active {
    transform: translateY(0);
}
</style>

<script>
function closeSuccessModal() {
    const modal = document.getElementById('successModal');
    modal.style.animation = 'fadeOut 0.3s ease';
    setTimeout(() => {
        modal.style.display = 'none';
    }, 300);
}

// Close modal when clicking outside
document.getElementById('successModal').addEventListener('click', function(e) {
    if (e.target === this) {
        closeSuccessModal();
    }
});

// Close modal with Escape key
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        closeSuccessModal();
    }
});


</script>

<?php 
unset($_SESSION['seller_registration_success']); 
?>
<?php endif; ?>