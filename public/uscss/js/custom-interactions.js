/**
 * SPORT CLUB MANAGEMENT SYSTEM - CUSTOM JAVASCRIPT
 * Modern, Interactive, Platform-Independent
 * Created: October 28, 2025
 */

document.addEventListener('DOMContentLoaded', function() {
    console.log('🎯 Sport Club MS - Custom JS Initialized');
    
    initNavigation();
    initScrollAnimations();
    initInteractiveCards();
    initButtonEffects();
    initSmoothScroll();
    initThemeToggle();
    initFormInteractions();
    initDashboardEnhancements();
});

/* ============================================================
   1. NAVIGATION INTERACTIONS
   ============================================================ */
function initNavigation() {
    const navbar = document.querySelector('nav.navbar');
    const navLinks = document.querySelectorAll('nav.navbar a');
    
    // Sticky navbar on scroll
    window.addEventListener('scroll', function() {
        if (window.scrollY > 50) {
            navbar.style.boxShadow = 'var(--shadow-md)';
        } else {
            navbar.style.boxShadow = 'none';
        }
    });
    
    // Highlight active nav link
    navLinks.forEach(link => {
        link.addEventListener('click', function(e) {
            navLinks.forEach(l => l.style.color = '');
            this.style.color = 'var(--primary-600)';
        });
    });
}

/* ============================================================
   2. SCROLL-TRIGGERED ANIMATIONS
   ============================================================ */
function initScrollAnimations() {
    const animatedElements = document.querySelectorAll('.card, .module-card, .feature-item, .stat-item, .section-title');
    
    const observer = new IntersectionObserver(function(entries) {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.style.animation = 'fadeInUp 0.6s ease-out forwards';
                observer.unobserve(entry.target);
            }
        });
    }, {
        threshold: 0.1,
        rootMargin: '0px 0px -100px 0px'
    });
    
    animatedElements.forEach(el => {
        el.style.opacity = '0';
        el.style.transform = 'translateY(30px)';
        observer.observe(el);
    });
}

/* ============================================================
   3. INTERACTIVE CARD EFFECTS
   ============================================================ */
function initInteractiveCards() {
    const cards = document.querySelectorAll('.module-card');
    
    cards.forEach(card => {
        // 3D tilt effect
        card.addEventListener('mousemove', function(e) {
            const rect = this.getBoundingClientRect();
            const x = e.clientX - rect.left;
            const y = e.clientY - rect.top;
            
            const rotateX = (y - rect.height / 2) / 10;
            const rotateY = (x - rect.width / 2) / -10;
            
            this.style.transform = `perspective(1000px) rotateX(${rotateX}deg) rotateY(${rotateY}deg)`;
        });
        
        // Reset on mouse leave
        card.addEventListener('mouseleave', function() {
            this.style.transform = 'translateY(-10px) scale(1.02)';
        });
        
        // Click ripple effect
        card.addEventListener('click', function(e) {
            createRipple(e, this);
        });
    });
}

/* ============================================================
   4. RIPPLE EFFECT
   ============================================================ */
function createRipple(event, element) {
    const circle = document.createElement('span');
    const diameter = Math.max(element.clientWidth, element.clientHeight);
    const radius = diameter / 2;
    
    circle.style.width = circle.style.height = diameter + 'px';
    circle.style.left = (event.clientX - element.getBoundingClientRect().left - radius) + 'px';
    circle.style.top = (event.clientY - element.getBoundingClientRect().top - radius) + 'px';
    
    circle.classList.add('ripple');
    circle.style.position = 'absolute';
    circle.style.borderRadius = '50%';
    circle.style.backgroundColor = 'rgba(255, 255, 255, 0.6)';
    circle.style.pointerEvents = 'none';
    circle.style.animation = 'ripple-animation 0.6s ease-out';
    
    element.style.position = 'relative';
    element.style.overflow = 'hidden';
    element.appendChild(circle);
    
    setTimeout(() => circle.remove(), 600);
}

// Add ripple animation to stylesheet
const style = document.createElement('style');
style.textContent = `
    @keyframes ripple-animation {
        to {
            transform: scale(4);
            opacity: 0;
        }
    }
`;
document.head.appendChild(style);

/* ============================================================
   5. BUTTON EFFECTS
   ============================================================ */
function initButtonEffects() {
    const buttons = document.querySelectorAll('.btn');
    
    buttons.forEach(btn => {
        btn.addEventListener('mouseenter', function() {
            this.style.transform = 'translateY(-3px)';
        });
        
        btn.addEventListener('mouseleave', function() {
            this.style.transform = 'translateY(0)';
        });
        
        btn.addEventListener('click', function() {
            this.style.animation = 'pulse 0.6s ease-out';
            setTimeout(() => {
                this.style.animation = '';
            }, 600);
        });
    });
}

/* ============================================================
   6. SMOOTH SCROLL
   ============================================================ */
function initSmoothScroll() {
    const links = document.querySelectorAll('a[href^="#"]');
    
    links.forEach(link => {
        link.addEventListener('click', function(e) {
            const href = this.getAttribute('href');
            
            if (href === '#' || href === '') return;
            
            const target = document.querySelector(href);
            if (!target) return;
            
            e.preventDefault();
            
            const offsetTop = target.getBoundingClientRect().top + window.scrollY - 80;
            
            window.scrollTo({
                top: offsetTop,
                behavior: 'smooth'
            });
        });
    });
}

/* ============================================================
   7. COUNTER ANIMATION FOR STATS
   ============================================================ */
function animateCounters() {
    const statNumbers = document.querySelectorAll('.stat-number');
    
    statNumbers.forEach(element => {
        const finalValue = parseInt(element.textContent);
        let currentValue = 0;
        const increment = finalValue / 50;
        const duration = 2000;
        const steps = 50;
        const stepDuration = duration / steps;
        
        const counter = setInterval(() => {
            currentValue += increment;
            if (currentValue >= finalValue) {
                element.textContent = finalValue + '+';
                clearInterval(counter);
            } else {
                element.textContent = Math.floor(currentValue) + '+';
            }
        }, stepDuration);
    });
}

// Trigger counter animation when stats section is visible
window.addEventListener('scroll', function() {
    const statsSection = document.querySelector('.stats-grid');
    if (statsSection && !statsSection.dataset.animated) {
        const rect = statsSection.getBoundingClientRect();
        if (rect.top < window.innerHeight) {
            animateCounters();
            statsSection.dataset.animated = 'true';
        }
    }
});

/* ============================================================
   8. THEME TOGGLE
   ============================================================ */
function initThemeToggle() {
    const themeToggle = document.querySelector('[data-theme-toggle]');
    const htmlElement = document.documentElement;
    
    // Check for saved theme preference or default to 'light'
    const currentTheme = localStorage.getItem('theme') || 'light';
    htmlElement.setAttribute('data-theme', currentTheme);
    
    if (themeToggle) {
        themeToggle.addEventListener('click', function() {
            const theme = htmlElement.getAttribute('data-theme');
            const newTheme = theme === 'light' ? 'dark' : 'light';
            
            htmlElement.setAttribute('data-theme', newTheme);
            localStorage.setItem('theme', newTheme);
        });
    }
}

/* ============================================================
   9. FORM INTERACTIONS
   ============================================================ */
function initFormInteractions() {
    const inputs = document.querySelectorAll('input, textarea, select');
    
    inputs.forEach(input => {
        // Add focus effect
        input.addEventListener('focus', function() {
            this.style.boxShadow = '0 0 0 3px rgba(59, 130, 246, 0.1)';
            this.style.borderColor = 'var(--primary-600)';
        });
        
        input.addEventListener('blur', function() {
            this.style.boxShadow = '';
            this.style.borderColor = '';
        });
        
        // Floating label effect
        input.addEventListener('input', function() {
            if (this.value) {
                this.style.backgroundColor = 'rgba(59, 130, 246, 0.02)';
            } else {
                this.style.backgroundColor = '';
            }
        });
    });
}

/* ============================================================
   10. KEYBOARD SHORTCUTS
   ============================================================ */
document.addEventListener('keydown', function(e) {
    // ESC to close modals
    if (e.key === 'Escape') {
        const modal = document.querySelector('[data-modal].visible');
        if (modal) {
            modal.classList.remove('visible');
        }
    }
    
    // CTRL+K to focus search (if applicable)
    if ((e.ctrlKey || e.metaKey) && e.key === 'k') {
        e.preventDefault();
        const searchInput = document.querySelector('[data-search]');
        if (searchInput) {
            searchInput.focus();
        }
    }
});

/* ============================================================
   11. PARALLAX EFFECT
   ============================================================ */
window.addEventListener('scroll', function() {
    const parallaxElements = document.querySelectorAll('[data-parallax]');
    
    parallaxElements.forEach(element => {
        const scrollPosition = window.scrollY;
        const elementOffset = element.offsetTop;
        const distance = scrollPosition - elementOffset;
        
        if (distance > -window.innerHeight && distance < window.innerHeight) {
            element.style.transform = `translateY(${distance * 0.5}px)`;
        }
    });
});

/* ============================================================
   12. LOADING ANIMATION
   ============================================================ */
window.addEventListener('load', function() {
    document.body.style.opacity = '0';
    setTimeout(() => {
        document.body.style.transition = 'opacity 0.5s ease-in';
        document.body.style.opacity = '1';
    }, 100);
});

/* ============================================================
   13. UTILITY FUNCTIONS
   ============================================================ */

// Debounce function for performance
function debounce(func, wait) {
    let timeout;
    return function executedFunction(...args) {
        const later = () => {
            clearTimeout(timeout);
            func(...args);
        };
        clearTimeout(timeout);
        timeout = setTimeout(later, wait);
    };
}

// Throttle function
function throttle(func, limit) {
    let inThrottle;
    return function(...args) {
        if (!inThrottle) {
            func.apply(this, args);
            inThrottle = true;
            setTimeout(() => inThrottle = false, limit);
        }
    };
}

// Show notification
function showNotification(message, type = 'info') {
    const notification = document.createElement('div');
    notification.style.cssText = `
        position: fixed;
        bottom: 20px;
        right: 20px;
        padding: 15px 20px;
        background: ${type === 'error' ? '#ef4444' : type === 'success' ? '#10b981' : '#3b82f6'};
        color: white;
        border-radius: 8px;
        box-shadow: 0 10px 25px rgba(0,0,0,0.2);
        z-index: 10000;
        animation: slideInRight 0.3s ease-out;
    `;
    notification.textContent = message;
    document.body.appendChild(notification);
    
    setTimeout(() => {
        notification.style.animation = 'slideInRight 0.3s ease-out reverse';
        setTimeout(() => notification.remove(), 300);
    }, 3000);
}

/* ============================================================
   14. PERFORMANCE MONITORING
   ============================================================ */
if (window.performance && window.performance.timing) {
    window.addEventListener('load', function() {
        const perfData = window.performance.timing;
        const pageLoadTime = perfData.loadEventEnd - perfData.navigationStart;
        console.log('⏱️ Page loaded in: ' + pageLoadTime + 'ms');
    });
}

/* ============================================================
   15. ACCESSIBILITY FEATURES
   ============================================================ */

// Add keyboard navigation for cards
document.addEventListener('keydown', function(e) {
    if (e.key === 'Tab') {
        const activeElement = document.activeElement;
        if (activeElement && activeElement.classList.contains('module-card')) {
            activeElement.style.outline = '2px solid var(--primary-600)';
            activeElement.style.outlineOffset = '2px';
        }
    }
});

console.log('✨ Custom Design System Loaded Successfully');

/* ============================================================
   Extra: Dashboard enhancements (count-up animation for KPIs)
   Elements should have `data-animate-count` attribute.
   ============================================================ */
function initDashboardEnhancements() {
    const els = document.querySelectorAll('[data-animate-count]');
    if (!els.length) return;

    function parseNumber(text) {
        if (!text) return 0;
        return parseFloat(String(text).replace(/,/g, '').replace(/[^0-9.\-]/g, '')) || 0;
    }

    function formatNumber(value, decimals) {
        try { return new Intl.NumberFormat('en-US', { minimumFractionDigits: decimals, maximumFractionDigits: decimals }).format(value); }
        catch (e) { return value.toFixed(decimals); }
    }

    function animate(el, duration = 1200) {
        const orig = el.textContent.trim();
        const target = parseNumber(orig);
        const hasDecimal = String(orig).indexOf('.') !== -1;
        const decimals = hasDecimal ? String(orig).split('.').pop().length : 0;
        if (!target || isNaN(target)) return;

        let start = null; const startValue = 0;
        function step(ts) {
            if (!start) start = ts;
            const progress = Math.min((ts - start) / duration, 1);
            const current = startValue + (target - startValue) * (1 - Math.pow(1 - progress, 3));
            // preserve non-numeric suffix/prefix
            const suffix = orig.replace(/[-+0-9,\.\s]/g, '');
            el.textContent = formatNumber(current, decimals) + suffix;
            if (progress < 1) requestAnimationFrame(step);
            else el.textContent = formatNumber(target, decimals) + suffix;
        }
        requestAnimationFrame(step);
    }

    els.forEach(el => {
        try { animate(el, 1200); } catch (e) { /* ignore */ }
    });
}
