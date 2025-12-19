// LBMS - Library Book Management System JavaScript

// Global variables
const LBMS = {
    baseUrl: window.location.origin,
    csrfToken: document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
};

// Utility functions
const Utils = {
    // Show loading state
    showLoading(element) {
        element.classList.add('loading');
        element.style.position = 'relative';
    },

    // Hide loading state
    hideLoading(element) {
        element.classList.remove('loading');
    },

    // Show toast notification
    showToast(message, type = 'info') {
        const toastHtml = `
            <div class="toast align-items-center text-white bg-${type} border-0" role="alert">
                <div class="d-flex">
                    <div class="toast-body">
                        ${message}
                    </div>
                    <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
                </div>
            </div>
        `;

        const toastContainer = this.getOrCreateToastContainer();
        const toastElement = document.createElement('div');
        toastElement.innerHTML = toastHtml;
        toastContainer.appendChild(toastElement);

        const toast = new bootstrap.Toast(toastElement.querySelector('.toast'));
        toast.show();

        // Remove toast element after hidden
        toastElement.addEventListener('hidden.bs.toast', () => {
            toastElement.remove();
        });
    },

    // Get or create toast container
    getOrCreateToastContainer() {
        let container = document.getElementById('toastContainer');
        if (!container) {
            container = document.createElement('div');
            container.id = 'toastContainer';
            container.className = 'toast-container position-fixed bottom-0 end-0 p-3';
            container.style.zIndex = '1055';
            document.body.appendChild(container);
        }
        return container;
    },

    // Format date
    formatDate(date, format = 'YYYY-MM-DD') {
        const d = new Date(date);
        const year = d.getFullYear();
        const month = String(d.getMonth() + 1).padStart(2, '0');
        const day = String(d.getDate()).padStart(2, '0');

        return format
            .replace('YYYY', year)
            .replace('MM', month)
            .replace('DD', day);
    },

    // Format currency
    formatCurrency(amount, currency = 'USD') {
        return new Intl.NumberFormat('en-US', {
            style: 'currency',
            currency: currency
        }).format(amount);
    },

    // Debounce function
    debounce(func, wait) {
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
};

// API helper
const API = {
    // Make AJAX request
    async request(url, options = {}) {
        const defaultOptions = {
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            }
        };

        const finalOptions = { ...defaultOptions, ...options };

        // Add CSRF token if available
        if (LBMS.csrfToken) {
            finalOptions.headers['X-CSRF-TOKEN'] = LBMS.csrfToken;
        }

        try {
            const response = await fetch(url, finalOptions);
            const data = await response.json();

            if (!response.ok) {
                throw new Error(data.message || 'Request failed');
            }

            return data;
        } catch (error) {
            console.error('API Error:', error);
            Utils.showToast(error.message, 'danger');
            throw error;
        }
    },

    // GET request
    async get(url) {
        return this.request(url, { method: 'GET' });
    },

    // POST request
    async post(url, data) {
        return this.request(url, {
            method: 'POST',
            body: JSON.stringify(data)
        });
    },

    // PUT request
    async put(url, data) {
        return this.request(url, {
            method: 'PUT',
            body: JSON.stringify(data)
        });
    },

    // DELETE request
    async delete(url) {
        return this.request(url, { method: 'DELETE' });
    }
};

// Form helpers
const FormHelper = {
    // Serialize form data
    serialize(form) {
        const formData = new FormData(form);
        const data = {};

        for (let [key, value] of formData.entries()) {
            if (data[key]) {
                // Handle multiple values with same name
                if (Array.isArray(data[key])) {
                    data[key].push(value);
                } else {
                    data[key] = [data[key], value];
                }
            } else {
                data[key] = value;
            }
        }

        return data;
    },

    // Validate required fields
    validateRequired(form) {
        const requiredFields = form.querySelectorAll('[required]');
        const errors = [];

        requiredFields.forEach(field => {
            if (!field.value.trim()) {
                errors.push(`${field.name || field.id} is required`);
                field.classList.add('is-invalid');
            } else {
                field.classList.remove('is-invalid');
            }
        });

        return {
            isValid: errors.length === 0,
            errors
        };
    },

    // Reset form
    reset(form) {
        form.reset();
        form.querySelectorAll('.is-invalid, .is-valid').forEach(field => {
            field.classList.remove('is-invalid', 'is-valid');
        });
    }
};

// Book management functions
const BookManager = {
    // Delete book
    async deleteBook(bookId) {
        if (!confirm('Are you sure you want to delete this book?')) {
            return;
        }

        try {
            Utils.showToast('Deleting book...', 'info');

            const response = await API.delete(`/api/books/${bookId}`);

            if (response.success) {
                Utils.showToast('Book deleted successfully', 'success');
                // Reload the page or remove from DataTable
                window.location.reload();
            } else {
                Utils.showToast(response.message || 'Failed to delete book', 'danger');
            }
        } catch (error) {
            Utils.showToast('Error deleting book', 'danger');
        }
    },

    // Borrow book
    async borrowBook(bookId, memberId, dueDate) {
        try {
            Utils.showToast('Processing loan...', 'info');

            const response = await API.post('/api/loans', {
                book_id: bookId,
                member_id: memberId,
                due_date: dueDate
            });

            if (response.success) {
                Utils.showToast('Book borrowed successfully', 'success');
                bootstrap.Modal.getInstance(document.getElementById('borrowModal')).hide();
                window.location.reload();
            } else {
                Utils.showToast(response.message || 'Failed to borrow book', 'danger');
            }
        } catch (error) {
            Utils.showToast('Error borrowing book', 'danger');
        }
    },

    // Return book
    async returnBook(loanId) {
        if (!confirm('Mark this book as returned?')) {
            return;
        }

        try {
            Utils.showToast('Processing return...', 'info');

            const response = await API.post(`/api/loans/${loanId}/return`);

            if (response.success) {
                Utils.showToast('Book returned successfully', 'success');
                window.location.reload();
            } else {
                Utils.showToast(response.message || 'Failed to return book', 'danger');
            }
        } catch (error) {
            Utils.showToast('Error returning book', 'danger');
        }
    }
};

// DataTables helper
const DataTableHelper = {
    // Initialize DataTable with common options
    init(tableId, options = {}) {
        const defaultOptions = {
            responsive: true,
            pageLength: 25,
            language: {
                search: "Search:",
                lengthMenu: "Show _MENU_ entries per page",
                info: "Showing _START_ to _END_ of _TOTAL_ entries",
                infoEmpty: "Showing 0 to 0 of 0 entries",
                infoFiltered: "(filtered from _MAX_ total entries)",
                paginate: {
                    first: "First",
                    last: "Last",
                    next: "Next",
                    previous: "Previous"
                }
            },
            dom: '<"row"<"col-sm-12 col-md-6"l><"col-sm-12 col-md-6"f>>' +
                 '<"row"<"col-sm-12"tr>>' +
                 '<"row"<"col-sm-12 col-md-5"i><"col-sm-12 col-md-7"p>>'
        };

        return $(`#${tableId}`).DataTable({ ...defaultOptions, ...options });
    }
};

// Initialize on DOM ready
$(document).ready(function() {
    // Initialize tooltips
    const tooltipTriggerList = [].slice.call($('[data-bs-toggle="tooltip"]'));
    tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });

    // Initialize popovers
    const popoverTriggerList = [].slice.call($('[data-bs-toggle="popover"]'));
    popoverTriggerList.map(function (popoverTriggerEl) {
        return new bootstrap.Popover(popoverTriggerEl);
    });

    // Auto-hide flash messages after 5 seconds
    setTimeout(function() {
        $('.alert:not(.alert-danger)').each(function() {
            const bsAlert = new bootstrap.Alert(this);
            bsAlert.close();
        });
    }, 5000);

    // Handle form submissions with AJAX if they have data-ajax="true"
    $('form[data-ajax="true"]').on('submit', async function(e) {
        e.preventDefault();

        const form = $(this);
        const validation = FormHelper.validateRequired(form[0]);
        if (!validation.isValid) {
            Utils.showToast('Please fill in all required fields', 'warning');
            return;
        }

        const submitBtn = form.find('button[type="submit"]');
        const originalText = submitBtn.text();

        Utils.showLoading(submitBtn[0]);
        submitBtn.text('Processing...');
        submitBtn.prop('disabled', true);

        try {
            const formData = FormHelper.serialize(form[0]);
            const method = form.attr('method') || 'POST';
            const url = form.attr('action');

            let response;
            if (method.toUpperCase() === 'POST') {
                response = await API.post(url, formData);
            } else if (method.toUpperCase() === 'PUT') {
                response = await API.put(url, formData);
            }

            if (response.success) {
                Utils.showToast(response.message || 'Operation successful', 'success');

                // Redirect or reload if specified
                if (form.data('redirect')) {
                    window.location.href = form.data('redirect');
                } else if (form.data('reload') !== 'false') {
                    window.location.reload();
                }
            } else {
                Utils.showToast(response.message || 'Operation failed', 'danger');
            }
        } catch (error) {
            Utils.showToast('An error occurred', 'danger');
        } finally {
            Utils.hideLoading(submitBtn[0]);
            submitBtn.text(originalText);
            submitBtn.prop('disabled', false);
        }
    });

    // Handle search/filter form submissions with debouncing
    $('#filterForm, .search-form').each(function() {
        const form = $(this);
        let searchTimeout;

        const handleSearch = Utils.debounce(function() {
            form[0].submit();
        }, 500);

        const searchInput = form.find('input[name="search"]');
        if (searchInput.length) {
            searchInput.on('input', handleSearch);
        }
    });

    console.log('LBMS Application initialized');
});

// Export for global access
window.LBMS = LBMS;
window.Utils = Utils;
window.API = API;
window.FormHelper = FormHelper;
window.BookManager = BookManager;
window.DataTableHelper = DataTableHelper;