document.addEventListener('DOMContentLoaded', function() {
    // Add any reminder-specific JavaScript here
    console.log('Reminder system loaded');
    
    // Example: Date/time picker initialization
    const dueDateInput = document.getElementById('due_date');
    if (dueDateInput) {
        // Set minimum date to today
        const today = new Date().toISOString().slice(0, 16);
        dueDateInput.min = today;
    }
    
    // Example: Send reminder confirmation
    const sendButtons = document.querySelectorAll('[data-action="send-reminder"]');
    sendButtons.forEach(button => {
        button.addEventListener('click', function(e) {
            if (!confirm('Are you sure you want to send this reminder now?')) {
                e.preventDefault();
            }
        });
    });
});