document.addEventListener('DOMContentLoaded', function() {
    // Mobile menu toggle
    const mobileMenuToggle = document.getElementById('mobile-menu-toggle');
    if (mobileMenuToggle) {
        mobileMenuToggle.addEventListener('click', function() {
            const nav = document.querySelector('.portal-nav');
            nav.classList.toggle('active');
        });
    }
    
    // Logout confirmation
    const logoutButtons = document.querySelectorAll('.btn-logout');
    logoutButtons.forEach(button => {
        button.addEventListener('click', function(e) {
            if (!confirm('Are you sure you want to logout?')) {
                e.preventDefault();
            }
        });
    });
    
    // Form validation
    const forms = document.querySelectorAll('form');
    forms.forEach(form => {
        form.addEventListener('submit', function(e) {
            const requiredFields = form.querySelectorAll('[required]');
            let isValid = true;
            
            requiredFields.forEach(field => {
                if (!field.value.trim()) {
                    field.classList.add('error');
                    isValid = false;
                } else {
                    field.classList.remove('error');
                }
            });
            
            if (!isValid) {
                e.preventDefault();
                alert('Please fill in all required fields.');
            }
        });
    });
    
    // Initialize date pickers with min date of today
    const dateInputs = document.querySelectorAll('input[type="date"]');
    dateInputs.forEach(input => {
        const today = new Date().toISOString().split('T')[0];
        input.min = today;
    });
    
    // Time slot selection
    document.addEventListener('click', function(e) {
        if (e.target.classList.contains('time-slot')) {
            const container = e.target.closest('.time-slots');
            if (container) {
                container.querySelectorAll('.time-slot').forEach(slot => {
                    slot.classList.remove('active');
                });
                e.target.classList.add('active');
            }
        }
    });
    
    // Responsive adjustments
    function handleResponsive() {
        const nav = document.querySelector('.portal-nav ul');
        if (window.innerWidth < 768) {
            nav.style.display = 'none';
            if (mobileMenuToggle) {
                mobileMenuToggle.style.display = 'block';
            }
        } else {
            nav.style.display = 'flex';
            if (mobileMenuToggle) {
                mobileMenuToggle.style.display = 'none';
            }
        }
    }
    
    window.addEventListener('resize', handleResponsive);
    handleResponsive();
});