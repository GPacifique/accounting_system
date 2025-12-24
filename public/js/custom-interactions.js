/**
 * Custom Interactions Helper
 * Handles custom JavaScript interactions for the application
 */

// Theme toggle functionality
function toggleTheme() {
    const htmlElement = document.documentElement;
    const isDarkMode = htmlElement.classList.toggle('dark');

    // Save preference to localStorage
    localStorage.setItem('theme-preference', isDarkMode ? 'dark' : 'light');

    // Update theme toggle button state
    const themeToggle = document.getElementById('theme-toggle');
    if (themeToggle) {
        themeToggle.setAttribute('aria-pressed', isDarkMode.toString());
    }
}

// Initialize theme on page load
document.addEventListener('DOMContentLoaded', function() {
    const savedTheme = localStorage.getItem('theme-preference');
    const prefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;

    if (savedTheme === 'dark' || (!savedTheme && prefersDark)) {
        document.documentElement.classList.add('dark');
    } else {
        document.documentElement.classList.remove('dark');
    }
});

// Handle sidebar toggle
document.addEventListener('DOMContentLoaded', function() {
    const sidebarToggles = document.querySelectorAll('[data-toggle-sidebar]');
    sidebarToggles.forEach(toggle => {
        toggle.addEventListener('click', function() {
            // Sidebar state is handled by Alpine.js $store.layout.sidebarOpen
        });
    });
});

// Flash message handling
document.addEventListener('DOMContentLoaded', function() {
    const flashElement = document.querySelector('[data-flash]');
    if (flashElement) {
        const message = flashElement.textContent;
        const type = flashElement.dataset.flash;

        if (message && window.notify && window.notify[type]) {
            window.notify[type](message);
        }
    }
});

// Global utility functions
window.debounce = function(func, wait) {
    let timeout;
    return function executedFunction(...args) {
        const later = () => {
            clearTimeout(timeout);
            func(...args);
        };
        clearTimeout(timeout);
        timeout = setTimeout(later, wait);
    };
};

window.throttle = function(func, limit) {
    let inThrottle;
    return function(...args) {
        if (!inThrottle) {
            func.apply(this, args);
            inThrottle = true;
            setTimeout(() => (inThrottle = false), limit);
        }
    };
};

// Log page loads for debugging
console.log('Custom interactions loaded');
