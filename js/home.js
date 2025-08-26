// Sidebar Toggle
document.addEventListener('DOMContentLoaded', function() {
    const sidebarToggle = document.getElementById('sidebarToggle');
    const sidebar = document.querySelector('.sidebar');
    const contentWrapper = document.querySelector('.content-wrapper');

    if (sidebarToggle && sidebar && contentWrapper) {
        sidebarToggle.addEventListener('click', () => {
            sidebar.classList.toggle('active');
            if (sidebar.classList.contains('active')) {
                contentWrapper.style.marginLeft = '250px';
                contentWrapper.style.width = 'calc(100% - 250px)';
            } else {
                contentWrapper.style.marginLeft = '0';
                contentWrapper.style.width = '100%';
            }
        });

        // Check if on mobile and adjust sidebar
        function checkWidth() {
            if (window.innerWidth <= 768) {
                sidebar.classList.remove('active');
                contentWrapper.style.marginLeft = '0';
                contentWrapper.style.width = '100%';
            } else {
                sidebar.classList.add('active');
                contentWrapper.style.marginLeft = '250px';
                contentWrapper.style.width = 'calc(100% - 250px)';
            }
        }

        // Run on page load
        checkWidth();

        // Run on window resize
        window.addEventListener('resize', checkWidth);
    }

    // Dark Mode Toggle
    const themeToggle = document.getElementById('theme-toggle');
    const body = document.body;
    if (themeToggle) {
        const themeIcon = themeToggle.querySelector('i');

        // Check for saved theme in localStorage
        const savedTheme = localStorage.getItem('theme');
        if (savedTheme) {
            body.classList.add(savedTheme);
            if (savedTheme === 'dark-mode') {
                themeIcon.classList.remove('fa-moon');
                themeIcon.classList.add('fa-sun');
                themeToggle.querySelector('span').textContent = 'Light Mode';
            }
        }

        themeToggle.addEventListener('click', () => {
            body.classList.toggle('dark-mode');
            const isDarkMode = body.classList.contains('dark-mode');
            // Toggle icon and text
            if (isDarkMode) {
                themeIcon.classList.remove('fa-moon');
                themeIcon.classList.add('fa-sun');
                themeToggle.querySelector('span').textContent = 'Light Mode';
            } else {
                themeIcon.classList.remove('fa-sun');
                themeIcon.classList.add('fa-moon');
                themeToggle.querySelector('span').textContent = 'Dark Mode';
            }
            localStorage.setItem('theme', isDarkMode ? 'dark-mode' : '');
        });
    }

    // Help Modal Functionality
    const helpButton = document.getElementById('helpButton');
    const helpModal = document.getElementById('helpModal');
    const closeButton = document.querySelector('.close');

    if (helpButton && helpModal && closeButton) {
        // Open modal when help button is clicked
        helpButton.addEventListener('click', function(e) {
            e.preventDefault();
            helpModal.style.display = 'block';
            document.body.style.overflow = 'hidden'; // Prevent scrolling behind modal
        });

        // Close modal when X is clicked
        closeButton.addEventListener('click', function() {
            helpModal.style.display = 'none';
            document.body.style.overflow = ''; // Restore scrolling
        });

        // Close modal when clicking outside of it
        window.addEventListener('click', function(event) {
            if (event.target === helpModal) {
                helpModal.style.display = 'none';
                document.body.style.overflow = ''; // Restore scrolling
            }
        });

        // Handle anchor links within the help modal
        document.querySelectorAll('.toc a').forEach(link => {
            link.addEventListener('click', function(e) {
                e.preventDefault();
                const targetId = this.getAttribute('href');
                const targetElement = document.querySelector(targetId);

                if (targetElement) {
                    targetElement.scrollIntoView({
                        behavior: 'smooth'
                    });
                }
            });
        });
    }
});