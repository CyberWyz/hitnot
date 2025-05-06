document.addEventListener('DOMContentLoaded', function() {
    // Create grid for security-themed background
    createGrid();
    
    // Add interactive effects for the grid
    startGridAnimation();
    
    // Add scan effect when switching between menu options
    addMenuInteractions();
});

function createGrid() {
    const gridContainer = document.getElementById('grid-container');
    const screenWidth = window.innerWidth;
    const screenHeight = window.innerHeight;
    
    // Determine grid size based on screen dimensions
    const cellSize = 50; // px
    const rows = Math.ceil(screenHeight / cellSize);
    const columns = Math.ceil(screenWidth / cellSize);
    
    // Create grid cells
    for (let i = 0; i < rows; i++) {
        for (let j = 0; j < columns; j++) {
            const cell = document.createElement('div');
            cell.className = 'grid-cell';
            
            cell.style.width = `${cellSize}px`;
            cell.style.height = `${cellSize}px`;
            cell.style.top = `${i * cellSize}px`;
            cell.style.left = `${j * cellSize}px`;
            
            // Add slight delay based on position for a more natural feel
            cell.style.transitionDelay = `${(i + j) * 0.01}s`;
            
            gridContainer.appendChild(cell);
        }
    }
}

function startGridAnimation() {
    const cells = document.querySelectorAll('.grid-cell');
    
    // Initial activation
    activateRandomCells(cells, 5);
    
    // Continuous animation
    setInterval(() => {
        activateRandomCells(cells, 5);
    }, 3000);
    
    // Create roaming highlight
    createRoamingHighlight(cells);
}

function activateRandomCells(cells, percentage) {
    // Deactivate all cells first
    cells.forEach(cell => {
        cell.classList.remove('active');
    });
    
    // Activate random cells
    const activationCount = Math.floor(cells.length * (percentage / 100));
    
    for (let i = 0; i < activationCount; i++) {
        const randomIndex = Math.floor(Math.random() * cells.length);
        cells[randomIndex].classList.add('active');
        
        // Add timed deactivation
        setTimeout(() => {
            cells[randomIndex].classList.remove('active');
        }, 2000 + Math.random() * 2000); // Random duration between 2-4 seconds
    }
}

function createRoamingHighlight(cells) {
    const rows = Math.sqrt(cells.length);
    let currentPosition = Math.floor(Math.random() * cells.length);
    
    setInterval(() => {
        // Remove previous highlight
        cells.forEach(cell => {
            cell.style.boxShadow = '';
        });
        
        // Move in a random direction (up, down, left, right)
        const directions = [
            -1, // left
            1,  // right
            -Math.floor(rows), // up
            Math.floor(rows)   // down
        ];
        
        const randomDirection = directions[Math.floor(Math.random() * directions.length)];
        currentPosition += randomDirection;
        
        // Keep within bounds
        if (currentPosition < 0) currentPosition = cells.length - 1;
        if (currentPosition >= cells.length) currentPosition = 0;
        
        // Highlight new position
        if (cells[currentPosition]) {
            cells[currentPosition].classList.add('active');
            cells[currentPosition].style.boxShadow = '0 0 20px rgba(0, 255, 240, 0.8)';
        }
    }, 500);
}

function addMenuInteractions() {
    const menuOptions = document.querySelectorAll('.options div');
    
    menuOptions.forEach(option => {
        option.addEventListener('mouseenter', function() {
            // Create a temporary scanner effect
            const scanner = document.createElement('div');
            scanner.className = 'scanner-line';
            scanner.style.top = `${this.offsetTop + this.offsetHeight / 2}px`;
            scanner.style.animation = 'scan 2s linear 1';
            
            document.querySelector('.animation-background').appendChild(scanner);
            
            // Remove after animation completes
            setTimeout(() => {
                scanner.remove();
            }, 2000);
        });
    });
}

// Add responsive handling
window.addEventListener('resize', function() {
    // Clear and recreate grid on significant resize
    const gridContainer = document.getElementById('grid-container');
    gridContainer.innerHTML = '';
    createGrid();
    startGridAnimation();
});