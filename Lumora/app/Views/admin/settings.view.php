<?php
// app/Views/admin/settings.view.php
?>

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
                    <td><strong><?= htmlspecialchars($category['name']) ?></strong></td>
                    <td><code><?= htmlspecialchars($category['slug']) ?></code></td>
                    <td><?= $category['product_count'] ?></td>
                    <td><?= date('M d, Y', strtotime($category['created_at'])) ?></td>
                    <td>
                        <button class="btn btn-warning btn-sm"
                            onclick='openEditModal(<?= json_encode($category) ?>)'>
                            <i class="fas fa-edit"></i>
                        </button>
                        <button class="btn btn-danger btn-sm"
                            onclick="deleteCategory(<?= $category['category_id'] ?>, '<?= htmlspecialchars($category['name']) ?>')">
                            <i class="fas fa-trash"></i>
                        </button>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
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
                <small>Enter the name of the category</small>
            </div>
            <div style="display:flex; justify-content:flex-end; gap:10px; margin-top:20px;">
                <button type="button" class="btn btn-secondary" onclick="closeAddModal()">Cancel</button>
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
                <button type="button" class="btn btn-secondary" onclick="closeEditModal()">Cancel</button>
                <button type="submit" class="btn btn-success"><i class="fas fa-save"></i> Update Category</button>
            </div>
        </form>
    </div>
</div>

<script src="/js/admin-settings.js"></script>