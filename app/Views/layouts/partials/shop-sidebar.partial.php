<aside class="shop-sidebar">
    <div class="sidebar-header">
        <h2><i class="fas fa-store"></i> Seller Panel</h2>
    </div>
    
    <nav class="sidebar-nav">
        <!-- Dashboard -->
        <a href="/shop/dashboard" class="sidebar-link <?= ($currentPage ?? '') === 'dashboard' ? 'active' : '' ?>">
            <i class="fas fa-th-large"></i>
            <span>Dashboard</span>
        </a>
        
        <!-- Products -->
        <div class="sidebar-dropdown">
            <button class="sidebar-link dropdown-toggle <?= in_array($currentPage ?? '', ['products', 'add-product']) ? 'active' : '' ?>" 
                    onclick="toggleDropdown(this)">
                <i class="fas fa-box"></i>
                <span>Products</span>
                <i class="fas fa-chevron-down dropdown-icon"></i>
            </button>
            <div class="dropdown-content">
                <a href="/shop/products" class="dropdown-item <?= ($currentPage ?? '') === 'products' ? 'active' : '' ?>">
                    <i class="fas fa-list"></i>
                    My Products
                </a>
                <a href="/shop/add-product" class="dropdown-item <?= ($currentPage ?? '') === 'add-product' ? 'active' : '' ?>">
                    <i class="fas fa-plus-circle"></i>
                    Add New Product
                </a>
            </div>
        </div>
        
        <!-- Orders -->
        <div class="sidebar-dropdown">
            <button class="sidebar-link dropdown-toggle <?= in_array($currentPage ?? '', ['orders', 'cancellations']) ? 'active' : '' ?>" 
                    onclick="toggleDropdown(this)">
                <i class="fas fa-shopping-cart"></i>
                <span>Orders</span>
                <i class="fas fa-chevron-down dropdown-icon"></i>
            </button>
            <div class="dropdown-content">
                <a href="/shop/orders" class="dropdown-item <?= ($currentPage ?? '') === 'orders' ? 'active' : '' ?>">
                    <i class="fas fa-receipt"></i>
                    My Orders
                </a>
                <a href="/shop/cancellations" class="dropdown-item <?= ($currentPage ?? '') === 'cancellations' ? 'active' : '' ?>">
                    <i class="fas fa-times-circle"></i>
                    Cancellations
                </a>
            </div>
        </div>
        
        <!-- Addresses -->
        <a href="/shop/addresses" class="sidebar-link <?= ($currentPage ?? '') === 'addresses' ? 'active' : '' ?>">
            <i class="fas fa-map-marker-alt"></i>
            <span>Addresses</span>
        </a>
    </nav>
    
    <script>
        function toggleDropdown(button) {
            const dropdown = button.parentElement;
            const content = dropdown.querySelector('.dropdown-content');
            const icon = button.querySelector('.dropdown-icon');
            
            // Toggle active state
            dropdown.classList.toggle('open');
            
            // Rotate icon
            if (dropdown.classList.contains('open')) {
                icon.style.transform = 'rotate(180deg)';
            } else {
                icon.style.transform = 'rotate(0deg)';
            }
        }
        
        // Auto-open dropdown if child is active
        document.addEventListener('DOMContentLoaded', function() {
            const activeDropdownItems = document.querySelectorAll('.dropdown-item.active');
            activeDropdownItems.forEach(item => {
                const dropdown = item.closest('.sidebar-dropdown');
                if (dropdown) {
                    dropdown.classList.add('open');
                    const icon = dropdown.querySelector('.dropdown-icon');
                    if (icon) {
                        icon.style.transform = 'rotate(180deg)';
                    }
                }
            });
        });
    </script>
</aside>