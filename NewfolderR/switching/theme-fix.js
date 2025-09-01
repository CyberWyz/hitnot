// theme-fix.js

document.addEventListener('DOMContentLoaded', function() {
    // Fix theme switcher
    const themeToggle = document.getElementById('theme-toggle');
    const themeMenu = document.querySelector('.theme-menu');
    const themeOptions = document.querySelectorAll('.theme-option');
    
    // Initialize theme from localStorage or default to light
    const savedTheme = localStorage.getItem('theme') || 'light';
    document.documentElement.setAttribute('data-theme', savedTheme);
    
    // Update active theme in the theme menu
    themeOptions.forEach(option => {
        if (option.getAttribute('data-theme') === savedTheme) {
            option.classList.add('active');
        } else {
            option.classList.remove('active');
        }
    });
    
    // Toggle theme menu
    if (themeToggle) {
        themeToggle.addEventListener('click', () => {
            themeMenu.classList.toggle('active');
        });
    }
    
    // Close theme menu when clicking outside
    document.addEventListener('click', (e) => {
        if (!themeMenu.contains(e.target) && !themeToggle.contains(e.target)) {
            themeMenu.classList.remove('active');
        }
    });
    
    // Theme selection
    themeOptions.forEach(option => {
        option.addEventListener('click', () => {
            const theme = option.getAttribute('data-theme');
            
            // Update active theme
            themeOptions.forEach(opt => opt.classList.remove('active'));
            option.classList.add('active');
            
            // Set theme
            document.documentElement.setAttribute('data-theme', theme);
            localStorage.setItem('theme', theme);
            
            // Show notification
            showNotification('Theme Changed', `Theme changed to ${theme.charAt(0).toUpperCase() + theme.slice(1)}`);
            
            // Close theme menu
            themeMenu.classList.remove('active');
        });
    });

    // Force CSS reload if content is missing
    setTimeout(() => {
        const links = document.querySelectorAll('link[rel="stylesheet"]');
        links.forEach(link => {
            const href = link.getAttribute('href');
            link.setAttribute('href', href + '?' + new Date().getTime());
        });
    }, 100);
});