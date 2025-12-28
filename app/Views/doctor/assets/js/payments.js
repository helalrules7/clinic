
document.addEventListener('DOMContentLoaded', function() {
    // Set current date and time as default
    const now = new Date();
    const year = now.getFullYear();
    const month = String(now.getMonth() + 1).padStart(2, '0');
    const day = String(now.getDate()).padStart(2, '0');
    const hours = String(now.getHours()).padStart(2, '0');
    const minutes = String(now.getMinutes()).padStart(2, '0');
    const localDateTime = `${year}-${month}-${day}T${hours}:${minutes}`;
    document.getElementById('balanceDate').value = localDateTime;
    document.getElementById('expenseDate').value = localDateTime;
    
    // Check if we need to open modals
    const urlParams = new URLSearchParams(window.location.search);
    if (urlParams.get('openModal') === 'dailyBalance') {
        setTimeout(() => {
            const balanceBtn = document.querySelector('[data-bs-target="#dailyBalanceModal"]');
            if (balanceBtn) {
                balanceBtn.click();
            }
            // Clean URL
            const newUrl = window.location.pathname + window.location.search.replace(/[?&]openModal=dailyBalance/, '').replace(/^&/, '?');
            window.history.replaceState({}, '', newUrl);
        }, 500);
    } else if (urlParams.get('openModal') === 'expense') {
        setTimeout(() => {
            const expenseBtn = document.querySelector('[data-bs-target="#expenseModal"]');
            if (expenseBtn) {
                expenseBtn.click();
            }
            // Clean URL
            const newUrl = window.location.pathname + window.location.search.replace(/[?&]openModal=expense/, '').replace(/^&/, '?');
            window.history.replaceState({}, '', newUrl);
        }, 500);
    }
    
    // Expense type badges functionality
    const expenseTypeBadges = document.querySelectorAll('.expense-type-badge');
    expenseTypeBadges.forEach(badge => {
        badge.addEventListener('click', function() {
            const expenseName = document.getElementById('expenseName');
            expenseName.value = this.dataset.type;
            
            // Update badge appearance
            expenseTypeBadges.forEach(b => b.classList.remove('bg-primary', 'text-white'));
            this.classList.add('bg-primary', 'text-white');
        });
    });
    
    // Daily balance form submission
    const dailyBalanceForm = document.getElementById('dailyBalanceForm');
    if (dailyBalanceForm) {
        dailyBalanceForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            if (!dailyBalanceForm.checkValidity()) {
                dailyBalanceForm.classList.add('was-validated');
                return;
            }
            
            const formData = new FormData(dailyBalanceForm);
            
            // Show loading state
            const submitButton = document.getElementById('balanceSubmit');
            const btnText = submitButton.querySelector('.btn-text');
            const spinner = submitButton.querySelector('.spinner-border');
            
            submitButton.disabled = true;
            btnText.textContent = 'Adding...';
            spinner.classList.remove('d-none');
            
            fetch('/api/daily-balance', {
                method: 'POST',
                body: formData,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            .then(response => response.json())
            .then(data => {
                submitButton.disabled = false;
                btnText.textContent = 'Add Balance';
                spinner.classList.add('d-none');
                
                if (data.ok) {
                    // Success
                    const messageEl = document.getElementById('dailyBalanceMessage');
                    messageEl.className = 'alert alert-success';
                    messageEl.textContent = 'Balance added successfully!';
                    messageEl.classList.remove('d-none');
                    
                    // Reset form
                    dailyBalanceForm.reset();
                    dailyBalanceForm.classList.remove('was-validated');
                    document.getElementById('balanceDate').value = localDateTime;
                    
                    // Close modal after delay
                    setTimeout(() => {
                        bootstrap.Modal.getInstance(document.getElementById('dailyBalanceModal')).hide();
                        // Update cards and transactions without full reload
                        updateDashboardCards();
                        loadFinancialTransactions();
                    }, 1500);
                } else {
                    // Error
                    const messageEl = document.getElementById('dailyBalanceMessage');
                    messageEl.className = 'alert alert-danger';
                    messageEl.textContent = data.error || 'Failed to add balance';
                    messageEl.classList.remove('d-none');
                }
            })
            .catch(error => {
                submitButton.disabled = false;
                btnText.textContent = 'Add Balance';
                spinner.classList.add('d-none');
                
                console.error('Error:', error);
                const messageEl = document.getElementById('dailyBalanceMessage');
                messageEl.className = 'alert alert-danger';
                messageEl.textContent = 'Error adding balance';
                messageEl.classList.remove('d-none');
            });
        });
    }
    
    // Expense form submission
    const expenseForm = document.getElementById('expenseForm');
    if (expenseForm) {
        expenseForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            if (!expenseForm.checkValidity()) {
                expenseForm.classList.add('was-validated');
                return;
            }
            
            const formData = new FormData(expenseForm);
            
            // Show loading state
            const submitButton = document.getElementById('expenseSubmit');
            const btnText = submitButton.querySelector('.btn-text');
            const spinner = submitButton.querySelector('.spinner-border');
            
            submitButton.disabled = true;
            btnText.textContent = 'Adding...';
            spinner.classList.remove('d-none');
            
            const jsonData = {
                amount: formData.get('amount'),
                expense_name: formData.get('expense_name'),
                category: formData.get('category'),
                notes: formData.get('notes'),
                expense_date: formData.get('expense_date')
            };
            
            fetch('/api/expenses', {
                method: 'POST',
                body: JSON.stringify(jsonData),
                headers: {
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                credentials: 'same-origin'
            })
            .then(response => response.json())
            .then(data => {
                submitButton.disabled = false;
                btnText.textContent = 'Add Expense';
                spinner.classList.add('d-none');
                
                if (data.ok) {
                    // Success
                    const messageEl = document.getElementById('expenseMessage');
                    messageEl.className = 'alert alert-success';
                    messageEl.textContent = 'Expense added successfully!';
                    messageEl.classList.remove('d-none');
                    
                    // Reset form
                    expenseForm.reset();
                    expenseForm.classList.remove('was-validated');
                    document.getElementById('expenseDate').value = localDateTime;
                    
                    // Reset badges
                    expenseTypeBadges.forEach(b => b.classList.remove('bg-primary', 'text-white'));
                    
                    // Close modal after delay
                    setTimeout(() => {
                        bootstrap.Modal.getInstance(document.getElementById('expenseModal')).hide();
                        // Update cards and transactions without full reload
                        updateDashboardCards();
                        loadFinancialTransactions();
                    }, 1500);
                } else {
                    // Error
                    const messageEl = document.getElementById('expenseMessage');
                    messageEl.className = 'alert alert-danger';
                    messageEl.textContent = data.error || 'Failed to add expense';
                    messageEl.classList.remove('d-none');
                }
            })
            .catch(error => {
                submitButton.disabled = false;
                btnText.textContent = 'Add Expense';
                spinner.classList.add('d-none');
                
                console.error('Error:', error);
                const messageEl = document.getElementById('expenseMessage');
                messageEl.className = 'alert alert-danger';
                messageEl.textContent = 'Error adding expense';
                messageEl.classList.remove('d-none');
            });
        });
    }
    
    // Edit Payment Form Submission
    const editPaymentForm = document.getElementById('editPaymentForm');
    if (editPaymentForm) {
        editPaymentForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            if (!editPaymentForm.checkValidity()) {
                editPaymentForm.classList.add('was-validated');
                return;
            }
            
            const formData = new FormData(editPaymentForm);
            const paymentId = document.getElementById('editPaymentId').value;
            
            // Show loading state
            const submitButton = document.getElementById('editPaymentSubmit');
            const btnText = submitButton.querySelector('.btn-text');
            const spinner = submitButton.querySelector('.spinner-border');
            
            submitButton.disabled = true;
            btnText.textContent = 'Updating...';
            spinner.classList.remove('d-none');
            
            const jsonData = {
                amount: formData.get('amount'),
                type: formData.get('type'),
                method: formData.get('method'),
                description: formData.get('description')
            };
            
            fetch(`/api/payments/${paymentId}`, {
                method: 'PUT',
                body: JSON.stringify(jsonData),
                headers: {
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                credentials: 'same-origin'
            })
            .then(response => response.json())
            .then(data => {
                submitButton.disabled = false;
                btnText.textContent = 'Update Payment';
                spinner.classList.add('d-none');
                
                if (data.ok) {
                    // Success
                    const messageEl = document.getElementById('editPaymentMessage');
                    messageEl.className = 'alert alert-success';
                    messageEl.textContent = 'Payment updated successfully!';
                    messageEl.classList.remove('d-none');
                    
                    // Close modal after delay
                    setTimeout(() => {
                        bootstrap.Modal.getInstance(document.getElementById('editPaymentModal')).hide();
                        // Update dashboard and transactions
                        updateDashboardCards();
                        loadFinancialTransactions();
                        location.reload();
                    }, 1500);
                } else {
                    // Error
                    const messageEl = document.getElementById('editPaymentMessage');
                    messageEl.className = 'alert alert-danger';
                    messageEl.textContent = data.error || 'Failed to update payment';
                    messageEl.classList.remove('d-none');
                }
            })
            .catch(error => {
                submitButton.disabled = false;
                btnText.textContent = 'Update Payment';
                spinner.classList.add('d-none');
                
                console.error('Error:', error);
                const messageEl = document.getElementById('editPaymentMessage');
                messageEl.className = 'alert alert-danger';
                messageEl.textContent = 'Error updating payment';
                messageEl.classList.remove('d-none');
            });
        });
    }
    
    // Edit Expense Form Submission
    const editExpenseForm = document.getElementById('editExpenseForm');
    if (editExpenseForm) {
        editExpenseForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            if (!editExpenseForm.checkValidity()) {
                editExpenseForm.classList.add('was-validated');
                return;
            }
            
            const formData = new FormData(editExpenseForm);
            const expenseId = document.getElementById('editExpenseId').value;
            
            // Show loading state
            const submitButton = document.getElementById('editExpenseSubmit');
            const btnText = submitButton.querySelector('.btn-text');
            const spinner = submitButton.querySelector('.spinner-border');
            
            submitButton.disabled = true;
            btnText.textContent = 'Updating...';
            spinner.classList.remove('d-none');
            
            const jsonData = {
                amount: formData.get('amount'),
                expense_name: formData.get('expense_name'),
                category: formData.get('category'),
                notes: formData.get('notes'),
                expense_date: formData.get('expense_date')
            };
            
            fetch(`/api/expenses/${expenseId}`, {
                method: 'PUT',
                body: JSON.stringify(jsonData),
                headers: {
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                credentials: 'same-origin'
            })
            .then(response => response.json())
            .then(data => {
                submitButton.disabled = false;
                btnText.textContent = 'Update Expense';
                spinner.classList.add('d-none');
                
                if (data.ok) {
                    // Success
                    const messageEl = document.getElementById('editExpenseMessage');
                    messageEl.className = 'alert alert-success';
                    messageEl.textContent = 'Expense updated successfully!';
                    messageEl.classList.remove('d-none');
                    
                    // Close modal after delay
                    setTimeout(() => {
                        bootstrap.Modal.getInstance(document.getElementById('editExpenseModal')).hide();
                        // Update dashboard and transactions
                        updateDashboardCards();
                        loadFinancialTransactions();
                        location.reload();
                    }, 1500);
                } else {
                    // Error
                    const messageEl = document.getElementById('editExpenseMessage');
                    messageEl.className = 'alert alert-danger';
                    messageEl.textContent = data.error || 'Failed to update expense';
                    messageEl.classList.remove('d-none');
                }
            })
            .catch(error => {
                console.error('Error updating expense:', error);
                
                submitButton.disabled = false;
                btnText.textContent = 'Update Expense';
                spinner.classList.add('d-none');
                
                const messageEl = document.getElementById('editExpenseMessage');
                messageEl.className = 'alert alert-danger';
                messageEl.textContent = 'Error updating expense: ' + error.message;
                messageEl.classList.remove('d-none');
            });
        });
    }
    
    // Load financial transactions
    loadFinancialTransactions();
    
    // Transaction filters
    const dateFilter = document.getElementById('dateFilter');
    const transactionTypeFilter = document.getElementById('transactionTypeFilter');
    
    if (dateFilter) {
        dateFilter.addEventListener('change', function() {
            loadFinancialTransactions();
        });
    }
    
    if (transactionTypeFilter) {
        transactionTypeFilter.addEventListener('change', function() {
            loadFinancialTransactions();
        });
    }
    
    // Keyboard shortcuts
    document.addEventListener('keydown', function(e) {
        const isModalOpen = document.querySelector('.modal.show');
        const isInputFocused = ['INPUT', 'TEXTAREA', 'SELECT'].includes(e.target.tagName) || 
                             e.target.contentEditable === 'true';
        
        // Open daily balance modal with 'B' key
        if (e.key.toLowerCase() === 'b' && !isInputFocused && !isModalOpen) {
            e.preventDefault();
            document.querySelector('[data-bs-target="#dailyBalanceModal"]').click();
        }
        
        // Open expense modal with 'E' key
        if (e.key.toLowerCase() === 'e' && !isInputFocused && !isModalOpen) {
            e.preventDefault();
            document.querySelector('[data-bs-target="#expenseModal"]').click();
        }
        
        // Open search modal with 'S' key
        if (e.key.toLowerCase() === 's' && !isInputFocused && !isModalOpen) {
            e.preventDefault();
            document.querySelector('[data-bs-target="#searchModal"]').click();
        }
        
        // Close modals with 'Escape' key
        if (e.key === 'Escape') {
            const openModal = document.querySelector('.modal.show');
            if (openModal) {
                bootstrap.Modal.getInstance(openModal).hide();
            }
        }
    });
});

// Financial transactions management
let currentPage = 1;
const itemsPerPage = 10;

function loadFinancialTransactions(page = 1) {
    currentPage = page;
    
    const dateFilter = document.getElementById('dateFilter').value;
    const transactionTypeFilter = document.getElementById('transactionTypeFilter').value;
    
    const params = new URLSearchParams({
        page: page,
        limit: itemsPerPage,
        date: dateFilter,
        type: transactionTypeFilter
    });
    
    fetch(`/api/financial-transactions?${params}`)
        .then(response => response.json())
        .then(data => {
            if (data.ok) {
                displayTransactions(data.data.transactions);
                updatePagination(data.data.pagination);
            } else {
                console.error('Error loading transactions:', data.error);
            }
        })
        .catch(error => {
            console.error('Error:', error);
        });
}

function displayTransactions(transactions) {
    const tbody = document.getElementById('transactionsTableBody');
    
    if (transactions.length === 0) {
        tbody.innerHTML = `
            <tr>
                <td colspan="6" class="text-center py-4">
                    <i class="bi bi-journal-text text-muted" style="font-size: 3rem;"></i>
                    <p class="text-muted mt-2 mb-0">No transactions found</p>
                </td>
            </tr>
        `;
        return;
    }
    
    let html = '';
    transactions.forEach(transaction => {
        const typeBadge = getTransactionTypeBadge(transaction.type);
        const amountClass = transaction.type === 'expense' ? 'text-danger' : 'text-success';
        const amountPrefix = transaction.type === 'expense' ? '-' : '+';
        
        html += `
            <tr>
                <td>${formatDateTime(transaction.created_at)}</td>
                <td>${typeBadge}</td>
                <td>${transaction.description}</td>
                <td>
                    <span class="fw-bold ${amountClass}">
                        ${amountPrefix}${formatMoney(transaction.amount)}
                    </span>
                </td>
                <td>
                    <span class="fw-bold text-primary">${formatMoney(transaction.balance)}</span>
                </td>
                <td>
                    <div class="btn-group" role="group">
                        ${getTransactionActions(transaction)}
                    </div>
                </td>
            </tr>
        `;
    });
    
    tbody.innerHTML = html;
}

function getTransactionTypeBadge(type) {
    const badges = {
        'payment': '<span class="badge bg-success">Payment</span>',
        'expense': '<span class="badge bg-danger">Expense</span>',
        'balance': '<span class="badge bg-info">Balance</span>'
    };
    return badges[type] || '<span class="badge bg-secondary">Unknown</span>';
}

function getTransactionActions(transaction) {
    let actions = '';
    
    if (transaction.type === 'payment') {
        actions += `
            <button type="button" class="btn btn-outline-primary btn-sm" 
                    onclick="viewPayment(${transaction.id})"
                    title="View Payment Details">
                <i class="bi bi-eye"></i>
            </button>
            <button type="button" class="btn btn-outline-info btn-sm" 
                    onclick="printReceipt(${transaction.id})"
                    title="Print Receipt">
                <i class="bi bi-printer"></i>
            </button>
            <button type="button" class="btn btn-outline-warning btn-sm" 
                    onclick="editPayment(${transaction.id})"
                    title="Edit Payment">
                <i class="bi bi-pencil"></i>
            </button>
            <button type="button" class="btn btn-outline-danger btn-sm" 
                    onclick="deletePayment(${transaction.id})"
                    title="Delete Payment">
                <i class="bi bi-trash"></i>
            </button>
        `;
    } else if (transaction.type === 'expense') {
        actions += `
            <button type="button" class="btn btn-outline-warning btn-sm" 
                    onclick="editExpense(${transaction.id})"
                    title="Edit Expense">
                <i class="bi bi-pencil"></i>
            </button>
            <button type="button" class="btn btn-outline-danger btn-sm" 
                    onclick="deleteExpense(${transaction.id})"
                    title="Delete Expense">
                <i class="bi bi-trash"></i>
            </button>
        `;
    }
    
    return actions;
}

function updatePagination(pagination) {
    document.getElementById('showingFrom').textContent = pagination.from;
    document.getElementById('showingTo').textContent = pagination.to;
    document.getElementById('totalRecords').textContent = pagination.total;
    
    const paginationContainer = document.getElementById('transactionsPagination');
    let html = '';
    
    // Previous button
    if (pagination.current_page > 1) {
        html += `
            <li class="page-item">
                <a class="page-link" href="#" onclick="loadFinancialTransactions(${pagination.current_page - 1})">Previous</a>
            </li>
        `;
    }
    
    // Page numbers
    const startPage = Math.max(1, pagination.current_page - 2);
    const endPage = Math.min(pagination.last_page, pagination.current_page + 2);
    
    for (let i = startPage; i <= endPage; i++) {
        html += `
            <li class="page-item ${i === pagination.current_page ? 'active' : ''}">
                <a class="page-link" href="#" onclick="loadFinancialTransactions(${i})">${i}</a>
            </li>
        `;
    }
    
    // Next button
    if (pagination.current_page < pagination.last_page) {
        html += `
            <li class="page-item">
                <a class="page-link" href="#" onclick="loadFinancialTransactions(${pagination.current_page + 1})">Next</a>
            </li>
        `;
    }
    
    paginationContainer.innerHTML = html;
}

function formatDateTime(dateTime) {
    const date = new Date(dateTime);
    return date.toLocaleDateString('en-US') + ' ' + date.toLocaleTimeString('en-US', {hour: '2-digit', minute: '2-digit'});
}

function formatMoney(amount) {
    return new Intl.NumberFormat('en-US', {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2
    }).format(amount) + ' EGP';
}

function exportToExcel() {
    const dateFilter = document.getElementById('dateFilter').value;
    const transactionTypeFilter = document.getElementById('transactionTypeFilter').value;
    
    // Show loading state
    const exportBtn = document.querySelector('[onclick="exportToExcel()"]');
    const originalText = exportBtn.innerHTML;
    exportBtn.innerHTML = '<i class="bi bi-hourglass-split me-1"></i>Exporting...';
    exportBtn.disabled = true;
    
    const params = new URLSearchParams({
        date: dateFilter,
        type: transactionTypeFilter
    });
    
    // Use window.open for direct download
    const exportUrl = `/api/financial-transactions/export?${params}`;
    window.open(exportUrl, '_blank');
    
    // Reset button
    setTimeout(() => {
        exportBtn.innerHTML = originalText;
        exportBtn.disabled = false;
        
        // Show success message
        showNotification('File exported successfully!', 'success');
    }, 1000);
}

// Notification function
function showNotification(message, type = 'info') {
    // Create notification element
    const notification = document.createElement('div');
    notification.className = `alert alert-${type === 'error' ? 'danger' : type === 'success' ? 'success' : 'info'} alert-dismissible fade show position-fixed`;
    notification.style.cssText = 'top: 20px; right: 20px; z-index: 9999; min-width: 300px;';
    notification.innerHTML = `
        ${message}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    `;
    
    document.body.appendChild(notification);
    
    // Auto remove after 5 seconds
    setTimeout(() => {
        if (notification.parentNode) {
            notification.parentNode.removeChild(notification);
        }
    }, 5000);
}

function updateDashboardCards() {
    fetch('/api/dashboard-summary', {
        credentials: 'same-origin'
    })
        .then(response => response.json())
        .then(data => {
            if (data.ok) {
                // Update daily balance cards
                if (data.data.dailyBalance) {
                    const openingBalanceEl = document.getElementById('openingBalance');
                    const totalReceivedEl = document.getElementById('totalReceived');
                    const currentBalanceEl = document.getElementById('currentBalance');
                    const transactionsCountEl = document.getElementById('transactionsCount');
                    
                    if (openingBalanceEl) openingBalanceEl.textContent = formatMoney(data.data.dailyBalance.opening_balance) + ' EGP';
                    if (totalReceivedEl) totalReceivedEl.textContent = formatMoney(data.data.dailyBalance.total_received) + ' EGP';
                    if (currentBalanceEl) currentBalanceEl.textContent = formatMoney(data.data.dailyBalance.current_balance) + ' EGP';
                    if (transactionsCountEl) transactionsCountEl.textContent = data.data.dailyBalance.transactions_count;
                }
                
                // Update payment types summary
                if (data.data.paymentTypes) {
                    Object.keys(data.data.paymentTypes).forEach(type => {
                        const element = document.getElementById(type + 'Count');
                        if (element) {
                            element.textContent = data.data.paymentTypes[type].count;
                        }
                    });
                }
            }
        })
        .catch(error => {
            console.error('Error updating dashboard cards:', error);
        });
}

// Action functions
function viewPayment(paymentId) {
    window.open(`/secretary/payments/${paymentId}`, '_blank');
}

function printReceipt(paymentId) {
    window.open(`/secretary/payments/${paymentId}/receipt`, '_blank');
}

function editPayment(paymentId) {
    // Fetch payment data and populate the edit modal
    fetch(`/api/payments/${paymentId}`, {
        credentials: 'same-origin'
    })
        .then(response => response.json())
        .then(data => {
            if (data.ok) {
                // Populate the edit form
                document.getElementById('editPaymentId').value = data.data.id;
                document.getElementById('editPaymentAmount').value = data.data.amount;
                document.getElementById('editPaymentType').value = data.data.type;
                document.getElementById('editPaymentMethod').value = data.data.method;
                document.getElementById('editPaymentDescription').value = data.data.description || '';
                
                // Format date for datetime-local input
                if (data.data.created_at) {
                    const date = new Date(data.data.created_at);
                    const year = date.getFullYear();
                    const month = String(date.getMonth() + 1).padStart(2, '0');
                    const day = String(date.getDate()).padStart(2, '0');
                    const hours = String(date.getHours()).padStart(2, '0');
                    const minutes = String(date.getMinutes()).padStart(2, '0');
                    const localDateTime = `${year}-${month}-${day}T${hours}:${minutes}`;
                    document.getElementById('editPaymentDate').value = localDateTime;
                }
                
                // Show the modal
                const modal = new bootstrap.Modal(document.getElementById('editPaymentModal'));
                modal.show();
            } else {
                showErrorModal('Error loading payment data: ' + (data.error || 'Unknown error'));
            }
        })
        .catch(error => {
            console.error('Error loading payment data:', error);
            showErrorModal('Error loading payment data: ' + error.message);
        });
}

function deletePayment(paymentId) {
    showDeleteConfirmation('payment', paymentId, 'Are you sure you want to delete this payment?', 'This action cannot be undone.');
}

function editExpense(expenseId) {
    // Fetch expense data and populate the edit modal
    fetch(`/api/expenses/${expenseId}`, {
        credentials: 'same-origin'
    })
        .then(response => response.json())
        .then(data => {
            if (data.ok) {
                // Populate the edit form
                document.getElementById('editExpenseId').value = data.data.id;
                document.getElementById('editExpenseAmount').value = data.data.amount;
                document.getElementById('editExpenseName').value = data.data.expense_name;
                document.getElementById('editExpenseCategory').value = data.data.category;
                document.getElementById('editExpenseNotes').value = data.data.notes || '';
                
                // Format date for datetime-local input
                if (data.data.created_at) {
                    const date = new Date(data.data.created_at);
                    const year = date.getFullYear();
                    const month = String(date.getMonth() + 1).padStart(2, '0');
                    const day = String(date.getDate()).padStart(2, '0');
                    const hours = String(date.getHours()).padStart(2, '0');
                    const minutes = String(date.getMinutes()).padStart(2, '0');
                    const localDateTime = `${year}-${month}-${day}T${hours}:${minutes}`;
                    document.getElementById('editExpenseDate').value = localDateTime;
                }
                
                // Show the modal
                const modal = new bootstrap.Modal(document.getElementById('editExpenseModal'));
                modal.show();
            } else {
                showErrorModal('Error loading expense data: ' + (data.error || 'Unknown error'));
            }
        })
        .catch(error => {
            console.error('Error loading expense data:', error);
            showErrorModal('Error loading expense data: ' + error.message);
        });
}

function deleteExpense(expenseId) {
    showDeleteConfirmation('expense', expenseId, 'Are you sure you want to delete this expense?', 'This action cannot be undone.');
}

function filterPaymentsByType(type) {
    const rows = document.querySelectorAll('#paymentsTableBody tr[data-type]');
    
    rows.forEach(row => {
        if (type === 'all' || row.dataset.type === type) {
            row.style.display = '';
        } else {
            row.style.display = 'none';
        }
    });
}

function performSearch() {
    // TODO: Implement search functionality
    showInfoModal('Search', 'Search functionality will be implemented soon', 'info');
}

// Modal Functions
function showDeleteConfirmation(type, id, message, details) {
    document.getElementById('deleteConfirmationMessage').textContent = message;
    document.getElementById('deleteConfirmationDetails').textContent = details;
    
    const modal = new bootstrap.Modal(document.getElementById('deleteConfirmationModal'));
    modal.show();
    
    // Store the delete action
    document.getElementById('confirmDeleteBtn').onclick = function() {
        executeDelete(type, id);
        modal.hide();
    };
}

function executeDelete(type, id) {
    const endpoint = type === 'payment' ? `/api/payments/${id}` : `/api/expenses/${id}`;
    
    fetch(endpoint, {
        method: 'DELETE',
        headers: {
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.ok) {
            showSuccessModal(`${type.charAt(0).toUpperCase() + type.slice(1)} deleted successfully`);
            // Update dashboard and transactions
            updateDashboardCards();
            loadFinancialTransactions();
            // Reload payments table
            location.reload();
        } else {
            showErrorModal(`Error deleting ${type}: ${data.error || 'Unknown error'}`);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showErrorModal(`Error deleting ${type}: ${error.message}`);
    });
}

function showInfoModal(title, message, type = 'info') {
    const iconClass = type === 'info' ? 'bi-info-circle text-info' : 
                     type === 'warning' ? 'bi-exclamation-triangle text-warning' : 
                     type === 'success' ? 'bi-check-circle text-success' : 
                     'bi-info-circle text-info';
    
    document.getElementById('infoModalTitle').innerHTML = `<i class="bi ${iconClass} me-2"></i>${title}`;
    document.getElementById('infoModalMessage').textContent = message;
    
    const modal = new bootstrap.Modal(document.getElementById('infoModal'));
    modal.show();
}

function showSuccessModal(message) {
    showInfoModal('Success', message, 'success');
}

function showErrorModal(message) {
    showInfoModal('Error', message, 'error');
}

// =========================================
// Custom Select Menu Logic
// =========================================

// Custom Select Menu Logic
function initCustomSelects() {
    const customSelects = document.querySelectorAll('.field.menu:not([data-initialized])');

    customSelects.forEach(field => {
        const select = field.querySelector('select');
        const button = field.querySelector('.custom-select-toggle');
        const menu = field.querySelector('menu');
        const options = menu ? menu.querySelectorAll('li') : [];

        if (!select || !button || !menu || options.length === 0) {
            console.warn('Missing elements for custom select initialization:', field);
            return;
        }
        
        // Mark as initialized to prevent duplicate event listeners
        field.setAttribute('data-initialized', 'true');

        // Set initial button text
        const selectedOption = select.options[select.selectedIndex];
        if (selectedOption) {
            const correspondingLi = Array.from(options).find(li => li.dataset.option === selectedOption.value);
            if (correspondingLi) {
                button.textContent = correspondingLi.querySelector('h3')?.textContent || selectedOption.textContent;
                correspondingLi.classList.add('selected');
            } else {
                button.textContent = selectedOption.textContent;
            }
        } else {
            button.textContent = 'Select an option';
        }

        function openMenu() {
            // Close any other open menus first
            document.querySelectorAll('.field.menu.open').forEach(openField => {
                if (openField !== field) {
                    const openButton = openField.querySelector('.custom-select-toggle');
                    openField.classList.remove('open');
                    if (openButton) openButton.setAttribute('aria-expanded', 'false');
                    const openParent = openField.closest('.mb-3, .modal-body, .d-flex, .card-header, .col-12, .card');
                    if (openParent && !openParent.classList.contains('modal')) {
                        openParent.style.zIndex = '';
                        openParent.style.position = '';
                    } else {
                        const openModal = openField.closest('.modal');
                        if (openModal) {
                            openModal.style.zIndex = '';
                        }
                    }
                }
            });

            field.classList.add('open');
            button.setAttribute('aria-expanded', 'true');

            // Fix z-index issue by elevating parent containers manually
            const parent = field.closest('.mb-3, .modal-body, .d-flex, .card-header, .col-12, .card');
            if (parent && !parent.classList.contains('modal')) {
                parent.style.zIndex = '1000002';
                parent.style.position = 'relative';
            } else {
                const modal = field.closest('.modal');
                if (modal) {
                    modal.style.zIndex = '1000002';
                }
            }

            const selected = menu.querySelector('.selected') || options[0];
            if (selected) {
                selected.focus();
                
                // Scroll to selected item if menu has many options
                setTimeout(() => {
                    selected.scrollIntoView({
                        behavior: 'smooth',
                        block: 'center',
                        inline: 'nearest'
                    });
                }, 150);
            }
        }

        function closeMenu() {
            field.classList.remove('open');
            button.setAttribute('aria-expanded', 'false');
            if (document.activeElement === document.body || document.activeElement === null) {
                button.focus();
            }

            const parent = field.closest('.mb-3, .modal-body, .d-flex, .card-header, .col-12, .card');
            if (parent && !parent.classList.contains('modal')) {
                setTimeout(() => {
                    if (!field.classList.contains('open')) {
                        parent.style.zIndex = '';
                        parent.style.position = '';
                    }
                }, 300);
            } else {
                const modal = field.closest('.modal');
                if (modal) {
                    setTimeout(() => {
                        if (!field.classList.contains('open')) {
                            modal.style.zIndex = '';
                        }
                    }, 300);
                }
            }
        }

        function setOption(optionEl) {
            const value = optionEl.dataset.option;
            const text = optionEl.querySelector('h3')?.textContent || optionEl.textContent;

            select.value = value;
            select.dispatchEvent(new Event('change'));

            button.textContent = text;

            options.forEach(el => el.classList.remove('selected'));
            optionEl.classList.add('selected');

            closeMenu();
        }

        button.addEventListener('click', (e) => {
            e.stopPropagation();
            e.preventDefault();
            if (field.classList.contains('open')) {
                closeMenu();
            } else {
                openMenu();
            }
        });

        button.addEventListener('keydown', (e) => {
            if (e.key === 'ArrowDown' || e.key === 'Enter' || e.key === ' ') {
                e.preventDefault();
                openMenu();
            }
        });

        // Prevent clicks on menu from closing modal
        menu.addEventListener('click', (e) => {
            e.stopPropagation();
        });

        options.forEach(option => {
            option.addEventListener('click', (e) => {
                e.stopPropagation();
                e.preventDefault();
                setOption(option);
            });

            option.addEventListener('keydown', (e) => {
                if (e.key === 'Enter' || e.key === ' ') {
                    e.preventDefault();
                    setOption(option);
                } else if (e.key === 'ArrowDown') {
                    e.preventDefault();
                    const next = option.nextElementSibling;
                    if (next) next.focus();
                } else if (e.key === 'ArrowUp') {
                    e.preventDefault();
                    const prev = option.previousElementSibling;
                    if (prev) prev.focus();
                } else if (e.key === 'Escape') {
                    e.preventDefault();
                    closeMenu();
                }
            });
        });

        // Close menu when clicking outside, but prevent modal from closing
        const handleOutsideClick = (e) => {
            const target = e.target;
            const isInteractiveElement = target.tagName === 'INPUT' || 
                                        target.tagName === 'TEXTAREA' || 
                                        target.tagName === 'SELECT' ||
                                        target.isContentEditable ||
                                        target.closest('input, textarea, select, [contenteditable]');
            
            if (isInteractiveElement) {
                return;
            }
            
            if (field.classList.contains('open') && !field.contains(target)) {
                const modal = field.closest('.modal');
                if (modal && target === modal) {
                    e.stopPropagation();
                    e.preventDefault();
                    return;
                }
                closeMenu();
            }
        };
        
        // Store handler for cleanup
        field._outsideClickHandler = handleOutsideClick;
        document.addEventListener('click', handleOutsideClick, false);
    });
}

// Initialize on DOM ready (after existing DOMContentLoaded)
document.addEventListener('DOMContentLoaded', function() {
    setTimeout(() => {
        initCustomSelects();
    }, 100);
});

// Also initialize when modals are shown
document.addEventListener('shown.bs.modal', function(e) {
    const modal = e.target;
    setTimeout(() => {
        initCustomSelects();
    }, 100);
});