// public/js/cart.js - Enhanced Version
// Shopping Cart JavaScript for Lumora with improved UX

/**
 * FIXED: Helper to generate correct URLs for subfolders/ngrok
 * Uses the global BASE_URL defined in header.partial.php
 */
function getUrl(path) {
    // Ensure BASE_URL is defined (from header)
    const base = typeof BASE_URL !== 'undefined' ? BASE_URL : '';
    // Remove leading slash from path if present to avoid double slashes
    const cleanPath = path.startsWith('/') ? path.substring(1) : path;
    return `${base}/${cleanPath}`;
}

/**
 * Update cart item quantity
 */
function updateQuantity(variantId, change) {
    const input = document.querySelector(`input[data-variant-id="${variantId}"]`);
    if (!input) return;
    
    let newQuantity = parseInt(input.value) + change;
    const maxQuantity = parseInt(input.max);
    
    // Validate quantity
    if (newQuantity < 1) {
        newQuantity = 1;
        showToast('Minimum quantity is 1', 'warning');
    }
    if (newQuantity > maxQuantity) {
        newQuantity = maxQuantity;
        showToast(`Maximum available quantity is ${maxQuantity}`, 'warning');
    }
    
    // Update input value
    input.value = newQuantity;
    
    // Update button states
    updateQuantityButtons(variantId, newQuantity, maxQuantity);
    
    // Send AJAX request
    updateQuantityAjax(variantId, newQuantity);
}

/**
 * Update quantity directly from input change
 */
function updateQuantityDirect(variantId, quantity) {
    const input = document.querySelector(`input[data-variant-id="${variantId}"]`);
    if (!input) return;
    
    let newQuantity = parseInt(quantity);
    const maxQuantity = parseInt(input.max);
    
    // Validate quantity
    if (isNaN(newQuantity) || newQuantity < 1) {
        newQuantity = 1;
        showToast('Quantity must be at least 1', 'warning');
    }
    if (newQuantity > maxQuantity) {
        newQuantity = maxQuantity;
        showToast(`Only ${maxQuantity} available in stock`, 'warning');
    }
    
    // Update input value
    input.value = newQuantity;
    
    // Update button states
    updateQuantityButtons(variantId, newQuantity, maxQuantity);
    
    // Send AJAX request
    updateQuantityAjax(variantId, newQuantity);
}

/**
 * Update quantity button states
 */
function updateQuantityButtons(variantId, quantity, maxQuantity) {
    const cartItem = document.querySelector(`.cart-item[data-variant-id="${variantId}"]`);
    if (!cartItem) return;
    
    const decreaseBtn = cartItem.querySelector('.qty-decrease');
    const increaseBtn = cartItem.querySelector('.qty-increase');
    
    // Update decrease button
    if (decreaseBtn) {
        decreaseBtn.disabled = quantity <= 1;
    }
    
    // Update increase button
    if (increaseBtn) {
        increaseBtn.disabled = quantity >= maxQuantity;
    }
}

/**
 * AJAX request to update quantity
 */
function updateQuantityAjax(variantId, quantity) {
    const csrfToken = document.getElementById('csrfToken').value;
    
    // Show loading state
    const cartItem = document.querySelector(`.cart-item[data-variant-id="${variantId}"]`);
    if (cartItem) {
        cartItem.classList.add('updating');
    }
    
    // FIX: Use getUrl()
    fetch(getUrl('/cart/update-quantity'), {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
            'X-Requested-With': 'XMLHttpRequest'
        },
        body: new URLSearchParams({
            csrf_token: csrfToken,
            variant_id: variantId,
            quantity: quantity
        })
    })
    .then(response => {
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        return response.json();
    })
    .then(data => {
        if (data.success) {
            // Update item total with animation
            const totalElement = document.querySelector(`.total-value[data-variant-id="${variantId}"]`);
            if (totalElement) {
                // Use raw value from server
                animateValueChange(totalElement, data.itemTotal);
            }
            
            // Update cart summary using precise server data
            updateCartSummary({
                subtotal: data.cartSubtotal,
                shipping: data.shippingFee,
                total: data.grandTotal
            });
            
            // Update header cart count
            updateHeaderCartCount(data.cartCount);
            
            showToast('Cart updated successfully', 'success');
        } else {
            showToast(data.message || 'Failed to update quantity', 'error');
            // Reload page to sync quantities
            setTimeout(() => location.reload(), 1500);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showToast('Unable to update cart. Please try again.', 'error');
    })
    .finally(() => {
        if (cartItem) {
            cartItem.classList.remove('updating');
        }
    });
}

/**
 * Animate value change for better UX
 */
function animateValueChange(element, newValue) {
    element.style.transform = 'scale(1.2)';
    element.style.color = 'var(--color-primary)';
    
    setTimeout(() => {
        element.textContent = '₱' + formatCurrency(newValue);
        element.style.transform = 'scale(1)';
    }, 150);
    
    setTimeout(() => {
        element.style.color = '';
    }, 300);
}

/**
 * Remove item from cart
 */
function removeItem(variantId, productName) {
    // Create custom confirm dialog
    const confirmRemove = confirm(`Remove "${productName}" from your shopping cart?`);
    
    if (!confirmRemove) {
        return;
    }
    
    const csrfToken = document.getElementById('csrfToken').value;
    
    // Show loading state
    const cartItem = document.querySelector(`.cart-item[data-variant-id="${variantId}"]`);
    if (cartItem) {
        cartItem.classList.add('removing');
    }
    
    // FIX: Use getUrl()
    fetch(getUrl('/cart/remove'), {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
            'X-Requested-With': 'XMLHttpRequest'
        },
        body: new URLSearchParams({
            csrf_token: csrfToken,
            variant_id: variantId
        })
    })
    .then(response => {
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        return response.json();
    })
    .then(data => {
        if (data.success) {
            // Remove item from DOM with smooth animation
            if (cartItem) {
                setTimeout(() => {
                    cartItem.remove();
                    
                    // Check if cart is now empty
                    const remainingItems = document.querySelectorAll('.cart-item');
                    if (remainingItems.length === 0) {
                        location.reload(); // Show empty cart state
                    } else {
                        // Update summary with server data
                        updateCartSummary({
                            subtotal: data.cartSubtotal,
                            shipping: data.shippingFee,
                            total: data.grandTotal
                        });
                        updateHeaderCartCount(data.cartCount);
                    }
                }, 300);
            }
            
            showToast(`${productName} removed from cart`, 'success');
        } else {
            showToast(data.message || 'Failed to remove item', 'error');
            if (cartItem) {
                cartItem.classList.remove('removing');
            }
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showToast('Unable to remove item. Please try again.', 'error');
        if (cartItem) {
            cartItem.classList.remove('removing');
        }
    });
}

/**
 * Clear entire cart
 */
function confirmClearCart() {
    const itemCount = document.querySelectorAll('.cart-item').length;
    
    if (!confirm(`Are you sure you want to remove all ${itemCount} items from your cart?`)) {
        return;
    }
    
    // Show loading state on all items
    document.querySelectorAll('.cart-item').forEach(item => {
        item.classList.add('removing');
    });
    
    showToast('Clearing cart...', 'info');
    
    // Small delay for visual feedback
    setTimeout(() => {
        document.getElementById('clearCartForm').submit();
    }, 500);
}

/**
 * Update cart summary totals
 * Now accepts optional serverData to prevent calculation errors
 */
function updateCartSummary(serverData = null) {
    // 1. Prefer Server Data (Accurate)
    if (serverData) {
        updateElementValue('subtotalAmount', serverData.subtotal);
        updateElementValue('shippingFee', serverData.shipping);
        updateElementValue('totalAmount', serverData.total);
        updateShippingProgress(serverData.subtotal, 5000);
        return;
    }

    // 2. Fallback: Manual Calculation (Preserved from original)
    let subtotal = 0;
    let itemCount = 0;
    
    // Calculate new subtotal
    document.querySelectorAll('.cart-item').forEach(item => {
        const variantId = item.dataset.variantId;
        const quantityInput = item.querySelector(`input[data-variant-id="${variantId}"]`);
        
        // Use total value instead of price * quantity to avoid scraping formatted prices
        const totalText = item.querySelector(`.total-value[data-variant-id="${variantId}"]`).textContent;
        
        if (quantityInput && totalText) {
            const quantity = parseInt(quantityInput.value);
            const itemTotal = parseFloat(totalText.replace(/[^\d.-]/g, ''));
            
            if (!isNaN(itemTotal)) {
                subtotal += itemTotal;
            }
            itemCount += quantity;
        }
    });
    
    // Calculate shipping (example: flat rate or free shipping threshold)
    const freeShippingThreshold = 5000;
    let shippingFee = subtotal >= freeShippingThreshold ? 0 : 50.00;
    if (subtotal === 0) shippingFee = 0;
    
    const total = subtotal + shippingFee;
    
    // Update DOM with animation
    updateElementValue('subtotalAmount', subtotal);
    updateElementValue('shippingFee', shippingFee);
    updateElementValue('totalAmount', total);
    
    // Update item count in header
    const itemCountElement = document.querySelector('.item-count');
    if (itemCountElement) {
        itemCountElement.textContent = itemCount + (itemCount === 1 ? ' item' : ' items');
    }
    
    // Update free shipping progress
    updateShippingProgress(subtotal, freeShippingThreshold);
}

/**
 * Update element value with animation and "Free" text support
 */
function updateElementValue(elementId, value) {
    const element = document.getElementById(elementId);
    if (element) {
        const numValue = parseFloat(value);
        if (isNaN(numValue)) return;
        
        let formattedValue;
        
        // Handle "Free" text for shipping fee
        if (elementId === 'shippingFee' && numValue === 0) {
            formattedValue = 'Free';
            element.classList.add('text-success'); // Optional: add green color class
        } else {
            formattedValue = '₱' + formatCurrency(numValue);
            element.classList.remove('text-success');
        }

        if (element.textContent.trim() !== formattedValue) {
            // Special animation for "Free"
            if (formattedValue === 'Free') {
                 element.textContent = formattedValue;
                 element.style.transform = 'scale(1.2)';
                 element.style.color = '#28a745'; // Green
                 setTimeout(() => {
                     element.style.transform = 'scale(1)';
                     element.style.color = ''; 
                 }, 300);
            } else {
                animateValueChange(element, numValue);
            }
        }
    }
}

/**
 * Update shipping progress bar
 * FIXED: Toggles between Progress Container and Success Container
 */
function updateShippingProgress(subtotal, threshold) {
    const progressContainer = document.getElementById('shippingProgressContainer');
    const noticeContainer = document.getElementById('freeShippingContainer');
    const progressFill = document.querySelector('.progress-fill');
    const remainingSpan = document.getElementById('remainingAmount');
    
    subtotal = parseFloat(subtotal);

    // Toggle based on threshold
    if (subtotal >= threshold) {
        // CASE: Free Shipping Qualified
        if (progressContainer) progressContainer.style.display = 'none';
        if (noticeContainer) {
            noticeContainer.style.display = 'block';
            noticeContainer.style.animation = 'fadeIn 0.5s';
        }
    } else {
        // CASE: Still needs more items
        if (noticeContainer) noticeContainer.style.display = 'none';
        if (progressContainer) {
            progressContainer.style.display = 'block';
            
            // Update the bar width
            if (progressFill) {
                const percentage = Math.min((subtotal / threshold) * 100, 100);
                progressFill.style.width = percentage + '%';
            }
            
            // Update the remaining amount text
            if (remainingSpan) {
                const remaining = threshold - subtotal;
                remainingSpan.textContent = '₱' + formatCurrency(remaining);
            }
        }
    }
}

/**
 * Format currency with thousand separators
 */
function formatCurrency(value) {
    return parseFloat(value).toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ',');
}

/**
 * Update header cart count badge
 */
function updateHeaderCartCount(count) {
    const cartBadge = document.querySelector('.icon-btn[title="Shopping Cart"] .badge');
    
    if (count > 0) {
        if (cartBadge) {
            cartBadge.textContent = count > 99 ? '99+' : count;
            // Animate badge update
            cartBadge.style.transform = 'scale(1.3)';
            setTimeout(() => {
                cartBadge.style.transform = 'scale(1)';
            }, 200);
        } else {
            // Create badge if it doesn't exist
            const cartButton = document.querySelector('.icon-btn[title="Shopping Cart"]');
            if (cartButton) {
                const badge = document.createElement('span');
                badge.className = 'badge';
                badge.textContent = count > 99 ? '99+' : count;
                cartButton.appendChild(badge);
            }
        }
    } else {
        // Remove badge if count is 0
        if (cartBadge) {
            cartBadge.style.transform = 'scale(0)';
            setTimeout(() => cartBadge.remove(), 200);
        }
    }
}

/**
 * Proceed to checkout
 */
function proceedToCheckout() {
    // Check if there are any unavailable items
    const unavailableItems = document.querySelectorAll('.cart-item.item-unavailable');
    
    if (unavailableItems.length > 0) {
        showToast('Please remove out-of-stock items before checkout', 'warning');
        
        // Highlight unavailable items
        unavailableItems.forEach(item => {
            item.style.animation = 'shake 0.5s';
            setTimeout(() => {
                item.style.animation = '';
            }, 500);
        });
        
        return;
    }
    
    // Show redirect message
    showToast('Redirecting to secure checkout...', 'info');
    
    // Redirect to the actual checkout route
    // FIX: Use getUrl()
    setTimeout(() => {
        window.location.href = getUrl('/checkout');
    }, 800);
}

/**
 * Show toast notification with improved styling
 */
function showToast(message, type = 'info') {
    // Remove existing toast if any
    const existingToast = document.querySelector('.toast-notification');
    if (existingToast) {
        existingToast.classList.remove('show');
        setTimeout(() => existingToast.remove(), 300);
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

/**
 * Add shake animation for error states
 */
const style = document.createElement('style');
style.textContent = `
    @keyframes shake {
        0%, 100% { transform: translateX(0); }
        10%, 30%, 50%, 70%, 90% { transform: translateX(-5px); }
        20%, 40%, 60%, 80% { transform: translateX(5px); }
    }
`;
document.head.appendChild(style);

/**
 * Initialize cart page
 */
document.addEventListener('DOMContentLoaded', function() {
    // Update button states for all quantity inputs
    document.querySelectorAll('.qty-input').forEach(input => {
        const variantId = input.dataset.variantId;
        const quantity = parseInt(input.value);
        const maxQuantity = parseInt(input.max);
        
        updateQuantityButtons(variantId, quantity, maxQuantity);
    });
    
    // Add keyboard support for quantity inputs
    document.querySelectorAll('.qty-input').forEach(input => {
        input.addEventListener('keydown', function(e) {
            if (e.key === 'ArrowUp') {
                e.preventDefault();
                const variantId = this.dataset.variantId;
                updateQuantity(variantId, 1);
            } else if (e.key === 'ArrowDown') {
                e.preventDefault();
                const variantId = this.dataset.variantId;
                updateQuantity(variantId, -1);
            }
        });
    });
    
    // Add loading indicator styles
    const loadingStyles = document.createElement('style');
    loadingStyles.textContent = `
        .cart-item.updating::after {
            content: '';
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            width: 40px;
            height: 40px;
            border: 4px solid var(--color-light-gray);
            border-top-color: var(--color-primary);
            border-radius: 50%;
            animation: spin 0.8s linear infinite;
        }
    `;
    document.head.appendChild(loadingStyles);
    
    console.log('✨ Lumora Cart initialized successfully');
});