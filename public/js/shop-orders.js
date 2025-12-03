// public/js/shop-orders.js
// Order Management JavaScript for Seller Dashboard

let currentOrderId = null;
let currentNewStatus = null;

/**
 * View order details in modal
 */
async function viewOrderDetails(orderId) {
    const modal = document.getElementById('orderDetailsModal');
    const content = document.getElementById('orderDetailsContent');
    
    // Show modal with loading state - USING FLEX TO CENTER
    modal.style.display = 'flex';
    // Trigger reflow to ensure transition works if you have one
    setTimeout(() => modal.classList.add('show'), 10);

    content.innerHTML = `
        <div class="loading-spinner">
            <i class="fas fa-spinner fa-spin"></i> Loading order details...
        </div>
    `;
    
    try {
        const response = await fetch(`/shop/orders/details?order_id=${orderId}`, {
            method: 'GET',
            headers: {
                'Content-Type': 'application/json'
            }
        });
        
        const result = await response.json();
        
        if (result.success) {
            displayOrderDetails(result.data);
        } else {
            content.innerHTML = `
                <div class="error-message">
                    <i class="fas fa-exclamation-circle"></i>
                    <p>${result.message || 'Failed to load order details'}</p>
                </div>
            `;
        }
    } catch (error) {
        console.error('Error fetching order details:', error);
        content.innerHTML = `
            <div class="error-message">
                <i class="fas fa-exclamation-circle"></i>
                <p>An error occurred while loading order details</p>
            </div>
        `;
    }
}

/**
 * Display order details in modal
 */
function displayOrderDetails(order) {
    const content = document.getElementById('orderDetailsContent');
    
    const statusClass = order.order_status.toLowerCase().replace(/_/g, '-');
    const statusDisplay = order.order_status.replace(/_/g, ' ');
    
    let itemsHtml = '';
    if (order.items && order.items.length > 0) {
        itemsHtml = order.items.map(item => {
            const variantInfo = [];
            if (item.color) variantInfo.push(`Color: ${item.color}`);
            if (item.size) variantInfo.push(`Size: ${item.size}`);
            if (item.material) variantInfo.push(`Material: ${item.material}`);
            
            return `
                <div class="order-item">
                    <div class="order-item-image">
                        ${item.cover_picture 
                            ? `<img src="/${item.cover_picture}" alt="${item.product_name}">`
                            : '<div class="no-image"><i class="fas fa-image"></i></div>'
                        }
                    </div>
                    <div class="order-item-details">
                        <div class="order-item-name">${item.product_name}</div>
                        ${variantInfo.length > 0 
                            ? `<div class="order-item-variant">${variantInfo.join(' • ')}</div>`
                            : ''
                        }
                        ${item.personalized_notes 
                            ? `<div class="order-item-variant"><strong>Note:</strong> ${item.personalized_notes}</div>`
                            : ''
                        }
                        <div class="order-item-price">
                            <span>₱${parseFloat(item.price_at_purchase).toFixed(2)} × ${item.quantity}</span>
                            <strong>₱${parseFloat(item.total_price).toFixed(2)}</strong>
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
        <div class="order-detail-section">
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
                    <span class="detail-value">${formatDate(order.created_at)}</span>
                </div>
                <div class="detail-item">
                    <span class="detail-label">Last Updated</span>
                    <span class="detail-value">${formatDate(order.updated_at)}</span>
                </div>
            </div>
        </div>
        
        <div class="order-detail-section">
            <h4><i class="fas fa-user"></i> Customer Information</h4>
            <div class="detail-grid">
                <div class="detail-item">
                    <span class="detail-label">Name</span>
                    <span class="detail-value">${order.customer_name || 'N/A'}</span>
                </div>
                <div class="detail-item">
                    <span class="detail-label">Email</span>
                    <span class="detail-value">${order.customer_email || 'N/A'}</span>
                </div>
                <div class="detail-item">
                    <span class="detail-label">Phone</span>
                    <span class="detail-value">${order.customer_phone || 'N/A'}</span>
                </div>
            </div>
        </div>
        
        <div class="order-detail-section">
            <h4><i class="fas fa-map-marker-alt"></i> Shipping Address</h4>
            <div class="detail-value">${fullAddress || 'No address provided'}</div>
        </div>
        
        <div class="order-detail-section">
            <h4><i class="fas fa-box"></i> Order Items</h4>
            <div class="order-items-list">
                ${itemsHtml}
            </div>
        </div>
        
        <div class="order-detail-section">
            <h4><i class="fas fa-receipt"></i> Order Summary</h4>
            <div class="detail-grid">
                <div class="detail-item">
                    <span class="detail-label">Subtotal</span>
                    <span class="detail-value">₱${(parseFloat(order.total_amount) - parseFloat(order.shipping_fee)).toFixed(2)}</span>
                </div>
                <div class="detail-item">
                    <span class="detail-label">Shipping Fee</span>
                    <span class="detail-value">₱${parseFloat(order.shipping_fee).toFixed(2)}</span>
                </div>
                <div class="detail-item">
                    <span class="detail-label"><strong>Total Amount</strong></span>
                    <span class="detail-value"><strong style="color: #D4AF37; font-size: 18px;">₱${parseFloat(order.total_amount).toFixed(2)}</strong></span>
                </div>
            </div>
        </div>
    `;
}

/**
 * Close order details modal
 */
function closeOrderDetailsModal() {
    const modal = document.getElementById('orderDetailsModal');
    modal.classList.remove('show');
    setTimeout(() => {
        modal.style.display = 'none';
    }, 300);
}

/**
 * Update order status
 */
function updateOrderStatus(orderId, newStatus) {
    currentOrderId = orderId;
    currentNewStatus = newStatus;
    
    const statusMessages = {
        'READY_TO_SHIP': 'Mark this order as ready to ship?',
        'SHIPPED': 'Mark this order as shipped?',
        'DELIVERED': 'Mark this order as delivered?'
    };
    
    const modal = document.getElementById('statusUpdateModal');
    const message = document.getElementById('statusUpdateMessage');
    
    message.textContent = statusMessages[newStatus] || 'Update order status?';
    
    // Show modal using FLEX
    modal.style.display = 'flex';
    setTimeout(() => modal.classList.add('show'), 10);
}

/**
 * Confirm status update
 */
async function confirmStatusUpdate() {
    if (!currentOrderId || !currentNewStatus) {
        return;
    }
    
    const csrfToken = document.getElementById('csrf_token').value;
    
    try {
        const response = await fetch('/shop/orders/update-status', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-Token': csrfToken
            },
            body: JSON.stringify({
                order_id: currentOrderId,
                status: currentNewStatus
            })
        });
        
        const result = await response.json();
        
        if (result.success) {
            showToast('Order status updated successfully', 'success');
            closeStatusUpdateModal();
            
            // Reload page after short delay
            setTimeout(() => {
                window.location.reload();
            }, 1000);
        } else {
            showToast(result.message || 'Failed to update order status', 'error');
            closeStatusUpdateModal();
        }
    } catch (error) {
        console.error('Error updating order status:', error);
        showToast('An error occurred while updating order status', 'error');
        closeStatusUpdateModal();
    }
}

/**
 * Close status update modal
 */
function closeStatusUpdateModal() {
    const modal = document.getElementById('statusUpdateModal');
    modal.classList.remove('show');
    setTimeout(() => {
        modal.style.display = 'none';
    }, 300);
    
    currentOrderId = null;
    currentNewStatus = null;
}

/**
 * Format date for display
 */
function formatDate(dateString) {
    const date = new Date(dateString);
    const options = { 
        year: 'numeric', 
        month: 'short', 
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
    const statusModal = document.getElementById('statusUpdateModal');
    
    if (event.target === orderModal) {
        closeOrderDetailsModal();
    }
    if (event.target === statusModal) {
        closeStatusUpdateModal();
    }
}

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
        
        .error-message {
            text-align: center;
            padding: 40px;
            color: #dc3545;
        }
        
        .error-message i {
            font-size: 48px;
            margin-bottom: 15px;
        }
    `;
    document.head.appendChild(style);
}