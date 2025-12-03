// Select All Checkbox
document.getElementById('selectAll')?.addEventListener('change', function() {
    const checkboxes = document.querySelectorAll('.product-checkbox');
    checkboxes.forEach(cb => cb.checked = this.checked);
    updateBulkActionButton();
});

// Update Bulk Action Button State
document.querySelectorAll('.product-checkbox').forEach(checkbox => {
    checkbox.addEventListener('change', updateBulkActionButton);
});

function updateBulkActionButton() {
    const checkedBoxes = document.querySelectorAll('.product-checkbox:checked');
    const bulkActionBtn = document.getElementById('applyBulkAction');
    bulkActionBtn.disabled = checkedBoxes.length === 0;
}

// Bulk Action Form Submission
document.getElementById('bulkActionForm')?.addEventListener('submit', function(e) {
    const action = document.getElementById('bulkAction').value;
    if (!action) {
        e.preventDefault();
        alert('Please select an action');
        return;
    }
    
    const checkedBoxes = document.querySelectorAll('.product-checkbox:checked');
    if (checkedBoxes.length === 0) {
        e.preventDefault();
        alert('Please select at least one product');
        return;
    }
    
    if (action === 'delete') {
        if (!confirm(`Are you sure you want to delete ${checkedBoxes.length} product(s)?`)) {
            e.preventDefault();
        }
    }
});

// Helper to get CSRF token
function getCsrfToken() {
    return document.getElementById('csrf_token').value;
}

// Update Product Status
function updateProductStatus(productId, status) {
    fetch('/shop/products/update-status', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        // ADDED: csrf_token to the body
        body: `csrf_token=${getCsrfToken()}&product_id=${productId}&status=${status}`
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Update the select element class based on new status
            const select = event.target;
            select.className = `status-select status-${status.toLowerCase()}`;
            
            // Show success message (you can use a toast notification here)
            console.log(data.message);
        } else {
            alert('Failed to update status: ' + data.message);
            // Reload page to reset the select
            location.reload();
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('An error occurred while updating the status');
        location.reload();
    });
}

// Delete Product
function deleteProduct(productId, productName) {
    document.getElementById('productIdToDelete').value = productId;
    document.getElementById('productNameToDelete').textContent = productName;
    document.getElementById('deleteModal').classList.add('active');
}

function closeDeleteModal() {
    document.getElementById('deleteModal').classList.remove('active');
}

// Close modal on outside click
document.getElementById('deleteModal')?.addEventListener('click', function(e) {
    if (e.target === this) {
        closeDeleteModal();
    }
});