/* =========================================
   COLLECTIONS PAGE - JavaScript Module
   
   Features:
   - Category filtering with AJAX
   - Dynamic product rendering
   - Loading states
   - Error handling
========================================= */

(function() {
  'use strict';

  // Only run on collections page
  const collectionsContainer = document.querySelector('.collections-container');
  if (!collectionsContainer) return;

  // ========== CATEGORY FILTER ==========
  const categoryButtons = document.querySelectorAll('.category-btn');
  const productsContainer = document.getElementById('productsContainer');

  if (categoryButtons.length > 0 && productsContainer) {
    categoryButtons.forEach(button => {
      button.addEventListener('click', async function() {
        // Update active state
        categoryButtons.forEach(btn => btn.classList.remove('active'));
        this.classList.add('active');

        const categoryId = this.getAttribute('data-category');

        // Show loading state
        productsContainer.innerHTML = `
          <div class="loading-container">
            <div class="loading-spinner"></div>
            <p class="loading-text">Loading products...</p>
          </div>
        `;

        try {
          // Fetch products for selected category
          const response = await fetch(`/collections/getByCategory?category_id=${categoryId}`);
          const data = await response.json();

          if (data.products && data.products.length > 0) {
            renderProducts(data.products);
          } else {
            productsContainer.innerHTML = `
              <div class="empty-state">
                <div class="empty-icon">📦</div>
                <h3 class="empty-title">No Products Found</h3>
                <p class="empty-message">No products available in this category yet</p>
              </div>
            `;
          }
        } catch (error) {
          console.error('Error fetching products:', error);
          productsContainer.innerHTML = `
            <div class="empty-state">
              <div class="empty-icon">⚠️</div>
              <h3 class="empty-title">Error Loading Products</h3>
              <p class="empty-message">Please try again later</p>
            </div>
          `;
        }
      });
    });
  }

  // ========== RENDER PRODUCTS ==========
  function renderProducts(products) {
    const productsHTML = products.map(product => `
      <div class="product-card" onclick="viewProduct('${escapeHtml(product.slug)}')">
        <div class="product-image-container">
          ${product.cover_picture 
            ? `<img src="/${escapeHtml(product.cover_picture)}" 
                   alt="${escapeHtml(product.name)}" 
                   class="product-image"
                   onerror="this.src='/assets/images/placeholder-product.jpg'">`
            : `<img src="/assets/images/placeholder-product.jpg" 
                   alt="${escapeHtml(product.name)}" 
                   class="product-image">`
          }
          <div class="product-badge">New</div>
        </div>
        <div class="product-info">
          <div class="product-shop">
            ${escapeHtml(product.shop_name)}
          </div>
          <h3 class="product-name">
            ${escapeHtml(product.name)}
          </h3>
          ${product.short_description 
            ? `<p class="product-description">${escapeHtml(product.short_description)}</p>` 
            : ''
          }
          <div class="product-footer">
            <div class="product-price">
              ₱${parseFloat(product.price).toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2})}
            </div>
            <button class="view-btn">View</button>
          </div>
        </div>
      </div>
    `).join('');

    productsContainer.innerHTML = `<div class="products-grid">${productsHTML}</div>`;
  }

  // ========== NAVIGATION ==========
  window.viewProduct = function(slug) {
    window.location.href = `/product/${slug}`;
  };

  // ========== UTILITY FUNCTIONS ==========
  function escapeHtml(text) {
    if (!text) return '';
    const map = {
      '&': '&amp;',
      '<': '&lt;',
      '>': '&gt;',
      '"': '&quot;',
      "'": '&#039;'
    };
    return String(text).replace(/[&<>"']/g, m => map[m]);
  }

  // ========== INITIALIZATION LOG ==========
  console.log('✓ Collections module loaded');

})();