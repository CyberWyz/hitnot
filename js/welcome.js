// TSParticles Initialization
tsParticles.load("tsparticles", {
    fpsLimit: 60,
    interactivity: {
        events: {
            onClick: { enable: true, mode: "push" },
            onHover: { enable: true, mode: "repulse" },
        },
        modes: {
            push: { quantity: 4 },
            repulse: { distance: 200, duration: 0.4 },
        },
    },
    particles: {
        color: { value: "#4e73df" },
        links: {
            color: "#4e73df",
            distance: 150,
            enable: true,
            opacity: 0.5,
            width: 1,
        },
        move: {
            direction: "none",
            enable: true,
            outModes: "bounce",
            random: false,
            speed: 2,
            straight: false,
        },
        number: { density: { enable: true, area: 800 }, value: 80 },
        opacity: { value: 0.5 },
        shape: { type: "circle" },
        size: { value: { min: 1, max: 5 } },
    },
    detectRetina: true,
});

// Sidebar Toggle
const sidebarToggle = document.getElementById('sidebarToggle');
const sidebar = document.querySelector('.sidebar');
const contentWrapper = document.querySelector('.content-wrapper');

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

// Dark Mode Toggle
const themeToggle = document.createElement('button');
themeToggle.id = 'theme-toggle';
themeToggle.className = 'btn nav-btn';
themeToggle.innerHTML = '<i class="fas fa-moon"></i> <span>Dark Mode</span>';
document.querySelector('.content-wrapper').prepend(themeToggle);

const body = document.body;
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