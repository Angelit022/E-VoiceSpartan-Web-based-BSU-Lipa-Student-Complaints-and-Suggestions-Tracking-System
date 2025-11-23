// Feedback Pagination JavaScript

let feedbackCurrentPage = 1;
let feedbackRowsPerPage = 10;
let allFeedbackRows = [];

document.addEventListener('DOMContentLoaded', function() {
    initializeFeedbackPagination();
});

function initializeFeedbackPagination() {
    const tableBody = document.getElementById('feedbackTableBody');
    if (tableBody) {
        allFeedbackRows = Array.from(tableBody.querySelectorAll('tr'));
        if (allFeedbackRows.length > 0) {
            displayFeedbackPage(1);
        }
    }
}

function displayFeedbackPage(pageNum) {
    feedbackCurrentPage = pageNum;
    const tableBody = document.getElementById('feedbackTableBody');
    
    if (!tableBody) return;
    
    tableBody.innerHTML = '';
    
    const totalPages = Math.ceil(allFeedbackRows.length / feedbackRowsPerPage);
    const start = (pageNum - 1) * feedbackRowsPerPage;
    const end = Math.min(start + feedbackRowsPerPage, allFeedbackRows.length);
    
    for (let i = start; i < end; i++) {
        tableBody.appendChild(allFeedbackRows[i].cloneNode(true));
    }
    
    document.getElementById('feedbackStartRow').textContent = allFeedbackRows.length === 0 ? 0 : start + 1;
    document.getElementById('feedbackEndRow').textContent = end;
    document.getElementById('feedbackTotalRows').textContent = allFeedbackRows.length;
    
    generateFeedbackPagination(totalPages, pageNum);
}

function generateFeedbackPagination(totalPages, currentPage) {
    const paginationEl = document.getElementById('feedbackPagination');
    if (!paginationEl) return;
    
    paginationEl.innerHTML = '';
    
    // Previous button
    const prevBtn = document.createElement('li');
    prevBtn.className = `page-item ${currentPage === 1 ? 'disabled' : ''}`;
    prevBtn.innerHTML = `<a class="page-link" href="#" onclick="displayFeedbackPage(${currentPage - 1}); return false;"><i class="bi bi-chevron-left"></i></a>`;
    paginationEl.appendChild(prevBtn);
    
    // Page numbers
    const maxButtons = 5;
    let startPage = Math.max(1, currentPage - Math.floor(maxButtons / 2));
    const endPage = Math.min(totalPages, startPage + maxButtons - 1);
    
    if (endPage - startPage < maxButtons - 1) {
        startPage = Math.max(1, endPage - maxButtons + 1);
    }
    
    if (startPage > 1) {
        const firstBtn = document.createElement('li');
        firstBtn.className = 'page-item';
        firstBtn.innerHTML = `<a class="page-link" href="#" onclick="displayFeedbackPage(1); return false;">1</a>`;
        paginationEl.appendChild(firstBtn);
        
        if (startPage > 2) {
            const dots = document.createElement('li');
            dots.className = 'page-item disabled';
            dots.innerHTML = `<span class="page-link">...</span>`;
            paginationEl.appendChild(dots);
        }
    }
    
    for (let i = startPage; i <= endPage; i++) {
        const btn = document.createElement('li');
        btn.className = `page-item ${i === currentPage ? 'active' : ''}`;
        btn.innerHTML = `<a class="page-link" href="#" onclick="displayFeedbackPage(${i}); return false;">${i}</a>`;
        paginationEl.appendChild(btn);
    }
    
    if (endPage < totalPages) {
        if (endPage < totalPages - 1) {
            const dots = document.createElement('li');
            dots.className = 'page-item disabled';
            dots.innerHTML = `<span class="page-link">...</span>`;
            paginationEl.appendChild(dots);
        }
        
        const lastBtn = document.createElement('li');
        lastBtn.className = 'page-item';
        lastBtn.innerHTML = `<a class="page-link" href="#" onclick="displayFeedbackPage(${totalPages}); return false;">${totalPages}</a>`;
        paginationEl.appendChild(lastBtn);
    }
    
    // Next button
    const nextBtn = document.createElement('li');
    nextBtn.className = `page-item ${currentPage === totalPages ? 'disabled' : ''}`;
    nextBtn.innerHTML = `<a class="page-link" href="#" onclick="displayFeedbackPage(${currentPage + 1}); return false;"><i class="bi bi-chevron-right"></i></a>`;
    paginationEl.appendChild(nextBtn);
}

function changeFeedbackPagination() {
    const select = document.getElementById('feedbackRowsPerPage');
    if (select) {
        feedbackRowsPerPage = parseInt(select.value);
        feedbackCurrentPage = 1;
        displayFeedbackPage(1);
    }
}