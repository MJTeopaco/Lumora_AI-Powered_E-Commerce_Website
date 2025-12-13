<?php
// app/Views/layouts/partials/seller-terms-modals.partial.php
?>

<div id="termsModal" class="modal">
    <div class="modal-content">
        <span class="modal-close-btn" data-target="termsModal">&times;</span>
        <div class="modal-header">
            <h2>Terms and Conditions</h2>
            <p class="effective-date">Effective Date: January 1, 2025</p>
        </div>
        <div class="modal-body">
            <p>Welcome to Lumora. By registering as a seller ("Vendor") on our platform, you agree to be bound by these Terms and Conditions.</p>

            <h3>1. Seller Eligibility</h3>
            <p>You must be at least 18 years old and legally able to enter into contracts. You agree to provide accurate, current, and complete information during the registration process.</p>

            <h3>2. Content Ownership</h3>
            <p>You retain all rights to the images and descriptions of products you list on Lumora. However, by listing products, you grant Lumora a license to use your content for marketing purposes.</p>

            <h3>3. Termination</h3>
            <p>Lumora reserves the right to suspend or terminate your seller account at any time for conduct that violates these Terms.</p>
        </div>
    </div>
</div>

<div id="sellerPolicyModal" class="modal">
    <div class="modal-content">
        <span class="modal-close-btn" data-target="sellerPolicyModal">&times;</span>
        <div class="modal-header">
            <h2>Seller Policy</h2>
            <p class="effective-date">Last Updated: January 1, 2025</p>
        </div>
        <div class="modal-body">
            <p>This Seller Policy outlines your rights and obligations as a seller on Lumora.</p>

            <h3>1. Prohibited Items</h3>
            <p>You may only list authentic, handcrafted, or curated accessories. Counterfeit goods, weapons, and hazardous materials are strictly prohibited.</p>

            <h3>2. Commission and Fees</h3>
            <p>Lumora charges a flat commission rate of <strong>5%</strong> on the final sale price of each item. This fee is automatically deducted from your payout.</p>

            <h3>3. Shipping and Fulfillment</h3>
            <p>Sellers are expected to ship orders within <strong>1-3 business days</strong> of purchase and provide valid tracking numbers.</p>

            <h3>4. Payout Schedule</h3>
            <p>Earnings are available for withdrawal 7 days after the order is marked as "Delivered".</p>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Get modal elements
    const termsModal = document.getElementById('termsModal');
    const sellerPolicyModal = document.getElementById('sellerPolicyModal');
    
    // Get trigger links
    const termsLink = document.getElementById('terms-link');
    const sellerPolicyLink = document.getElementById('seller-policy-link');
    
    // Get close buttons
    const closeButtons = document.querySelectorAll('.modal-close-btn');

    // Function to open modal
    function openModal(modal) {
        if (modal) {
            modal.style.display = "flex"; 
            setTimeout(() => {
                modal.classList.add('show');
            }, 10);
            document.body.style.overflow = 'hidden'; 
        }
    }

    // Function to close modal
    function closeModal(modal) {
        if (modal) {
            modal.classList.remove('show');
            setTimeout(() => {
                modal.style.display = "none";
            }, 400); 
            document.body.style.overflow = '';
        }
    }

    // Event Listeners for Links
    if (termsLink) {
        termsLink.addEventListener('click', function(e) {
            e.preventDefault();
            openModal(termsModal);
        });
    }

    if (sellerPolicyLink) {
        sellerPolicyLink.addEventListener('click', function(e) {
            e.preventDefault();
            openModal(sellerPolicyModal);
        });
    }

    // Event Listeners for Close Buttons
    closeButtons.forEach(btn => {
        btn.addEventListener('click', function() {
            const modalId = this.getAttribute('data-target');
            const modal = document.getElementById(modalId);
            closeModal(modal);
        });
    });

    // Close when clicking outside
    window.addEventListener('click', function(e) {
        if (e.target.classList.contains('modal')) {
            closeModal(e.target);
        }
    });
});
</script>