/**
 * Activity Logs Page JavaScript
 * Handles filtering, search, export, and table interactions
 */

document.addEventListener('DOMContentLoaded', function() {
    initializeActivityLogs();
});

function initializeActivityLogs() {
    setupFilterForm();
    setupClearFilters();
    setupExport();
    setupSearch();
    setupSorting();
    setupAutoRefresh();
    setupTableInteractions();
    updateSortIndicators();
}

function setupFilterForm() {
    const filterForm = document.getElementById('filterForm');
    if (!filterForm) return;
    filterForm.addEventListener('submit', function(e) {
        const submitBtn = filterForm.querySelector('button[type="submit"]');
        if (submitBtn) {
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>Loading...';
        }
    });
}

function setupClearFilters() {
    const clearBtn = document.getElementById('clearFilters');
    if (!clearBtn) return;
    clearBtn.addEventListener('click', function(e) {
        e.preventDefault();
        document.getElementById('userFilter').value = '';
        document.getElementById('typeFilter').value = '';
        document.getElementById('activityFilter').value = '';
        document.getElementById('dateFrom').value = '';
        document.getElementById('dateTo').value = '';
        document.getElementById('limitSelect').value = '50';
        document.getElementById('sortBy').value = 'created_at';
        document.getElementById('sortOrder').value = 'DESC';
        window.location.href = '?page=activity_log';
    });
}

// Only allow sorting for User Type and Activity Type columns
function setupSorting() {
    const sortableHeaders = document.querySelectorAll('.sortable');
    sortableHeaders.forEach(header => {
        header.style.cursor = 'pointer';
        header.style.userSelect = 'none';
        header.addEventListener('click', function() {
            const sortColumn = this.getAttribute('data-sort');
            const currentSortBy = document.getElementById('sortBy').value;
            const currentSortOrder = document.getElementById('sortOrder').value;
            let newSortOrder = 'DESC';
            if (currentSortBy === sortColumn) {
                newSortOrder = currentSortOrder === 'DESC' ? 'ASC' : 'DESC';
            }
            document.getElementById('sortBy').value = sortColumn;
            document.getElementById('sortOrder').value = newSortOrder;
            document.getElementById('filterForm').submit();
        });
        header.addEventListener('mouseenter', function() {
            this.style.backgroundColor = '#e9ecef';
        });
        header.addEventListener('mouseleave', function() {
            this.style.backgroundColor = '';
        });
    });
}

function updateSortIndicators() {
    if (typeof currentSortBy === 'undefined' || typeof currentSortOrder === 'undefined') return;
    const sortableHeaders = document.querySelectorAll('.sortable');
    sortableHeaders.forEach(header => {
        const sortColumn = header.getAttribute('data-sort');
        const icon = header.querySelector('.sort-icon');
        if (sortColumn === currentSortBy) {
            header.style.fontWeight = '700';
            header.style.color = '#dc3545';
            if (icon) {
                icon.classList.remove('bi-arrow-down-up');
                if (currentSortOrder === 'ASC') {
                    icon.classList.add('bi-arrow-up');
                } else {
                    icon.classList.add('bi-arrow-down');
                }
                icon.style.color = '#dc3545';
            }
        } else {
            if (icon) {
                icon.classList.remove('bi-arrow-up', 'bi-arrow-down');
                icon.classList.add('bi-arrow-down-up');
                icon.style.color = '';
            }
        }
    });
}

function setupExport() {
    const exportBtn = document.getElementById('exportBtn');
    if (!exportBtn) return;
    exportBtn.addEventListener('click', function(e) {
        e.preventDefault();
        if (typeof activityLogsData === 'undefined' || activityLogsData.length === 0) {
            Swal.fire({
                icon: 'warning',
                title: 'No Data',
                text: 'There are no activity logs to export.',
                confirmButtonColor: '#dc3545'
            });
            return;
        }
        Swal.fire({
            title: 'Export Activity Logs',
            text: 'Do you want to export the current filtered logs to CSV?',
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#dc3545',
            cancelButtonColor: '#6c757d',
            confirmButtonText: '<i class="bi bi-download me-1"></i>Export',
            cancelButtonText: 'Cancel'
        }).then((result) => {
            if (result.isConfirmed) exportToCSV();
        });
    });
}

function exportToCSV() {
    if (typeof activityLogsData === 'undefined') {
        console.error('Activity logs data not available');
        return;
    }
    let csv = 'Log ID,User ID,User Type,Activity Type,Description,IP Address,User Agent,Date & Time\n';
    activityLogsData.forEach(log => {
        const row = [
            log.log_id,
            log.user_id,
            log.user_type,
            log.activity_type,
            `"${log.activity_description.replace(/"/g, '""')}"`,
            log.ip_address || 'N/A',
            `"${(log.user_agent || 'N/A').replace(/"/g, '""')}"`,
            new Date(log.created_at).toLocaleString()
        ];
        csv += row.join(',') + '\n';
    });
    const blob = new Blob([csv], { type: 'text/csv;charset=utf-8;' });
    const link = document.createElement('a');
    const url = URL.createObjectURL(blob);
    const filename = `activity_logs_${new Date().toISOString().split('T')[0]}_${Date.now()}.csv`;
    link.setAttribute('href', url);
    link.setAttribute('download', filename);
    link.style.visibility = 'hidden';
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
    Swal.fire({
        icon: 'success',
        title: 'Export Successful',
        text: `Activity logs exported to ${filename}`,
        timer: 2000,
        showConfirmButton: false
    });
}

function setupSearch() {
    const searchInputs = ['userFilter', 'typeFilter', 'activityFilter'];
    searchInputs.forEach(inputId => {
        const input = document.getElementById(inputId);
        if (input) input.addEventListener('input', debounce(filterTable, 300));
    });
}

function filterTable() {
    const userFilter = document.getElementById('userFilter')?.value.toLowerCase() || '';
    const typeFilter = document.getElementById('typeFilter')?.value.toLowerCase() || '';
    const activityFilter = document.getElementById('activityFilter')?.value.toLowerCase() || '';
    const rows = document.querySelectorAll('#activityTable tbody tr');
    let visibleCount = 0;
    rows.forEach(row => {
        if (row.querySelector('td[colspan]')) return;
        const cells = row.querySelectorAll('td');
        const userId = cells[2]?.textContent.toLowerCase() || '';
        const userType = cells[3]?.textContent.toLowerCase() || '';
        const activityType = cells[4]?.textContent.toLowerCase() || '';
        const matchesUser = !userFilter || userId.includes(userFilter);
        const matchesType = !typeFilter || userType.includes(typeFilter);
        const matchesActivity = !activityFilter || activityType.includes(activityFilter);
        if (matchesUser && matchesType && matchesActivity) {
            row.style.display = '';
            visibleCount++;
        } else {
            row.style.display = 'none';
        }
    });
    updateVisibleCount(visibleCount);
}

function updateVisibleCount(count) {
    const footer = document.querySelector('.card-footer small');
    if (footer && typeof activityLogsData !== 'undefined') {
        footer.textContent = `Showing ${count} of ${activityLogsData.length} activities`;
    }
}

function setupAutoRefresh() {
    const autoRefresh = localStorage.getItem('activityLogsAutoRefresh');
    if (autoRefresh === 'true') {
        setInterval(() => {
            console.log('Auto-refreshing activity logs...');
            location.reload();
        }, 30000);
    }
}

function setupTableInteractions() {
    const table = document.getElementById('activityTable');
    if (!table) return;
    const rows = table.querySelectorAll('tbody tr');
    rows.forEach(row => {
        if (row.querySelector('td[colspan]')) return;
        row.style.cursor = 'pointer';
        row.addEventListener('click', function(e) {
            if (e.target.tagName === 'A' || e.target.tagName === 'BUTTON') return;
            showLogDetails(this);
        });
    });
}

function showLogDetails(row) {
    const cells = row.querySelectorAll('td');
    if (cells.length < 8) return;
    const logData = {
        logId: cells[0].textContent.trim(),
        dateTime: cells[1].textContent.trim(),
        userId: cells[2].textContent.trim(),
        userType: cells[3].textContent.trim(),
        activityType: cells[4].textContent.trim(),
        description: cells[5].textContent.trim(),
        ipAddress: cells[6].textContent.trim(),
        userAgent: cells[7].getAttribute('title') || cells[7].textContent.trim()
    };
    const html = `
        <div class="text-start">
            <table class="table table-sm">
                <tbody>
                    <tr><th style="width: 30%;">Log ID:</th><td><span class="badge bg-info text-white">${logData.logId}</span></td></tr>
                    <tr><th>Date & Time:</th><td>${logData.dateTime}</td></tr>
                    <tr><th>User ID:</th><td><span class="badge bg-light text-dark">${logData.userId}</span></td></tr>
                    <tr><th>User Type:</th><td>${logData.userType}</td></tr>
                    <tr><th>Activity Type:</th><td>${logData.activityType}</td></tr>
                    <tr><th>Description:</th><td>${logData.description}</td></tr>
                    <tr><th>IP Address:</th><td><code>${logData.ipAddress}</code></td></tr>
                    <tr><th>User Agent:</th><td><small class="text-muted">${logData.userAgent}</small></td></tr>
                </tbody>
            </table>
        </div>
    `;
    Swal.fire({
        title: '<span style="color: #dc3545;"><i class="bi bi-info-circle me-2"></i>Activity Log Details</span>',
        html: html,
        width: '600px',
        confirmButtonColor: '#dc3545',
        confirmButtonText: 'Close'
    });
}

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

const dateFrom = document.getElementById('dateFrom');
const dateTo = document.getElementById('dateTo');
if (dateFrom && dateTo) {
    dateFrom.addEventListener('change', function() { dateTo.min = this.value; });
    dateTo.addEventListener('change', function() { dateFrom.max = this.value; });
}

document.addEventListener('keydown', function(e) {
    if ((e.ctrlKey || e.metaKey) && e.key === 'k') {
        e.preventDefault();
        document.getElementById('userFilter')?.focus();
    }
    if ((e.ctrlKey || e.metaKey) && e.key === 'e') {
        e.preventDefault();
        document.getElementById('exportBtn')?.click();
    }
    if ((e.ctrlKey || e.metaKey) && e.key === 'r') {
        e.preventDefault();
        document.getElementById('clearFilters')?.click();
    }
});

console.log('[Activity Logs] Page initialized');