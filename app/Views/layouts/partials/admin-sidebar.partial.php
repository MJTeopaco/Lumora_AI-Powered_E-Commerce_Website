<?php
// app/Views/layouts/partials/admin-sidebar.partial.php

// Determine current page for active state
$currentPath = $_SERVER['REQUEST_URI'] ?? '';
$currentPage = basename(parse_url($currentPath, PHP_URL_PATH));
?>
<div class="sidebar-header">
    <h2><i class="fas fa-database"></i> Lumora</h2>
    <p>Admin Panel</p>
</div>

<ul class="sidebar-menu">
    <li>
        <a href="/admin/dashboard" class="<?= strpos($currentPath, '/admin/dashboard') !== false ? 'active' : '' ?>">
            <i class="fas fa-home"></i>
            <span>Dashboard</span>
        </a>
    </li>
    <li>
        <a href="/admin/users" class="<?= strpos($currentPath, '/admin/users') !== false ? 'active' : '' ?>">
            <i class="fas fa-users"></i>
            <span>Users</span>
        </a>
    </li>
    <li>
        <a href="/admin/sellers" class="<?= strpos($currentPath, '/admin/sellers') !== false ? 'active' : '' ?>">
            <i class="fas fa-user-tag"></i>
            <span>Sellers</span>
        </a>
    </li>
    <li>
        <a href="/admin/settings" class="<?= strpos($currentPath, '/admin/settings') !== false ? 'active' : '' ?>">
            <i class="fas fa-cog"></i>
            <span>Configure Settings</span>
        </a>
    </li>
    <li>
        <a href="/admin/reports" class="<?= strpos($currentPath, '/admin/reports') !== false ? 'active' : '' ?>">
            <i class="fas fa-chart-bar"></i>
            <span>Reports</span>
        </a>
    </li>
    <li>
        <a href="/logout">
            <i class="fas fa-sign-out-alt"></i>
            <span>Logout</span>
        </a>
    </li>
</ul>