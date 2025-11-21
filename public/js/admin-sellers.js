/**
 * Admin Sellers JavaScript
 * app/public/js/admin-sellers.js
 */

// View seller details in modal
function viewSellerDetails(seller) {
    // Populate shop information
    document.getElementById('detail_shop_name').textContent = seller.shop_name || 'N/A';
    document.getElementById('detail_slug').textContent = seller.slug || 'N/A';
    document.getElementById('detail_description').textContent = seller.shop_description || 'No description provided';

    // Populate owner information
    document.getElementById('detail_username').textContent = seller.username || 'N/A';
    document.getElementById('detail_email').textContent = seller.email || 'N/A';
    document.getElementById('detail_user_id').textContent = seller.user_id || 'N/A';

    // Populate contact information
    document.getElementById('detail_contact_email').textContent = seller.contact_email || 'N/A';
    document.getElementById('detail_contact_phone').textContent = seller.contact_phone || 'N/A';

    // Populate address information
    const address1 = seller.address_line_1 || '';
    const address2 = seller.address_line_2 || '';
    const fullAddress = address2 ? `${address1}, ${address2}` : address1;
    
    document.getElementById('detail_address').textContent = fullAddress || 'N/A';
    document.getElementById('detail_barangay').textContent = seller.barangay || 'N/A';
    document.getElementById('detail_city').textContent = seller.city || 'N/A';
    document.getElementById('detail_province').textContent = seller.province || 'N/A';
    document.getElementById('detail_region').textContent = seller.region || 'N/A';
    document.getElementById('detail_postal_code').textContent = seller.postal_code || 'N/A';

    // Populate timeline
    const appliedDate = new Date(seller.applied_at);
    document.getElementById('detail_applied_date').textContent = appliedDate.toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'long',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    });

    // Check if approved and show approved date
    const approvedDateSection = document.getElementById('approved_date_section');
    if (seller.approved_at) {
        const approvedDate = new Date(seller.approved_at);
        document.getElementById('detail_approved_date').textContent = approvedDate.toLocaleDateString('en-US', {
            year: 'numeric',
            month: 'long',
            day: 'numeric',
            hour: '2-digit',
            minute: '2-digit'
        });
        approvedDateSection.style.display = 'block';
    } else {
        approvedDateSection.style.display = 'none';
    }

    // Add action buttons based on status
    const actionButtonsDiv = document.getElementById('modal_action_buttons');
    actionButtonsDiv.innerHTML = '';

    if (!seller.approved_at) {
        // Pending seller - show approve/reject buttons
        actionButtonsDiv.innerHTML = `
            <button class="btn btn-success" onclick="approveSeller(${seller.user_id}, '${escapeHtml(seller.shop_name)}')">
                <i class="fas fa-check"></i> Approve Seller
            </button>
            <button class="btn btn-danger" onclick="rejectSeller(${seller.user_id}, '${escapeHtml(seller.shop_name)}')">
                <i class="fas fa-times"></i> Reject Application
            </button>
        `;
    } else {
        // Approved seller - show suspend button
        actionButtonsDiv.innerHTML = `
            <button class="btn btn-warning" onclick="suspendSeller(${seller.user_id}, '${escapeHtml(seller.shop_name)}')">
                <i class="fas fa-ban"></i> Suspend Seller
            </button>
        `;
    }

    // Show modal
    document.getElementById('sellerDetailsModal').classList.add('active');
}

// Close details modal
function closeDetailsModal() {
    document.getElementById('sellerDetailsModal').classList.remove('active');
}

// Approve seller
function approveSeller(userId, shopName) {
    if (confirm(`Are you sure you want to APPROVE the seller application for "${shopName}"?\n\nThis will grant them full seller privileges.`)) {
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = '/admin/approve-seller';

        const input = document.createElement('input');
        input.type = 'hidden';
        input.name = 'user_id';
        input.value = userId;

        form.appendChild(input);
        document.body.appendChild(form);
        form.submit();
    }
}

// Reject seller
function rejectSeller(userId, shopName) {
    if (confirm(`Are you sure you want to REJECT the seller application for "${shopName}"?\n\nThis action will:\n- Delete their seller role\n- Mark their shop as deleted\n- Remove them from the pending list\n\nThis action cannot be undone.`)) {
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = '/admin/reject-seller';

        const input = document.createElement('input');
        input.type = 'hidden';
        input.name = 'user_id';
        input.value = userId;

        form.appendChild(input);
        document.body.appendChild(form);
        form.submit();
    }
}

// Suspend seller
function suspendSeller(userId, shopName) {
    if (confirm(`Are you sure you want to SUSPEND "${shopName}"?\n\nThis will:\n- Revoke their seller privileges\n- Hide their shop from the marketplace\n- Prevent them from managing products\n\nYou can reactivate them later if needed.`)) {
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = '/admin/suspend-seller';

        const input = document.createElement('input');
        input.type = 'hidden';
        input.name = 'user_id';
        input.value = userId;

        form.appendChild(input);
        document.body.appendChild(form);
        form.submit();
    }
}

// Utility function to escape HTML
function escapeHtml(text) {
    const map = {
        '&': '&amp;',
        '<': '&lt;',
        '>': '&gt;',
        '"': '&quot;',
        "'": '&#039;'
    };
    return text.replace(/[&<>"']/g, m => map[m]);
}

// Close modal on background click
window.onclick = function(event) {
    const modal = document.getElementById('sellerDetailsModal');
    if (event.target === modal) {
        closeDetailsModal();
    }
}

// Close modal on ESC key
document.addEventListener('keydown', function(event) {
    if (event.key === 'Escape') {
        closeDetailsModal();
    }
});

// Auto-hide alerts after 5 seconds
document.addEventListener('DOMContentLoaded', function() {
    const alerts = document.querySelectorAll('.alert');
    
    alerts.forEach(alert => {
        setTimeout(() => {
            alert.style.opacity = '0';
            setTimeout(() => {
                alert.remove();
            }, 300);
        }, 5000);
    });
});

// Add search/filter functionality
function filterTable(tableId, searchValue) {
    const table = document.getElementById(tableId);
    const rows = table.getElementsByTagName('tr');
    
    for (let i = 1; i < rows.length; i++) {
        const row = rows[i];
        const cells = row.getElementsByTagName('td');
        let found = false;
        
        for (let j = 0; j < cells.length; j++) {
            const cellText = cells[j].textContent || cells[j].innerText;
            if (cellText.toLowerCase().indexOf(searchValue.toLowerCase()) > -1) {
                found = true;
                break;
            }
        }
        
        row.style.display = found ? '' : 'none';
    }
}