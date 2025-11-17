<?php
namespace App\Views\Layouts\Admin;

$admin_name = $_SESSION['username'] ?? 'Admin';
$admin_role = $_SESSION['role'] ?? 'Administrator';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Configure Settings - Lumora DB</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">

    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #f5f7fa;
            color: #333;
        }

        .container { display: flex; min-height: 100vh; }

        /* Sidebar */
        .sidebar {
            width: 260px;
            background: #1e4d3d;
            color: #ecf0f1;
            padding: 20px 0;
            position: fixed;
            height: 100vh;
            overflow-y: auto;
        }

        .sidebar-header { padding: 0 20px 20px; border-bottom: 1px solid #34495e; }
        .sidebar-header h2 { color: #fff; font-size: 24px; margin-bottom: 5px; }
        .sidebar-header p { font-size: 12px; color: #95a5a6; }

        .sidebar-menu { list-style: none; padding: 20px 0; }
        .sidebar-menu li { margin: 5px 0; }

        .sidebar-menu a {
            display: flex; align-items: center;
            padding: 12px 20px;
            color: #ecf0f1;
            text-decoration: none;
            transition: all .3s;
        }
        .sidebar-menu a:hover,
        .sidebar-menu a.active {
            background: #34495e;
            border-left: 4px solid #3498db;
            padding-left: 16px;
        }
        .sidebar-menu i { width: 25px; margin-right: 10px; }

        /* Main Content */
        .main-content { flex: 1; margin-left: 260px; padding: 20px; }

        .header {
            background: white; padding: 20px 30px;
            border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            display: flex; justify-content: space-between;
            align-items: center; margin-bottom: 30px;
        }
        .header h1 { font-size: 28px; color: #2c3e50; }

        .user-info { display: flex; align-items: center; gap: 15px; }

        .user-avatar {
            width: 45px; height: 45px; border-radius: 50%;
            background: #3498db; color: white;
            display: flex; align-items: center; justify-content: center;
            font-weight: bold; font-size: 18px;
        }

        /* Alerts */
        .alert {
            padding: 15px 20px;
            border-radius: 8px;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .alert.success { background: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
        .alert.error { background: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }

        /* Content Card */
        .content-card {
            background: white; padding: 30px;
            border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            margin-bottom: 30px;
        }

        .content-card h2 {
            font-size: 20px; color: #2c3e50;
            margin-bottom: 20px; padding-bottom: 15px;
            border-bottom: 2px solid #ecf0f1;
            display: flex; justify-content: space-between;
            align-items: center;
        }

        /* Buttons */
        .btn {
            padding: 12px 24px; border: none;
            border-radius: 8px; font-size: 14px;
            font-weight: 600; cursor: pointer;
            transition: all .3s; display: inline-flex;
            align-items: center; gap: 8px;
        }
        .btn-primary { background: #3498db; color: white; }
        .btn-primary:hover { background: #2980b9; transform: translateY(-2px); }
        .btn-success { background: #27ae60; color: white; }
        .btn-warning { background: #f39c12; color: white; }
        .btn-danger { background: #e74c3c; color: white; }
        .btn-sm { padding: 8px 16px; font-size: 12px; }

        /* Table */
        .data-table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        .data-table thead { background: #f8f9fa; }
        .data-table th {
            text-align: left; padding: 15px 12px;
            font-weight: 600; color: #2c3e50;
            border-bottom: 2px solid #ecf0f1;
        }
        .data-table td {
            padding: 15px 12px;
            border-bottom: 1px solid #ecf0f1;
        }
        .data-table tr:hover { background: #f8f9fa; }

        /* Modal */
        .modal {
            display: none; position: fixed;
            z-index: 1000; top: 0; left: 0;
            width: 100%; height: 100%;
            background: rgba(0,0,0,0.5);
            animation: fadeIn .3s;
        }
        .modal.active { display: flex; align-items: center; justify-content: center; }
        .modal-content {
            background: white; padding: 30px;
            border-radius: 10px; max-width: 500px;
            width: 90%; max-height: 90vh;
            overflow-y: auto; animation: slideUp .3s;
        }

        .modal-header {
            display: flex; justify-content: space-between;
            padding-bottom: 15px; margin-bottom: 20px;
            border-bottom: 2px solid #ecf0f1;
        }

        @keyframes fadeIn { from { opacity: 0; } to { opacity: 1; } }
        @keyframes slideUp { from { transform: translateY(50px); opacity: 0; } to { transform: translateY(0); opacity: 1; } }
    </style>
</head>

<body>

<div class="container">

    <!-- Sidebar -->
    <aside class="sidebar">
        <div class="sidebar-header">
            <h2><i class="fas fa-database"></i> Lumora</h2>
            <p>Admin Panel</p>
        </div>
        <ul class="sidebar-menu">
            <li><a href="/admin/dashboard"><i class="fas fa-home"></i><span>Dashboard</span></a></li>
            <li><a href="/admin/users"><i class="fas fa-users"></i><span>Users</span></a></li>
            <li><a href="/admin/sellers"><i class="fas fa-user-tag"></i><span>Sellers</span></a></li>
            <li><a href="/admin/settings" class="active"><i class="fas fa-cog"></i><span>Configure Settings</span></a></li>
            <li><a href="/admin/reports"><i class="fas fa-chart-bar"></i><span>Reports</span></a></li>
            <li><a href="/logout"><i class="fas fa-sign-out-alt"></i><span>Logout</span></a></li>
        </ul>
    </aside>

    <!-- Main Content -->
    <main class="main-content">

        <!-- Header -->
        <div class="header">
            <h1>Configure Settings</h1>
            <div class="user-info">
                <div class="user-avatar"><?= strtoupper(substr($admin_name, 0, 1)); ?></div>
                <div>
                    <h3><?= htmlspecialchars($admin_name); ?></h3>
                    <p><?= htmlspecialchars($admin_role); ?></p>
                </div>
            </div>
        </div>

        <!-- Alerts -->
        <?php if (isset($_SESSION['success'])): ?>
            <div class="alert success">
                <i class="fas fa-check-circle"></i>
                <span><?= htmlspecialchars($_SESSION['success']); unset($_SESSION['success']); ?></span>
            </div>
        <?php endif; ?>

        <?php if (isset($_SESSION['error'])): ?>
            <div class="alert error">
                <i class="fas fa-exclamation-circle"></i>
                <span><?= htmlspecialchars($_SESSION['error']); unset($_SESSION['error']); ?></span>
            </div>
        <?php endif; ?>

        <!-- Category Management -->
        <div class="content-card">
            <h2>
                <span><i class="fas fa-tags"></i> Product Categories</span>
                <button class="btn btn-primary" onclick="openAddModal()">
                    <i class="fas fa-plus"></i> Add New Category
                </button>
            </h2>

            <?php if (empty($categories)): ?>
                <div class="empty-state">
                    <i class="fas fa-tags"></i>
                    <p>No categories found. Add your first category to get started!</p>
                </div>
            <?php else: ?>

                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Category Name</th>
                            <th>Slug</th>
                            <th>Products</th>
                            <th>Created</th>
                            <th>Actions</th>
                        </tr>
                    </thead>

                    <tbody>
                    <?php foreach ($categories as $category): ?>
                        <tr>
                            <td><strong><?= htmlspecialchars($category['name']); ?></strong></td>
                            <td><code><?= htmlspecialchars($category['slug']); ?></code></td>
                            <td><?= $category['product_count']; ?></td>
                            <td><?= date('M d, Y', strtotime($category['created_at'])); ?></td>

                            <td>
                                <button class="btn btn-warning btn-sm"
                                    onclick='openEditModal(<?= json_encode($category); ?>)'>
                                    <i class="fas fa-edit"></i>
                                </button>

                                <button class="btn btn-danger btn-sm"
                                    onclick="deleteCategory(<?= $category['category_id']; ?>, '<?= htmlspecialchars($category['name']); ?>')">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>

                </table>

            <?php endif; ?>

        </div>
    </main>
</div>

<!-- Add Modal -->
<div id="addModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h3><i class="fas fa-plus-circle"></i> Add New Category</h3>
            <button class="close-modal" onclick="closeAddModal()">&times;</button>
        </div>

        <form method="POST" action="/admin/add-category">
            <div class="form-group">
                <label for="add_category_name">Category Name *</label>
                <input type="text" id="add_category_name" name="category_name" required>
                    </br>
                <small>Enter the name of the category</small>
            </div>

            <div style="display:flex; justify-content:flex-end; gap:10px; margin-top:20px;">
                <button type="button" class="btn" style="background:#95a5a6; color:white;" onclick="closeAddModal()">Cancel</button>
                <button type="submit" class="btn btn-success"><i class="fas fa-save"></i> Add Category</button>
            </div>
        </form>
    </div>
</div>

<!-- Edit Modal -->
<div id="editModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h3><i class="fas fa-edit"></i> Edit Category</h3>
            <button class="close-modal" onclick="closeEditModal()">&times;</button>
        </div>

        <form method="POST" action="/admin/update-category">
            <input type="hidden" id="edit_category_id" name="category_id">

            <div class="form-group">
                <label for="edit_category_name">Category Name *</label>
                <input type="text" id="edit_category_name" name="category_name" required>
            </div>

            <div style="display:flex; justify-content:flex-end; gap:10px; margin-top:20px;">
                <button type="button" class="btn" style="background:#95a5a6; color:white;" onclick="closeEditModal()">Cancel</button>
                <button type="submit" class="btn btn-success"><i class="fas fa-save"></i> Update Category</button>
            </div>
        </form>
    </div>
</div>

<script>
/* Modal Controls */
function openAddModal() {
    document.getElementById('addModal').classList.add('active');
}
function closeAddModal() {
    document.getElementById('addModal').classList.remove('active');
    document.getElementById('add_category_name').value = '';
}

function openEditModal(category) {
    document.getElementById('edit_category_id').value = category.category_id;
    document.getElementById('edit_category_name').value = category.name;
    document.getElementById('editModal').classList.add('active');
}
function closeEditModal() {
    document.getElementById('editModal').classList.remove('active');
}

/* Delete Category */
function deleteCategory(id, name) {
    if (confirm(`Are you sure you want to delete "${name}"?\n\nNote: Categories assigned to products cannot be deleted.`)) {
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = '/admin/delete-category';

        const input = document.createElement('input');
        input.type = 'hidden';
        input.name = 'category_id';
        input.value = id;

        form.appendChild(input);
        document.body.appendChild(form);
        form.submit();
    }
}

/* Close modals on background click */
window.onclick = function(event) {
    if (event.target === document.getElementById('addModal')) closeAddModal();
    if (event.target === document.getElementById('editModal')) closeEditModal();
}
</script>

</body>
</html>
