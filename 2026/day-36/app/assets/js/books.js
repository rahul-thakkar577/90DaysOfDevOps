document.addEventListener('DOMContentLoaded', function() {
    const bookImages = document.querySelectorAll('.book-image img');
    
    bookImages.forEach(img => {
        // Handle load errors
        img.onerror = function() {
            this.parentElement.classList.add('no-image');
        };
        
        // Handle empty or invalid sources
        if (!img.src || img.src === '' || img.src === 'undefined' || img.src === 'null') {
            img.parentElement.classList.add('no-image');
        }
    });
}); 