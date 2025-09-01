// Function to hide the page transition overlay
function hidePageTransition() {
  const pageTransition = document.querySelector('.page-transition');
  if (pageTransition) {
    pageTransition.style.opacity = '0';
    setTimeout(() => {
      pageTransition.style.display = 'none';
    }, 500); // Wait for transition to complete before hiding
  }
}

// Hide the loading screen when the page is fully loaded
document.addEventListener('DOMContentLoaded', function() {
  // Small delay to ensure everything is loaded
  setTimeout(hidePageTransition, 300);
});

// Also hide it if it's still showing after window load
window.addEventListener('load', hidePageTransition);
