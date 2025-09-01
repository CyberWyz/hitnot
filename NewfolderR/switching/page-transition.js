// page-transition.js

document.addEventListener('DOMContentLoaded', function() {
    // Hide the page transition overlay after the page has loaded
    setTimeout(function() {
        const pageTransition = document.querySelector('.page-transition');
        if (pageTransition) {
            pageTransition.style.opacity = '0';
            setTimeout(function() {
                pageTransition.style.display = 'none';
            }, 500); // Wait for fade out animation to complete
        }
    }, 500); // Small delay to ensure everything is loaded
});
