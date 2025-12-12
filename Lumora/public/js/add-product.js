let taggingTimeout = null;

// Listen for changes in product fields to trigger auto-tagging
document.addEventListener('DOMContentLoaded', function() {
    const productNameInput = document.getElementById('product_name');
    const descriptionInput = document.getElementById('description');
    const shortDescriptionInput = document.getElementById('short_description');
    const tagsInput = document.getElementById('tags');

    // Create a button to manually trigger tag prediction
    createAutoTagButton();

    // Auto-predict tags when user stops typing (debounced)
    if (productNameInput && descriptionInput) {
        productNameInput.addEventListener('input', debounceAutoTag);
        descriptionInput.addEventListener('input', debounceAutoTag);
        shortDescriptionInput.addEventListener('input', debounceAutoTag);
    }
});

function createAutoTagButton() {
    const tagsGroup = document.querySelector('#tags').closest('.form-group');
    const button = document.createElement('button');
    button.type = 'button';
    button.className = 'btn btn-outline btn-auto-tag';
    button.innerHTML = '<i class="fas fa-magic"></i> Generate Tags with AI';
    button.style.marginTop = '10px';
    button.onclick = predictTags;
    
    tagsGroup.appendChild(button);
}

function debounceAutoTag() {
    clearTimeout(taggingTimeout);
    taggingTimeout = setTimeout(() => {
        const productName = document.getElementById('product_name').value;
        const description = document.getElementById('description').value;
        
        // Only auto-predict if there's substantial content
        if (productName.length > 10 || description.length > 50) {
            predictTags(true); // true = silent mode (no alerts)
        }
    }, 2000); // Wait 2 seconds after user stops typing
}

async function predictTags(silent = false) {
    const productName = document.getElementById('product_name').value;
    const description = document.getElementById('description').value;
    const shortDescription = document.getElementById('short_description').value;
    const tagsInput = document.getElementById('tags');

    // Validate inputs
    if (!productName && !description) {
        if (!silent) {
            alert('Please enter a product name or description first');
        }
        return;
    }

    // Show loading state
    const button = document.querySelector('.btn-auto-tag');
    const originalHTML = button.innerHTML;
    button.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Generating...';
    button.disabled = true;

    try {
        const response = await fetch('/api/products/predict-tags', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                product_name: productName,
                description: description,
                short_description: shortDescription
            })
        });

        const result = await response.json();

        if (result.success && result.tags && result.tags.length > 0) {
            // Get existing tags
            const existingTags = tagsInput.value
                .split(',')
                .map(tag => tag.trim().toLowerCase())
                .filter(tag => tag.length > 0);

            // Merge with predicted tags (avoid duplicates)
            const predictedTags = result.tags
                .filter(tag => !existingTags.includes(tag.toLowerCase()));

            if (predictedTags.length > 0) {
                // Append new tags
                const allTags = existingTags.concat(predictedTags);
                tagsInput.value = allTags.join(', ');

                // Show success message
                if (!silent) {
                    showTagNotification(
                        `Added ${predictedTags.length} AI-generated tags: ${predictedTags.join(', ')}`,
                        'success'
                    );
                }

                // Highlight the tags input briefly
                tagsInput.style.backgroundColor = '#d4edda';
                setTimeout(() => {
                    tagsInput.style.backgroundColor = '';
                }, 2000);
            } else {
                if (!silent) {
                    showTagNotification('All predicted tags are already present', 'info');
                }
            }
        } else {
            if (!silent) {
                showTagNotification(
                    result.error || 'Could not generate tags. Please try again.',
                    'error'
                );
            }
        }
    } catch (error) {
        console.error('Error predicting tags:', error);
        if (!silent) {
            showTagNotification('Failed to connect to tagging service', 'error');
        }
    } finally {
        // Restore button state
        button.innerHTML = originalHTML;
        button.disabled = false;
    }
}

function showTagNotification(message, type) {
    // Create notification element
    const notification = document.createElement('div');
    notification.className = `tag-notification tag-notification-${type}`;
    notification.innerHTML = `
        <i class="fas fa-${type === 'success' ? 'check-circle' : type === 'error' ? 'exclamation-circle' : 'info-circle'}"></i>
        <span>${message}</span>
    `;

    // Add to page
    const tagsGroup = document.querySelector('#tags').closest('.form-group');
    tagsGroup.appendChild(notification);

    // Remove after 5 seconds
    setTimeout(() => {
        notification.style.opacity = '0';
        setTimeout(() => notification.remove(), 300);
    }, 5000);
}