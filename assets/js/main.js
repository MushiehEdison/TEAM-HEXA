// assets/js/main.js

// Sidebar toggle functionality
function toggleSidebar() {
    const sidebar = document.querySelector('.sidebar');
    const mainContent = document.querySelector('.main-content');
    
    sidebar.classList.toggle('collapsed');
    mainContent.classList.toggle('expanded');
}

// Modal functionality
class Modal {
    constructor(modalId) {
        this.modal = document.getElementById(modalId);
        this.closeBtn = this.modal.querySelector('.modal-close');
        this.init();
    }
    
    init() {
        // Close modal when clicking close button
        if (this.closeBtn) {
            this.closeBtn.addEventListener('click', () => this.close());
        }
        
        // Close modal when clicking outside
        this.modal.addEventListener('click', (e) => {
            if (e.target === this.modal) {
                this.close();
            }
        });
        
        // Close modal with Escape key
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape' && this.modal.classList.contains('active')) {
                this.close();
            }
        });
    }
    
    open() {
        this.modal.classList.add('active');
        document.body.style.overflow = 'hidden';
    }
    
    close() {
        this.modal.classList.remove('active');
        document.body.style.overflow = 'auto';
    }
}

// Alert system
class AlertSystem {
    static show(message, type = 'info', duration = 5000) {
        const alertContainer = this.getOrCreateContainer();
        const alert = this.createAlert(message, type);
        
        alertContainer.appendChild(alert);
        
        // Auto remove after duration
        setTimeout(() => {
            this.removeAlert(alert);
        }, duration);
        
        return alert;
    }
    
    static getOrCreateContainer() {
        let container = document.getElementById('alert-container');
        if (!container) {
            container = document.createElement('div');
            container.id = 'alert-container';
            container.style.cssText = `
                position: fixed;
                top: 20px;
                right: 20px;
                z-index: 3000;
                max-width: 400px;
            `;
            document.body.appendChild(container);
        }
        return container;
    }
    
    static createAlert(message, type) {
        const alert = document.createElement('div');
        alert.className = `alert alert-${type}`;
        alert.style.cssText = `
            margin-bottom: 10px;
            padding: 12px 16px;
            border-radius: 8px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
            animation: slideIn 0.3s ease;
        `;
        
        const colors = {
            success: { bg: '#dcfce7', border: '#166534', text: '#166534' },
            error: { bg: '#fee2e2', border: '#991b1b', text: '#991b1b' },
            warning: { bg: '#fef3c7', border: '#92400e', text: '#92400e' },
            info: { bg: '#dbeafe', border: '#1e40af', text: '#1e40af' }
        };
        
        const color = colors[type] || colors.info;
        alert.style.backgroundColor = color.bg;
        alert.style.borderLeft = `4px solid ${color.border}`;
        alert.style.color = color.text;
        
        alert.innerHTML = `
            <div style="display: flex; justify-content: space-between; align-items: center;">
                <span>${message}</span>
                <button onclick="AlertSystem.removeAlert(this.parentElement.parentElement)" 
                        style="background: none; border: none; color: inherit; cursor: pointer; padding: 0 4px;">×</button>
            </div>
        `;
        
        return alert;
    }
    
    static removeAlert(alert) {
        if (alert && alert.parentNode) {
            alert.style.animation = 'slideOut 0.3s ease';
            setTimeout(() => {
                alert.remove();
            }, 300);
        }
    }
}

// Add CSS animations
const style = document.createElement('style');
style.textContent = `
    @keyframes slideIn {
        from { transform: translateX(100%); opacity: 0; }
        to { transform: translateX(0); opacity: 1; }
    }
    
    @keyframes slideOut {
        from { transform: translateX(0); opacity: 1; }
        to { transform: translateX(100%); opacity: 0; }
    }
`;
document.head.appendChild(style);

// AJAX utility functions
class AjaxUtil {
    static async post(url, data) {
        try {
            const formData = new FormData();
            for (const key in data) {
                formData.append(key, data[key]);
            }
            
            const response = await fetch(url, {
                method: 'POST',
                body: formData
            });
            
            return await response.json();
        } catch (error) {
            console.error('AJAX Error:', error);
            return { success: false, message: 'Network error occurred' };
        }
    }
    
    static async get(url, params = {}) {
        try {
            const urlParams = new URLSearchParams(params);
            const response = await fetch(`${url}?${urlParams}`);
            
            return await response.json();
        } catch (error) {
            console.error('AJAX Error:', error);
            return { success: false, message: 'Network error occurred' };
        }
    }
}

// Form validation utility
class FormValidator {
    static validateRequired(fields) {
        const errors = [];
        
        fields.forEach(field => {
            const element = document.querySelector(`[name="${field.name}"]`);
            if (!element || !element.value.trim()) {
                errors.push(field.label + ' is required');
            }
        });
        
        return errors;
    }
    
    static validateEmail(email) {
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        return emailRegex.test(email);
    }
    
    static validatePhone(phone) {
        const phoneRegex = /^\+?[\d\s-()]+$/;
        return phoneRegex.test(phone) && phone.replace(/\D/g, '').length >= 10;
    }
    
    static showFieldError(fieldName, message) {
        const field = document.querySelector(`[name="${fieldName}"]`);
        if (field) {
            field.style.borderColor = '#ef4444';
            
            // Remove existing error message
            const existingError = field.parentNode.querySelector('.field-error');
            if (existingError) {
                existingError.remove();
            }
            
            // Add new error message
            const errorDiv = document.createElement('div');
            errorDiv.className = 'field-error';
            errorDiv.style.cssText = 'color: #ef4444; font-size: 0.8rem; margin-top: 0.25rem;';
            errorDiv.textContent = message;
            field.parentNode.appendChild(errorDiv);
        }
    }
    
    static clearFieldErrors() {
        document.querySelectorAll('.field-error').forEach(error => error.remove());
        document.querySelectorAll('input, select, textarea').forEach(field => {
            field.style.borderColor = '#ddd';
        });
    }
}

// Data table utility
class DataTable {
    constructor(tableId, options = {}) {
        this.table = document.getElementById(tableId);
        this.options = {
            searchable: true,
            sortable: true,
            pagination: true,
            pageSize: 10,
            ...options
        };
        
        this.currentPage = 1;
        this.sortColumn = null;
        this.sortDirection = 'asc';
        this.searchQuery = '';
        
        if (this.table) {
            this.init();
        }
    }
    
    init() {
        this.originalData = this.getTableData();
        this.filteredData = [...this.originalData];
        
        if (this.options.searchable) {
            this.addSearchBox();
        }
        
        if (this.options.sortable) {
            this.addSortHandlers();
        }
        
        if (this.options.pagination) {
            this.addPagination();
        }
        
        this.render();
    }
    
    getTableData() {
        const rows = Array.from(this.table.querySelectorAll('tbody tr'));
        return rows.map(row => {
            const cells = Array.from(row.querySelectorAll('td'));
            return {
                element: row,
                data: cells.map(cell => cell.textContent.trim())
            };
        });
    }
    
    addSearchBox() {
        const container = document.createElement('div');
        container.className = 'table-controls';
        container.style.cssText = 'margin-bottom: 1rem; display: flex; justify-content: space-between; align-items: center;';
        
        const searchBox = document.createElement('input');
        searchBox.type = 'text';
        searchBox.placeholder = 'Search...';
        searchBox.style.cssText = 'padding: 0.5rem; border: 1px solid #ddd; border-radius: 4px; width: 250px;';
        
        searchBox.addEventListener('input', (e) => {
            this.searchQuery = e.target.value.toLowerCase();
            this.filter();
        });
        
        container.appendChild(searchBox);
        this.table.parentNode.insertBefore(container, this.table);
    }
    
    addSortHandlers() {
        const headers = this.table.querySelectorAll('th');
        headers.forEach((header, index) => {
            header.style.cursor = 'pointer';
            header.addEventListener('click', () => {
                this.sort(index);
            });
        });
    }
    
    filter() {
        if (this.searchQuery) {
            this.filteredData = this.originalData.filter(row => {
                return row.data.some(cell => 
                    cell.toLowerCase().includes(this.searchQuery)
                );
            });
        } else {
            this.filteredData = [...this.originalData];
        }
        
        this.currentPage = 1;
        this.render();
    }
    
    sort(columnIndex) {
        if (this.sortColumn === columnIndex) {
            this.sortDirection = this.sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            this.sortColumn = columnIndex;
            this.sortDirection = 'asc';
        }
        
        this.filteredData.sort((a, b) => {
            const aVal = a.data[columnIndex];
            const bVal = b.data[columnIndex];
            
            const result = aVal.localeCompare(bVal, undefined, { numeric: true });
            return this.sortDirection === 'asc' ? result : -result;
        });
        
        this.render();
    }
    
    addPagination() {
        const paginationContainer = document.createElement('div');
        paginationContainer.className = 'pagination-container';
        paginationContainer.style.cssText = 'margin-top: 1rem; display: flex; justify-content: center; align-items: center; gap: 0.5rem;';
        
        this.paginationContainer = paginationContainer;
        this.table.parentNode.appendChild(paginationContainer);
    }
    
    render() {
        // Clear current table body
        const tbody = this.table.querySelector('tbody');
        tbody.innerHTML = '';
        
        // Calculate pagination
        const totalItems = this.filteredData.length;
        const totalPages = Math.ceil(totalItems / this.options.pageSize);
        const startIndex = (this.currentPage - 1) * this.options.pageSize;
        const endIndex = startIndex + this.options.pageSize;
        
        // Render current page data
        const pageData = this.filteredData.slice(startIndex, endIndex);
        pageData.forEach(row => {
            tbody.appendChild(row.element);
        });
        
        // Update pagination
        if (this.options.pagination) {
            this.renderPagination(totalPages);
        }
    }
    
    renderPagination(totalPages) {
        if (!this.paginationContainer) return;
        
        this.paginationContainer.innerHTML = '';
        
        // Previous button
        const prevBtn = document.createElement('button');
        prevBtn.textContent = '←';
        prevBtn.disabled = this.currentPage === 1;
        prevBtn.addEventListener('click', () => {
            if (this.currentPage > 1) {
                this.currentPage--;
                this.render();
            }
        });
        this.paginationContainer.appendChild(prevBtn);
        
        // Page numbers
        for (let i = 1; i <= totalPages; i++) {
            const pageBtn = document.createElement('button');
            pageBtn.textContent = i;
            pageBtn.className = i === this.currentPage ? 'active' : '';
            pageBtn.addEventListener('click', () => {
                this.currentPage = i;
                this.render();
            });
            this.paginationContainer.appendChild(pageBtn);
        }
        
        // Next button
        const nextBtn = document.createElement('button');
        nextBtn.textContent = '→';
        nextBtn.disabled = this.currentPage === totalPages;
        nextBtn.addEventListener('click', () => {
            if (this.currentPage < totalPages) {
                this.currentPage++;
                this.render();
            }
        });
        this.paginationContainer.appendChild(nextBtn);
    }
}

// Blood Bank specific functions
class BloodBankManager {
    static async addDonation(formData) {
        const result = await AjaxUtil.post('../ajax/blood_operations.php', {
            action: 'add_donation',
            ...formData
        });
        
        if (result.success) {
            AlertSystem.show(result.message, 'success');
        } else {
            AlertSystem.show(result.message, 'error');
        }
        
        return result;
    }
    
    static async addBloodRequest(formData) {
        const result = await AjaxUtil.post('../ajax/blood_operations.php', {
            action: 'add_request',
            ...formData
        });
        
        if (result.success) {
            AlertSystem.show(result.message, 'success');
        } else {
            AlertSystem.show(result.message, 'error');
        }
        
        return result;
    }
    
    static async fulfillRequest(requestId) {
        if (confirm('Are you sure you want to fulfill this blood request?')) {
            const result = await AjaxUtil.post('../ajax/blood_operations.php', {
                action: 'fulfill_request',
                request_id: requestId
            });
            
            if (result.success) {
                AlertSystem.show(result.message, 'success');
                location.reload();
            } else {
                AlertSystem.show(result.message, 'error');
            }
        }
    }
    
    static async updateRequestStatus(requestId, status) {
        const result = await AjaxUtil.post('../ajax/blood_operations.php', {
            action: 'update_request_status',
            request_id: requestId,
            status: status
        });
        
        if (result.success) {
            AlertSystem.show(result.message, 'success');
            location.reload();
        } else {
            AlertSystem.show(result.message, 'error');
        }
        
        return result;
    }
}

// Staff Management functions
class StaffManager {
    static async createStaff(formData) {
        const result = await AjaxUtil.post('../ajax/staff_operations.php', {
            action: 'create_staff',
            ...formData
        });
        
        if (result.success) {
            AlertSystem.show(result.message, 'success');
        } else {
            AlertSystem.show(result.message, 'error');
        }
        
        return result;
    }
    
    static async updateStaff(staffId, formData) {
        const result = await AjaxUtil.post('../ajax/staff_operations.php', {
            action: 'update_staff',
            staff_id: staffId,
            ...formData
        });
        
        if (result.success) {
            AlertSystem.show(result.message, 'success');
        } else {
            AlertSystem.show(result.message, 'error');
        }
        
        return result;
    }
    
    static async toggleStaffStatus(staffId) {
        if (confirm('Are you sure you want to change this staff member\'s status?')) {
            const result = await AjaxUtil.post('../ajax/staff_operations.php', {
                action: 'toggle_status',
                staff_id: staffId
            });
            
            if (result.success) {
                AlertSystem.show(result.message, 'success');
                location.reload();
            } else {
                AlertSystem.show(result.message, 'error');
            }
        }
    }
    
    static async resetPassword(staffId, newPassword) {
        const result = await AjaxUtil.post('../ajax/staff_operations.php', {
            action: 'reset_password',
            staff_id: staffId,
            new_password: newPassword
        });
        
        if (result.success) {
            AlertSystem.show(result.message, 'success');
        } else {
            AlertSystem.show(result.message, 'error');
        }
        
        return result;
    }
}

// Forecasting functions
class ForecastingManager {
    static async generateForecastData() {
        const result = await AjaxUtil.post('../ajax/forecasting.php', {
            action: 'generate_data'
        });
        
        if (result.success) {
            AlertSystem.show(result.message, 'success');
        } else {
            AlertSystem.show(result.message, 'error');
        }
        
        return result;
    }
    
    static async getForecast(bloodType, daysAhead = 30) {
        const result = await AjaxUtil.post('../ajax/forecasting.php', {
            action: 'get_forecast',
            blood_type: bloodType,
            days_ahead: daysAhead
        });
        
        return result;
    }
    
    static async getDashboardForecast(daysAhead = 30) {
        const result = await AjaxUtil.post('../ajax/forecasting.php', {
            action: 'get_dashboard_forecast',
            days_ahead: daysAhead
        });
        
        return result;
    }
}

// Utility functions
function formatDate(dateString) {
    const options = { year: 'numeric', month: 'short', day: 'numeric' };
    return new Date(dateString).toLocaleDateString(undefined, options);
}

function formatDateTime(dateString) {
    const options = { 
        year: 'numeric', 
        month: 'short', 
        day: 'numeric', 
        hour: '2-digit', 
        minute: '2-digit' 
    };
    return new Date(dateString).toLocaleDateString(undefined, options);
}

function calculateDaysUntil(dateString) {
    const targetDate = new Date(dateString);
    const today = new Date();
    const diffTime = targetDate - today;
    return Math.ceil(diffTime / (1000 * 60 * 60 * 24));
}

function formatNumber(number) {
    return new Intl.NumberFormat().format(number);
}

// Chart utilities
class ChartUtils {
    static createBloodTypeChart(canvasId, data) {
        const ctx = document.getElementById(canvasId).getContext('2d');
        
        return new Chart(ctx, {
            type: 'doughnut',
            data: {
                labels: data.map(item => item.blood_type),
                datasets: [{
                    data: data.map(item => item.available_units),
                    backgroundColor: [
                        '#FF6384', '#36A2EB', '#FFCE56', '#4BC0C0',
                        '#9966FF', '#FF9F40', '#FF6384', '#C9CBCF'
                    ],
                    borderWidth: 2,
                    borderColor: '#fff'
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: {
                            padding: 20,
                            usePointStyle: true
                        }
                    }
                }
            }
        });
    }
    
    static createLineChart(canvasId, data, label, color = '#36A2EB') {
        const ctx = document.getElementById(canvasId).getContext('2d');
        
        return new Chart(ctx, {
            type: 'line',
            data: {
                labels: data.map(item => item.date),
                datasets: [{
                    label: label,
                    data: data.map(item => item.value),
                    borderColor: color,
                    backgroundColor: color + '20',
                    fill: true,
                    tension: 0.4,
                    pointBackgroundColor: color,
                    pointBorderColor: '#fff',
                    pointBorderWidth: 2
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        beginAtZero: true,
                        grid: {
                            color: '#f0f0f0'
                        }
                    },
                    x: {
                        grid: {
                            color: '#f0f0f0'
                        }
                    }
                },
                plugins: {
                    legend: {
                        display: false
                    }
                }
            }
        });
    }
    
    static createBarChart(canvasId, data, label, color = '#667eea') {
        const ctx = document.getElementById(canvasId).getContext('2d');
        
        return new Chart(ctx, {
            type: 'bar',
            data: {
                labels: data.map(item => item.label),
                datasets: [{
                    label: label,
                    data: data.map(item => item.value),
                    backgroundColor: color,
                    borderColor: color,
                    borderWidth: 1,
                    borderRadius: 4
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        beginAtZero: true,
                        grid: {
                            color: '#f0f0f0'
                        }
                    },
                    x: {
                        grid: {
                            display: false
                        }
                    }
                },
                plugins: {
                    legend: {
                        display: false
                    }
                }
            }
        });
    }
}

// Initialize components when DOM is loaded
document.addEventListener('DOMContentLoaded', function() {
    // Initialize mobile sidebar toggle
    const sidebarToggle = document.querySelector('.sidebar-toggle');
    const sidebar = document.querySelector('.sidebar');
    
    if (sidebarToggle && sidebar) {
        sidebarToggle.addEventListener('click', function() {
            sidebar.classList.toggle('active');
        });
        
        // Close sidebar when clicking outside on mobile
        document.addEventListener('click', function(e) {
            if (window.innerWidth <= 768) {
                if (!sidebar.contains(e.target) && !sidebarToggle.contains(e.target)) {
                    sidebar.classList.remove('active');
                }
            }
        });
    }
    
    // Initialize data tables
    const dataTables = document.querySelectorAll('.data-table');
    dataTables.forEach((table, index) => {
        if (table.id) {
            new DataTable(table.id);
        }
    });
    
    // Initialize form validation
    const forms = document.querySelectorAll('form[data-validate]');
    forms.forEach(form => {
        form.addEventListener('submit', function(e) {
            FormValidator.clearFieldErrors();
            
            const requiredFields = JSON.parse(form.dataset.validate || '[]');
            const errors = FormValidator.validateRequired(requiredFields);
            
            if (errors.length > 0) {
                e.preventDefault();
                AlertSystem.show('Please fill in all required fields', 'error');
            }
        });
    });
    
    // Auto-hide alerts after page load
    const existingAlerts = document.querySelectorAll('.alert');
    existingAlerts.forEach(alert => {
        setTimeout(() => {
            if (alert.parentNode) {
                alert.style.opacity = '0';
                setTimeout(() => alert.remove(), 300);
            }
        }, 5000);
    });
});

// Export classes for global use
window.Modal = Modal;
window.AlertSystem = AlertSystem;
window.AjaxUtil = AjaxUtil;
window.FormValidator = FormValidator;
window.DataTable = DataTable;
window.BloodBankManager = BloodBankManager;
window.StaffManager = StaffManager;
window.ForecastingManager = ForecastingManager;
window.ChartUtils = ChartUtils;