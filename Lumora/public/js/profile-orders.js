// public/js/profile-orders.js
// Customer Order History JavaScript

/**
 * View order details in modal
 */
async function viewOrderDetails(orderId) {
    const modal = document.getElementById('orderDetailsModal');
    const content = document.getElementById('orderDetailsContent');
    
    // Show modal with loading state
    modal.style.display = 'flex';
    content.innerHTML = `
        <div class="loading-container">
            <i class="fas fa-spinner fa-spin"></i>
            <p>Loading order details...</p>
        </div>
    `;
    
    try {
        const response = await fetch(`/profile/orders/details?order_id=${orderId}`, {
            method: 'GET',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Content-Type': 'application/json'
            }
        });
        
        const result = await response.json();
        
        if (result.success) {
            displayOrderDetails(result.order, result.items);
        } else {
            content.innerHTML = `
                <div class="error-container">
                    <i class="fas fa-exclamation-circle"></i>
                    <p>${result.message || 'Failed to load order details'}</p>
                </div>
            `;
        }
    } catch (error) {
        console.error('Error fetching order details:', error);
        content.innerHTML = `
            <div class="error-container">
                <i class="fas fa-exclamation-circle"></i>
                <p>An error occurred while loading order details</p>
            </div>
        `;
    }
}

/**
 * Display order details in modal
 */
function displayOrderDetails(order, items) {
    const content = document.getElementById('orderDetailsContent');
    
    const statusClass = order.order_status.toLowerCase().replace(/_/g, '-');
    const statusDisplay = order.order_status.replace(/_/g, ' ');
    
    let itemsHtml = '';
    if (items && items.length > 0) {
        itemsHtml = items.map(item => {
            const variantInfo = [];
            if (item.color) variantInfo.push(`Color: ${item.color}`);
            if (item.size) variantInfo.push(`Size: ${item.size}`);
            if (item.material) variantInfo.push(`Material: ${item.material}`);
            
            return `
                <div class="order-item-detail">
                    <div class="item-image">
                        ${item.cover_picture 
                            ? `<img src="/${item.cover_picture}" alt="${item.product_name}">`
                            : '<div class="no-image"><i class="fas fa-image"></i></div>'
                        }
                    </div>
                    <div class="item-info">
                        <div class="item-name">${item.product_name}</div>
                        ${item.shop_name 
                            ? `<div class="item-shop"><i class="fas fa-store"></i> ${item.shop_name}</div>`
                            : ''
                        }
                        ${variantInfo.length > 0 
                            ? `<div class="item-variant">${variantInfo.join(' • ')}</div>`
                            : ''
                        }
                        ${item.personalized_notes 
                            ? `<div class="item-notes"><strong>Note:</strong> ${item.personalized_notes}</div>`
                            : ''
                        }
                        <div class="item-pricing">
                            <span class="item-quantity">Qty: ${item.quantity}</span>
                            <span class="item-price">₱${parseFloat(item.price_at_purchase).toFixed(2)} each</span>
                            <span class="item-total">₱${parseFloat(item.total_price).toFixed(2)}</span>
                        </div>
                    </div>
                </div>
            `;
        }).join('');
    } else {
        itemsHtml = '<p>No items found</p>';
    }
    
    const fullAddress = [
        order.address_line_1,
        order.address_line_2,
        order.barangay,
        order.city,
        order.province,
        order.region,
        order.postal_code
    ].filter(part => part).join(', ');
    
    content.innerHTML = `
        <div class="order-details-container">
            <div class="detail-section">
                <h4><i class="fas fa-info-circle"></i> Order Information</h4>
                <div class="detail-grid">
                    <div class="detail-item">
                        <span class="detail-label">Order ID</span>
                        <span class="detail-value">#${String(order.order_id).padStart(6, '0')}</span>
                    </div>
                    <div class="detail-item">
                        <span class="detail-label">Status</span>
                        <span class="order-status status-${statusClass}">${statusDisplay}</span>
                    </div>
                    <div class="detail-item">
                        <span class="detail-label">Order Date</span>
                        <span class="detail-value">${formatDateTime(order.created_at)}</span>
                    </div>
                    <div class="detail-item">
                        <span class="detail-label">Last Updated</span>
                        <span class="detail-value">${formatDateTime(order.updated_at)}</span>
                    </div>
                </div>
            </div>
            
            <div class="detail-section">
                <h4><i class="fas fa-map-marker-alt"></i> Shipping Address</h4>
                <div class="address-display">
                    <p class="recipient-name">${order.full_name || 'N/A'}</p>
                    <p class="recipient-phone">${order.phone_number || 'N/A'}</p>
                    <p class="recipient-address">${fullAddress || 'No address provided'}</p>
                </div>
            </div>
            
            <div class="detail-section">
                <h4><i class="fas fa-box"></i> Order Items</h4>
                <div class="order-items-list">
                    ${itemsHtml}
                </div>
            </div>
            
            <div class="detail-section">
                <h4><i class="fas fa-receipt"></i> Order Summary</h4>
                <div class="summary-grid">
                    <div class="summary-row">
                        <span>Subtotal</span>
                        <span>₱${(parseFloat(order.total_amount) - parseFloat(order.shipping_fee)).toFixed(2)}</span>
                    </div>
                    <div class="summary-row">
                        <span>Shipping Fee</span>
                        <span>₱${parseFloat(order.shipping_fee).toFixed(2)}</span>
                    </div>
                    <div class="summary-row total">
                        <span><strong>Total Amount</strong></span>
                        <span><strong>₱${parseFloat(order.total_amount).toFixed(2)}</strong></span>
                    </div>
                </div>
            </div>
        </div>
    `;
}

/**
 * Close order details modal
 */
function closeOrderModal() {
    document.getElementById('orderDetailsModal').style.display = 'none';
}

/**
 * Open cancel order confirmation
 */
function confirmCancelOrder(orderId) {
    document.getElementById('cancelOrderId').value = orderId;
    document.getElementById('cancelOrderModal').style.display = 'flex';
}

/**
 * Close cancel modal
 */
function closeCancelModal() {
    document.getElementById('cancelOrderModal').style.display = 'none';
}

/**
 * Format date and time
 */
function formatDateTime(dateString) {
    const date = new Date(dateString);
    const options = { 
        year: 'numeric', 
        month: 'long', 
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    };
    return date.toLocaleDateString('en-US', options);
}

/**
 * Show toast notification
 */
function showToast(message, type = 'info') {
    // Remove existing toasts
    const existingToasts = document.querySelectorAll('.toast-notification');
    existingToasts.forEach(toast => toast.remove());
    
    // Create toast element
    const toast = document.createElement('div');
    toast.className = `toast-notification toast-${type}`;
    
    const icon = type === 'success' ? 'fa-check-circle' : 
                 type === 'error' ? 'fa-exclamation-circle' : 
                 'fa-info-circle';
    
    toast.innerHTML = `
        <i class="fas ${icon}"></i>
        <span>${message}</span>
    `;
    
    document.body.appendChild(toast);
    
    // Trigger animation
    setTimeout(() => toast.classList.add('show'), 10);
    
    // Auto remove after 3 seconds
    setTimeout(() => {
        toast.classList.remove('show');
        setTimeout(() => toast.remove(), 300);
    }, 3000);
}

// Close modals when clicking outside
window.onclick = function(event) {
    const orderModal = document.getElementById('orderDetailsModal');
    const cancelModal = document.getElementById('cancelOrderModal');
    
    if (event.target === orderModal) {
        closeOrderModal();
    }
    if (event.target === cancelModal) {
        closeCancelModal();
    }
}

// Show status message if present (from URL params)
document.addEventListener('DOMContentLoaded', function() {
    const urlParams = new URLSearchParams(window.location.search);
    let message = urlParams.get('message');
    const statusType = urlParams.get('status_type');
    
    if (message) {
        // FIX: Manually replace '+' with space to ensure clean display
        message = decodeURIComponent(message.replace(/\+/g, ' '));
        
        showToast(message, statusType || 'info');
        
        // Clean URL
        const cleanUrl = window.location.pathname + 
            (urlParams.get('status') ? '?status=' + urlParams.get('status') : '');
        window.history.replaceState({}, '', cleanUrl);
    }
});

// Add toast styles if not already present
if (!document.getElementById('toast-styles')) {
    const style = document.createElement('style');
    style.id = 'toast-styles';
    style.textContent = `
        .toast-notification {
            position: fixed;
            top: 20px;
            right: 20px;
            background: white;
            padding: 15px 20px;
            border-radius: 8px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
            display: flex;
            align-items: center;
            gap: 12px;
            z-index: 10000;
            opacity: 0;
            transform: translateX(400px);
            transition: all 0.3s ease;
        }
        
        .toast-notification.show {
            opacity: 1;
            transform: translateX(0);
        }
        
        .toast-success {
            border-left: 4px solid #28a745;
        }
        
        .toast-success i {
            color: #28a745;
        }
        
        .toast-error {
            border-left: 4px solid #dc3545;
        }
        
        .toast-error i {
            color: #dc3545;
        }
        
        .toast-info {
            border-left: 4px solid #D4AF37;
        }
        
        .toast-info i {
            color: #D4AF37;
        }
    `;
    document.head.appendChild(style);
}