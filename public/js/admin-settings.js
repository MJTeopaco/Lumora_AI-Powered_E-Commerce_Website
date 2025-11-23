/**
 * Admin Settings JavaScript
 * app/public/js/admin-settings.js
 */

// Modal Controls
function openAddModal() {
    document.getElementById('addModal').classList.add('active');
    document.getElementById('add_category_name').focus();
}

function closeAddModal() {
    document.getElementById('addModal').classList.remove('active');
    document.getElementById('add_category_name').value = '';
}

function openEditModal(category) {
    document.getElementById('edit_category_id').value = category.category_id;
    document.getElementById('edit_category_name').value = category.name;
    document.getElementById('editModal').classList.add('active');
    document.getElementById('edit_category_name').focus();
}

function closeEditModal() {
    document.getElementById('editModal').classList.remove('active');
    document.getElementById('edit_category_id').value = '';
    document.getElementById('edit_category_name').value = '';
}

// Delete Category
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

// Close modals on background click
window.onclick = function(event) {
    const addModal = document.getElementById('addModal');
    const editModal = document.getElementById('editModal');
    
    if (event.target === addModal) {
        closeAddModal();
    }
    if (event.target === editModal) {
        closeEditModal();
    }
}

// Close modals on ESC key
document.addEventListener('keydown', function(event) {
    if (event.key === 'Escape') {
        closeAddModal();
        closeEditModal();
    }
});

// Auto-hide alerts after 5 seconds
document.addEventListener('DOMContentLoaded', function() {
    const alerts = document.querySelectorAll('.alert');
    
    alerts.forEach(alert => {
        setTimeout(() => {
            alert.style.opacity = '0';
            setTimeout(() => {
                alert.remove();
            }, 300);
        }, 5000);
    });
});