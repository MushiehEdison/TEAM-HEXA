document.addEventListener('DOMContentLoaded', function() {
    // Mobile menu toggle
    const mobileMenuToggle = document.createElement('button');
    mobileMenuToggle.innerHTML = '☰';
    mobileMenuToggle.className = 'mobile-menu-toggle';
    mobileMenuToggle.style.display = 'none';
    
    const header = document.querySelector('.staff-header');
    header.insertBefore(mobileMenuToggle, header.firstChild);
    
    mobileMenuToggle.addEventListener('click', function() {
        const nav = document.querySelector('.main-nav');
        nav.style.display = nav.style.display === 'none' ? 'block' : 'none';
    });
    
    // Handle responsive menu
    function handleResponsive() {
        const nav = document.querySelector('.main-nav');
        if (window.innerWidth < 768) {
            nav.style.display = 'none';
            mobileMenuToggle.style.display = 'block';
        } else {
            nav.style.display = 'block';
            mobileMenuToggle.style.display = 'none';
        }
    }
    
    window.addEventListener('resize', handleResponsive);
    handleResponsive();
    
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
    
    // Initialize time pickers
    const timeInputs = document.querySelectorAll('input[type="time"]');
    timeInputs.forEach(input => {
        input.step = 900; // 15 minute intervals
    });
    
    // Confirm before destructive actions
    const confirmLinks = document.querySelectorAll('[data-confirm]');
    confirmLinks.forEach(link => {
        link.addEventListener('click', function(e) {
            if (!confirm(this.dataset.confirm)) {
                e.preventDefault();
            }
        });
    });
});