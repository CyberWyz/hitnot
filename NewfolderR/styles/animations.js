// KILL THE LOADER IMMEDIATELY
document.addEventListener('DOMContentLoaded', function() {
    killLoader();
});

// Run this immediately
killLoader();

// Also run this after a small delay just to be sure
setTimeout(killLoader, 100);
setTimeout(killLoader, 500);
setTimeout(killLoader, 1000);

function killLoader() {
    const loaders = document.querySelectorAll('.page-transition, .loader');
    loaders.forEach(loader => {
        if (loader) {
            loader.style.display = 'none';
            loader.remove(); // Actually remove it from the DOM
        }
    });
}

// Function to show notifications
function showNotification(title, message) {
    const container = document.getElementById('notification-container');
    if (!container) return;
    
    const notification = document.createElement('div');
    notification.className = 'notification';
    notification.innerHTML = `
        <div class="notification-header">
            <h4>${title}</h4>
            <button class="notification-close">&times;</button>
        </div>
        <div class="notification-body">
            <p>${message}</p>
        </div>
    `;
    
    container.appendChild(notification);
    
    // Add animation
    setTimeout(() => {
        notification.classList.add('show');
    }, 10);
    
    // Auto close after 5 seconds
    setTimeout(() => {
        notification.classList.remove('show');
        setTimeout(() => {
            notification.remove();
        }, 300);
    }, 5000);
    
    // Close button functionality
    const closeBtn = notification.querySelector('.notification-close');
    closeBtn.addEventListener('click', () => {
        notification.classList.remove('show');
        setTimeout(() => {
            notification.remove();
        }, 300);
    });
}

document.addEventListener('DOMContentLoaded', function() {
    // Initialize mobile menu functionality
    const mobileMenuBtn = document.getElementById('mobile-menu-btn');
    const mobileNav = document.querySelector('.mobile-nav');
    
    if (mobileMenuBtn && mobileNav) {
        mobileMenuBtn.addEventListener('click', function() {
            mobileNav.classList.toggle('active');
            mobileMenuBtn.classList.toggle('active');
        });
    }
    
    // Sign in button functionality (for demo purposes)
    const signInBtn = document.getElementById('sign-in-btn');
    if (signInBtn) {
        signInBtn.addEventListener('click', function() {
            showNotification('Sign In', 'This is a demonstration. The sign-in functionality is not implemented in this demo.');
        });
    }
    
    // Cookie functions
    function setCookie(name, value, days) {
        let expires = '';
        if (days) {
            const date = new Date();
            date.setTime(date.getTime() + (days * 24 * 60 * 60 * 1000));
            expires = '; expires=' + date.toUTCString();
        }
        document.cookie = name + '=' + (value || '') + expires + '; path=/';
    }

    function getCookie(name) {
        const nameEQ = name + '=';
        const ca = document.cookie.split(';');
        for (let i = 0; i < ca.length; i++) {
            let c = ca[i];
            while (c.charAt(0) === ' ') c = c.substring(1, c.length);
            if (c.indexOf(nameEQ) === 0) return c.substring(nameEQ.length, c.length);
        }
        return null;
    }

    // Show cookie popup if not accepted yet
    const cookiePopup = document.getElementById('cookie-popup');
    const acceptCookiesBtn = document.getElementById('accept-cookies');
    const declineCookiesBtn = document.getElementById('decline-cookies');

    if (cookiePopup && !getCookie('cookie-consent')) {
        setTimeout(() => {
            cookiePopup.classList.add('active');
        }, 2000);
    }

    if (acceptCookiesBtn) {
        acceptCookiesBtn.addEventListener('click', () => {
            setCookie('cookie-consent', 'accepted', 365);
            cookiePopup.classList.remove('active');
            showNotification('Cookies Accepted', 'Thank you! We\'ll remember your preference.');
        });
    }

    if (declineCookiesBtn) {
        declineCookiesBtn.addEventListener('click', () => {
            setCookie('cookie-consent', 'declined', 365);
            cookiePopup.classList.remove('active');
            showNotification('Cookies Declined', 'You\'ve declined cookies. Some features may not work properly.');
        });
    }
});