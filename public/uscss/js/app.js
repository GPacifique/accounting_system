/**
 * Sport Academy MS - Custom JavaScript (No Framework Dependencies)
 * Generated: October 28, 2025
 */

// DOM Ready
document.addEventListener('DOMContentLoaded', function() {
    // Initialize all components
    initThemeToggle();
    initMobileMenu();
    initDropdowns();
    initFlashMessages();
    initModals();
    initFormValidation();
});

/**
 * Theme Toggle (Dark/Light Mode)
 */
function initThemeToggle() {
    const themeToggleBtns = document.querySelectorAll('[data-theme-toggle]');
    
    // Get current theme from localStorage or system preference
    const currentTheme = localStorage.getItem('theme') || 
        (window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light');
    
    // Apply theme
    if (currentTheme === 'dark') {
        document.documentElement.classList.add('dark');
    }
    
    // Toggle theme on button click
    themeToggleBtns.forEach(btn => {
        btn.addEventListener('click', function() {
            const isDark = document.documentElement.classList.toggle('dark');
            localStorage.setItem('theme', isDark ? 'dark' : 'light');
        });
    });
}

/**
 * Mobile Menu Toggle
 */
function initMobileMenu() {
    const mobileMenuBtn = document.querySelector('[data-mobile-menu-toggle]');
    const mobileMenu = document.querySelector('[data-mobile-menu]');
    
    if (mobileMenuBtn && mobileMenu) {
        mobileMenuBtn.addEventListener('click', function() {
            const isOpen = mobileMenu.classList.toggle('hidden');
            mobileMenuBtn.setAttribute('aria-expanded', !isOpen);
        });
    }
}

/**
 * Dropdown Menus
 */
function initDropdowns() {
    const dropdowns = document.querySelectorAll('[data-dropdown]');
    
    dropdowns.forEach(dropdown => {
        const trigger = dropdown.querySelector('[data-dropdown-trigger]');
        const menu = dropdown.querySelector('[data-dropdown-menu]');
        
        if (trigger && menu) {
            // Toggle on click
            trigger.addEventListener('click', function(e) {
                e.stopPropagation();
                const isOpen = !menu.classList.contains('hidden');
                
                // Close all dropdowns
                document.querySelectorAll('[data-dropdown-menu]').forEach(m => {
                    m.classList.add('hidden');
                });
                
                // Toggle current
                if (!isOpen) {
                    menu.classList.remove('hidden');
                }
            });
            
            // Close on click outside
            document.addEventListener('click', function() {
                menu.classList.add('hidden');
            });
        }
    });
}

/**
 * Flash Messages Auto-hide
 */
function initFlashMessages() {
    const flashMessages = document.querySelectorAll('[data-flash-message]');
    
    flashMessages.forEach(message => {
        const dismissBtn = message.querySelector('[data-dismiss]');
        
        // Auto-hide after 5 seconds
        setTimeout(() => {
            message.style.opacity = '0';
            message.style.transition = 'opacity 0.3s';
            setTimeout(() => message.remove(), 300);
        }, 5000);
        
        // Manual dismiss
        if (dismissBtn) {
            dismissBtn.addEventListener('click', function() {
                message.style.opacity = '0';
                message.style.transition = 'opacity 0.3s';
                setTimeout(() => message.remove(), 300);
            });
        }
    });
}

/**
 * Modal Windows
 */
function initModals() {
    const modalTriggers = document.querySelectorAll('[data-modal-trigger]');
    
    modalTriggers.forEach(trigger => {
        trigger.addEventListener('click', function(e) {
            e.preventDefault();
            const modalId = this.getAttribute('data-modal-trigger');
            const modal = document.getElementById(modalId);
            
            if (modal) {
                modal.classList.remove('hidden');
                document.body.style.overflow = 'hidden';
                
                // Close on backdrop click
                modal.addEventListener('click', function(e) {
                    if (e.target === modal) {
                        closeModal(modal);
                    }
                });
                
                // Close on close button
                const closeBtn = modal.querySelector('[data-modal-close]');
                if (closeBtn) {
                    closeBtn.addEventListener('click', function() {
                        closeModal(modal);
                    });
                }
                
                // Close on ESC key
                document.addEventListener('keydown', function(e) {
                    if (e.key === 'Escape') {
                        closeModal(modal);
                    }
                });
            }
        });
    });
}

function closeModal(modal) {
    modal.classList.add('hidden');
    document.body.style.overflow = '';
}

/**
 * Form Validation
 */
function initFormValidation() {
    const forms = document.querySelectorAll('form[data-validate]');
    
    forms.forEach(form => {
        form.addEventListener('submit', function(e) {
            let isValid = true;
            const requiredFields = form.querySelectorAll('[required]');
            
            requiredFields.forEach(field => {
                if (!field.value.trim()) {
                    isValid = false;
                    field.classList.add('border-red-500');
                    
                    // Show error message
                    let errorMsg = field.parentElement.querySelector('.error-message');
                    if (!errorMsg) {
                        errorMsg = document.createElement('span');
                        errorMsg.className = 'error-message text-xs text-red-500 mt-1';
                        errorMsg.textContent = 'This field is required';
                        field.parentElement.appendChild(errorMsg);
                    }
                } else {
                    field.classList.remove('border-red-500');
                    const errorMsg = field.parentElement.querySelector('.error-message');
                    if (errorMsg) errorMsg.remove();
                }
            });
            
            if (!isValid) {
                e.preventDefault();
            }
        });
    });
}

/**
 * Smooth Scroll for Anchor Links
 */
document.querySelectorAll('a[href^="#"]').forEach(anchor => {
    anchor.addEventListener('click', function(e) {
        const href = this.getAttribute('href');
        if (href !== '#' && href !== '') {
            const target = document.querySelector(href);
            if (target) {
                e.preventDefault();
                target.scrollIntoView({
                    behavior: 'smooth',
                    block: 'start'
                });
            }
        }
    });
});

/**
 * Table Row Click Handler
 */
document.querySelectorAll('[data-row-link]').forEach(row => {
    row.style.cursor = 'pointer';
    row.addEventListener('click', function() {
        const url = this.getAttribute('data-row-link');
        if (url) window.location.href = url;
    });
});

/**
 * Confirmation Dialogs
 */
document.querySelectorAll('[data-confirm]').forEach(element => {
    element.addEventListener('click', function(e) {
        const message = this.getAttribute('data-confirm');
        if (!confirm(message)) {
            e.preventDefault();
            return false;
        }
    });
});

/**
 * Copy to Clipboard
 */
document.querySelectorAll('[data-copy]').forEach(btn => {
    btn.addEventListener('click', function() {
        const text = this.getAttribute('data-copy');
        navigator.clipboard.writeText(text).then(() => {
            // Show feedback
            const originalText = this.textContent;
            this.textContent = 'Copied!';
            setTimeout(() => {
                this.textContent = originalText;
            }, 2000);
        });
    });
});

/**
 * Tooltips
 */
document.querySelectorAll('[data-tooltip]').forEach(element => {
    element.addEventListener('mouseenter', function() {
        const text = this.getAttribute('data-tooltip');
        const tooltip = document.createElement('div');
        tooltip.className = 'tooltip fixed bg-slate-900 text-white text-xs px-2 py-1 rounded shadow-lg z-50';
        tooltip.textContent = text;
        tooltip.style.pointerEvents = 'none';
        document.body.appendChild(tooltip);
        
        const rect = this.getBoundingClientRect();
        tooltip.style.left = rect.left + (rect.width / 2) - (tooltip.offsetWidth / 2) + 'px';
        tooltip.style.top = rect.top - tooltip.offsetHeight - 5 + 'px';
        
        this.addEventListener('mouseleave', function() {
            tooltip.remove();
        });
    });
});

/**
 * Auto-resize Textareas
 */
document.querySelectorAll('textarea[data-auto-resize]').forEach(textarea => {
    textarea.addEventListener('input', function() {
        this.style.height = 'auto';
        this.style.height = this.scrollHeight + 'px';
    });
});

/**
 * Print Helper
 */
window.printPage = function() {
    window.print();
};

/**
 * Export to CSV (basic)
 */
window.exportTableToCSV = function(tableId, filename) {
    const table = document.getElementById(tableId);
    if (!table) return;
    
    let csv = [];
    const rows = table.querySelectorAll('tr');
    
    rows.forEach(row => {
        const cols = row.querySelectorAll('td, th');
        const rowData = Array.from(cols).map(col => {
            return '"' + col.textContent.trim().replace(/"/g, '""') + '"';
        });
        csv.push(rowData.join(','));
    });
    
    // Download
    const csvContent = csv.join('\n');
    const blob = new Blob([csvContent], { type: 'text/csv' });
    const url = window.URL.createObjectURL(blob);
    const a = document.createElement('a');
    a.href = url;
    a.download = filename || 'export.csv';
    a.click();
    window.URL.revokeObjectURL(url);
};
