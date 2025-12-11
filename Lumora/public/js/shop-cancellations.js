/**
 * Shop Cancellations Management
 * Handles displaying order details in a modal
 */

function viewCancellationDetails(orderId) {
    const modal = document.getElementById('cancellationDetailsModal');
    const content = document.getElementById('modalContent');
    
    // 1. Show Modal
    modal.classList.add('active');
    
    // 2. Reset Content to Loading State
    content.innerHTML = `
        <div class="loading-state">
            <i class="fas fa-spinner fa-spin"></i>
            <p>Retrieving cancellation details...</p>
        </div>
    `;
    
    // 3. Fetch Data
    // Note: Reusing the generic shop order details endpoint which works for any status
    fetch(`/shop/orders/details?order_id=${orderId}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                renderDetails(data.data);
            } else {
                content.innerHTML = `
                    <div style="text-align: center; color: #dc3545; padding: 2rem;">
                        <i class="fas fa-exclamation-triangle" style="font-size: 2rem; margin-bottom: 1rem;"></i>
                        <p>${data.message || 'Failed to load details'}</p>
                    </div>
                `;
            }
        })
        .catch(error => {
            console.error('Error:', error);
            content.innerHTML = '<p style="text-align: center; color: red;">An error occurred while loading details.</p>';
        });
}

function renderDetails(order) {
    const content = document.getElementById('modalContent');
    
    // Format Items
    const itemsHtml = order.items.map(item => `
        <div class="order-item">
            <div style="display: flex; align-items: center; gap: 1rem;">
                <div style="width: 40px; height: 40px; background: #eee; border-radius: 4px; overflow: hidden;">
                    ${item.cover_picture ? 
                        `<img src="/${item.cover_picture}" style="width: 100%; height: 100%; object-fit: cover;">` : 
                        '<div style="width: 100%; height: 100%; display: flex; align-items: center; justify-content: center; color: #999;"><i class="fas fa-image"></i></div>'
                    }
                </div>
                <div>
                    <div style="font-weight: 600; color: #333;">${item.product_name}</div>
                    <div style="font-size: 0.85rem; color: #666;">
                        ${item.variant_name ? item.variant_name : ''} 
                        ${item.color ? '• ' + item.color : ''} 
                        ${item.size ? '• ' + item.size : ''}
                    </div>
                </div>
            </div>
            <div style="text-align: right;">
                <div style="font-weight: 600;">₱${parseFloat(item.price).toFixed(2)}</div>
                <div style="font-size: 0.85rem; color: #666;">Qty: ${item.quantity}</div>
            </div>
        </div>
    `).join('');

    // Format Date
    const date = new Date(order.created_at).toLocaleDateString('en-US', {
        year: 'numeric', month: 'long', day: 'numeric', hour: '2-digit', minute: '2-digit'
    });

    // Render Full Content
    content.innerHTML = `
        <div class="detail-group">
            <div class="detail-label">Customer Information</div>
            <div style="font-size: 1.1rem; font-weight: 600; color: #1a1a1a;">${order.customer_name || 'Guest'}</div>
            <div style="color: #666;">${order.email || ''}</div>
            <div style="margin-top: 0.5rem; font-size: 0.9rem; color: #444;">
                <i class="fas fa-map-marker-alt" style="color: #D4AF37; width: 20px;"></i>
                ${[order.address_line_1, order.city, order.province].filter(Boolean).join(', ')}
            </div>
        </div>

        <div class="detail-group">
            <div class="detail-label">Cancellation Status</div>
            <div style="display: inline-block; background: #ffebee; color: #c62828; padding: 0.25rem 0.75rem; border-radius: 20px; font-weight: 600; font-size: 0.9rem;">
                <i class="fas fa-ban"></i> Cancelled
            </div>
            <div style="margin-top: 0.5rem; font-size: 0.9rem; color: #666;">
                Date: ${date}
            </div>
        </div>

        <div class="detail-group">
            <div class="detail-label">Order Items</div>
            <div class="order-items-list">
                ${itemsHtml}
            </div>
        </div>

        <div class="detail-group" style="text-align: right;">
            <div class="detail-label">Total Refund Amount</div>
            <div style="font-size: 1.5rem; font-weight: 700; color: #D4AF37;">
                ₱${parseFloat(order.total_amount).toFixed(2)}
            </div>
        </div>
    `;
}

function closeCancellationModal() {
    const modal = document.getElementById('cancellationDetailsModal');
    modal.classList.remove('active');
}

// Close modal when clicking outside
window.onclick = function(event) {
    const modal = document.getElementById('cancellationDetailsModal');
    if (event.target === modal) {
        closeCancellationModal();
    }
}