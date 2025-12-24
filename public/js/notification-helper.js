/**
 * Notification System Helper
 * Allows triggering notifications from JavaScript
 */

window.notify = {
    /**
     * Show a success notification
     * @param {string} message - The message to display
     */
    success(message) {
        window.dispatchEvent(new CustomEvent('notify', {
            detail: { type: 'success', message }
        }));
    },

    /**
     * Show an error notification
     * @param {string} message - The message to display
     */
    error(message) {
        window.dispatchEvent(new CustomEvent('notify', {
            detail: { type: 'error', message }
        }));
    },

    /**
     * Show a warning notification
     * @param {string} message - The message to display
     */
    warning(message) {
        window.dispatchEvent(new CustomEvent('notify', {
            detail: { type: 'warning', message }
        }));
    },

    /**
     * Show an info notification
     * @param {string} message - The message to display
     */
    info(message) {
        window.dispatchEvent(new CustomEvent('notify', {
            detail: { type: 'info', message }
        }));
    }
};

// Example usage:
// notify.success('Operation completed successfully!');
// notify.error('Something went wrong!');
// notify.warning('Please be careful!');
// notify.info('Here is some information');
