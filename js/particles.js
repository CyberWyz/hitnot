// Particle Background Animation for Home Page
document.addEventListener('DOMContentLoaded', function() {
    const particlesContainer = document.querySelector('.particles-container');

    if (!particlesContainer) {
        console.warn('Particles container not found');
        return;
    }

    // Create particles
    function createParticle() {
        const particle = document.createElement('div');
        particle.className = 'particle';

        // Random size between 8px and 20px (much bigger)
        const size = Math.random() * 12 + 8;
        particle.style.width = size + 'px';
        particle.style.height = size + 'px';

        // Random horizontal position
        particle.style.left = Math.random() * 100 + '%';

        // Random animation delay
        particle.style.animationDelay = Math.random() * 15 + 's';

        // Higher opacity for more visibility
        particle.style.opacity = Math.random() * 0.4 + 0.4;

        particlesContainer.appendChild(particle);

        // Remove particle after animation completes
        setTimeout(() => {
            if (particle.parentNode) {
                particle.parentNode.removeChild(particle);
            }
        }, 20000);
    }

    // Background particles removed - only topbar particles remain
    /*
    // Create initial particles (fewer particles)
    for (let i = 0; i < 30; i++) {
        setTimeout(createParticle, Math.random() * 5000);
    }

    // Create new particles less frequently
    setInterval(createParticle, 800);
    */

    // Create topbar particles
    function createTopbarParticle() {
        const topbar = document.querySelector('.topbar');
        if (!topbar) return;

        const particle = document.createElement('div');
        particle.className = 'topbar-particle';

        // Smaller particles for topbar
        const size = Math.random() * 3 + 1;
        particle.style.width = size + 'px';
        particle.style.height = size + 'px';

        // Position within topbar bounds
        const topbarRect = topbar.getBoundingClientRect();
        particle.style.left = (Math.random() * topbarRect.width) + 'px';
        particle.style.top = (Math.random() * topbarRect.height) + 'px';
        particle.style.position = 'absolute';

        // Random animation delay
        particle.style.animationDelay = Math.random() * 10 + 's';

        // Higher opacity for topbar particles
        particle.style.opacity = Math.random() * 0.5 + 0.3;

        topbar.appendChild(particle);

        // Remove particle after animation completes
        setTimeout(() => {
            if (particle.parentNode) {
                particle.parentNode.removeChild(particle);
            }
        }, 15000);
    }

    // Create initial topbar particles
    for (let i = 0; i < 15; i++) {
        setTimeout(createTopbarParticle, Math.random() * 3000);
    }

    // Create new topbar particles
    setInterval(createTopbarParticle, 1200);

    // Add some interactive particles on mouse move
    document.addEventListener('mousemove', function(e) {
        if (Math.random() > 0.9) { // Only create particles occasionally
            const particle = document.createElement('div');
            particle.className = 'particle interactive-particle';

            const size = Math.random() * 3 + 1;
            particle.style.width = size + 'px';
            particle.style.height = size + 'px';
            particle.style.left = e.clientX + 'px';
            particle.style.top = e.clientY + 'px';
            particle.style.position = 'fixed';
            particle.style.pointerEvents = 'none';
            particle.style.zIndex = '9999';

            document.body.appendChild(particle);

            // Remove interactive particle quickly
            setTimeout(() => {
                if (particle.parentNode) {
                    particle.parentNode.removeChild(particle);
                }
            }, 2000);
        }
    });
});

// Add CSS for interactive particles
const style = document.createElement('style');
style.textContent = `
    .interactive-particle {
        background: rgba(255, 255, 255, 0.8) !important;
        animation: interactiveFloat 2s ease-out forwards !important;
    }

    @keyframes interactiveFloat {
        0% {
            transform: scale(0) rotate(0deg);
            opacity: 1;
        }
        100% {
            transform: scale(1) rotate(180deg) translateY(-50px);
            opacity: 0;
        }
    }
`;
document.head.appendChild(style);