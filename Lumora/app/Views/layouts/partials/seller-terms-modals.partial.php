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
            <p>You must be at least 18 years old and legally able to enter into contracts. You agree to provide accurate, current, and complete information during the registration process and to update such information to keep it accurate, current, and complete.</p>

            <h3>2. Account Security</h3>
            <p>You are responsible for safeguarding the password that you use to access the Service and for any activities or actions under your password. You agree not to disclose your password to any third party.</p>

            <h3>3. Content Ownership</h3>
            <p>You retain all rights to the images and descriptions of products you list on Lumora. However, by listing products, you grant Lumora a non-exclusive, worldwide, royalty-free license to use, display, and reproduce your content for marketing and promotional purposes.</p>

            <h3>4. Termination</h3>
            <p>Lumora reserves the right to suspend or terminate your seller account at any time, without notice, for conduct that we believe violates these Terms or is harmful to other users of the Service, us, or third parties, or for any other reason.</p>

            <h3>5. Liability</h3>
            <p>Lumora is a marketplace platform. We are not responsible for the quality, safety, or legality of the items advertised, the truth or accuracy of the listings, or the ability of sellers to sell items.</p>
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
            <p>This Seller Policy is a part of our Terms of Service. It outlines your rights and obligations as a seller on Lumora.</p>

            <h3>1. Prohibited Items</h3>
            <p>You may only list authentic, handcrafted, or curated accessories. The following are strictly prohibited:</p>
            <ul>
                <li>Counterfeit or "replica" luxury goods.</li>
                <li>Items made from endangered species.</li>
                <li>Stolen goods or property.</li>
                <li>Weapons or hazardous materials.</li>
            </ul>

            <h3>2. Commission and Fees</h3>
            <p>Lumora charges a flat commission rate of <strong>5%</strong> on the final sale price of each item (excluding shipping). This fee is automatically deducted from your payout. Payment processing fees (e.g., PayMongo) may also apply.</p>

            <h3>3. Shipping and Fulfillment</h3>
            <p>Sellers are expected to ship orders within <strong>1-3 business days</strong> of purchase. You must provide valid tracking numbers for all shipments. Failure to ship on time may result in order cancellation and account suspension.</p>

            <h3>4. Returns and Refunds</h3>
            <p>Sellers must honor Lumora's 7-day return policy for items that are damaged, defective, or significantly not as described. For "change of mind" returns, sellers may set their own policies, provided they are clearly stated in the shop description.</p>

            <h3>5. Payout Schedule</h3>
            <p>Earnings are available for withdrawal 7 days after the order is marked as "Delivered" by the courier. This holding period ensures funds are available for potential refunds. Payouts are processed weekly on Wednesdays.</p>

            <h3>6. Customer Service</h3>
            <p>You agree to respond to customer inquiries within 24 hours. Professional and respectful communication is required at all times. Harassment of buyers or Lumora staff will result in immediate account termination.</p>
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
            document.body.style.overflow = 'hidden'; // Prevent background scrolling
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

    // Close on Escape key
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            closeModal(termsModal);
            closeModal(sellerPolicyModal);
        }
    });
});
</script>