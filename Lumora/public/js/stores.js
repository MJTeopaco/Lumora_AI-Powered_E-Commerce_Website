// public/js/stores.js
// Stores Directory JavaScript

document.addEventListener('DOMContentLoaded', function() {
    console.log('Stores page loaded');
    
    // Debug: Log all banner images on page load
    const bannerImages = document.querySelectorAll('.featured-seller-banner img, .store-card-banner img');
    console.log('Found ' + bannerImages.length + ' banner images');
    
    bannerImages.forEach((img, index) => {
        console.log('Image ' + index + ':', {
            src: img.src,
            alt: img.alt,
            naturalWidth: img.naturalWidth,
            complete: img.complete
        });
        
        // Add error event listener to log failures
        img.addEventListener('error', function() {
            console.error('Failed to load image:', this.src);
        });
        
        // Log when image loads successfully
        img.addEventListener('load', function() {
            console.log('Successfully loaded:', this.src);
        });
    });
    
    // Debug: Log all profile images
    const profileImages = document.querySelectorAll('.featured-seller-profile-pic img, .store-card-profile img');
    console.log('Found ' + profileImages.length + ' profile images');
    
    profileImages.forEach((img, index) => {
        console.log('Profile ' + index + ':', {
            src: img.src,
            alt: img.alt
        });
    });
});