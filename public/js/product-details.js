/* public/js/product-details.js */

document.addEventListener('DOMContentLoaded', function() {
    
    // --- Image Gallery Logic ---
    const mainImage = document.getElementById('detailMainImage');
    const thumbnails = document.querySelectorAll('.thumb-item');

    thumbnails.forEach(thumb => {
        thumb.addEventListener('click', function() {
            // Get source from data attribute
            const newSrc = this.getAttribute('data-src');
            
            // Apply simple fade effect
            mainImage.style.opacity = '0.5';
            
            setTimeout(() => {
                mainImage.src = newSrc;
                mainImage.style.opacity = '1';
            }, 150);

            // Update active class
            thumbnails.forEach(t => t.classList.remove('active'));
            this.classList.add('active');
        });
    });

    // --- Modal Logic ---
    const deleteModal = document.getElementById('deleteModal');
    
    // Close modal if user clicks outside content
    window.addEventListener('click', function(event) {
        if (event.target === deleteModal) {
            closeDeleteModal();
        }
    });
});

// Exposed function to open modal (called by inline onclick)
function openDeleteModal(id, name) {
    const modal = document.getElementById('deleteModal');
    const idInput = document.getElementById('productIdToDelete');
    const nameSpan = document.getElementById('productNameToDelete');

    if (idInput) idInput.value = id;
    if (nameSpan) nameSpan.textContent = name;
    
    modal.classList.add('active');
    document.body.style.overflow = 'hidden'; // Prevent background scrolling
}

// Exposed function to close modal
function closeDeleteModal() {
    const modal = document.getElementById('deleteModal');
    modal.classList.remove('active');
    document.body.style.overflow = ''; // Restore scrolling
}

