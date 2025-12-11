        // Change main image when thumbnail clicked
        function changeMainImage(src) {
            document.getElementById('mainProductImage').src = src;
            
            // Update active thumbnail
            document.querySelectorAll('.thumbnail-image').forEach(thumb => {
                thumb.classList.remove('active');
            });
            event.target.classList.add('active');
        }

        // Quantity controls
        function increaseQuantity() {
            const qtyInput = document.getElementById('quantityInput');
            const maxStock = getSelectedVariantStock();
            
            if (parseInt(qtyInput.value) < maxStock) {
                qtyInput.value = parseInt(qtyInput.value) + 1;
            }
        }

        function decreaseQuantity() {
            const qtyInput = document.getElementById('quantityInput');
            if (parseInt(qtyInput.value) > 1) {
                qtyInput.value = parseInt(qtyInput.value) - 1;
            }
        }

        // Get selected variant stock
        function getSelectedVariantStock() {
            const selectedVariant = document.querySelector('input[name="variant"]:checked');
            return selectedVariant ? parseInt(selectedVariant.dataset.stock) : 99;
        }

        // Update stock info when variant changes
        document.querySelectorAll('input[name="variant"]').forEach(radio => {
            radio.addEventListener('change', function() {
                const stock = parseInt(this.dataset.stock);
                const stockInfo = document.getElementById('stockInfo');
                
                if (stock === 0) {
                    stockInfo.textContent = 'Out of Stock';
                    stockInfo.className = 'stock-availability out-of-stock';
                } else if (stock <= 5) {
                    stockInfo.textContent = `Only ${stock} left in stock`;
                    stockInfo.className = 'stock-availability low-stock';
                } else {
                    stockInfo.textContent = 'In Stock';
                    stockInfo.className = 'stock-availability in-stock';
                }

                // Update quantity max
                const qtyInput = document.getElementById('quantityInput');
                qtyInput.max = stock;
                if (parseInt(qtyInput.value) > stock) {
                    qtyInput.value = stock;
                }
            });
        });

        // Add to cart with AJAX
        function addToCart() {
            const selectedVariant = document.querySelector('input[name="variant"]:checked');
            const quantity = document.getElementById('quantityInput').value;
            const csrfToken = document.getElementById('csrfToken').value;

            if (!selectedVariant) {
                showToast('Please select a variant', 'error');
                return;
            }

            // Show loading state
            const btn = document.getElementById('addToCartBtn');
            const originalText = btn.innerHTML;
            btn.disabled = true;
            btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Adding...';

            // Send AJAX request
            fetch('/cart/add', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: new URLSearchParams({
                    csrf_token: csrfToken,
                    variant_id: selectedVariant.value,
                    quantity: quantity
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showToast(data.message, 'success');
                    
                    // Update cart count in header
                    updateHeaderCartCount(data.cartCount);
                    
                    // Reset quantity to 1
                    document.getElementById('quantityInput').value = 1;
                } else {
                    showToast(data.message, 'error');
                    
                    // If login required, redirect
                    if (data.redirect) {
                        setTimeout(() => {
                            window.location.href = data.redirect;
                        }, 1500);
                    }
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showToast('Failed to add to cart. Please try again.', 'error');
            })
            .finally(() => {
                // Restore button state
                btn.disabled = false;
                btn.innerHTML = originalText;
            });
        }

        // Update header cart count
        function updateHeaderCartCount(count) {
            const cartBadge = document.querySelector('.icon-btn[title="Shopping Cart"] .badge');
            
            if (count > 0) {
                if (cartBadge) {
                    cartBadge.textContent = count > 99 ? '99+' : count;
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
            }
        }

        // Show toast notification
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

        // Buy now
        function buyNow() {
            const selectedVariant = document.querySelector('input[name="variant"]:checked');
            const quantity = document.getElementById('quantityInput').value;
            const csrfToken = document.getElementById('csrfToken') ? document.getElementById('csrfToken').value : '';

            if (!selectedVariant) {
                showToast('Please select a variant', 'error');
                return;
            }
            
            if (!csrfToken) {
                 console.error('CSRF Token not found');
                 return;
            }

            // Show loading on the specific Buy Now button
            const btn = document.querySelector('.btn-buy-now');
            let originalText = '';
            if(btn) {
                originalText = btn.innerHTML;
                btn.disabled = true;
                btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Processing...';
            }

            fetch('/cart/add', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: new URLSearchParams({
                    csrf_token: csrfToken,
                    variant_id: selectedVariant.value,
                    quantity: quantity
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Redirect to checkout
                    window.location.href = '/checkout';
                } else {
                    showToast(data.message, 'error');
                    if (data.redirect) {
                        window.location.href = data.redirect;
                    } else if (btn) {
                        btn.disabled = false;
                        btn.innerHTML = originalText;
                    }
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showToast('An error occurred', 'error');
                if (btn) {
                    btn.disabled = false;
                    btn.innerHTML = originalText;
                }
            });
        }