@extends('admin.layouts.master')

@section('title', 'Contest Entries Management')

@section('content')
<div class="container-fluid py-4">
    
    <!-- Header Section -->
    <div class="card shadow-sm mb-4">
        <div class="card-body">
            <div class="row align-items-center">
                <div class="col-md-6">
                    <h4 class="mb-0">
                        <i class="fas fa-images me-2 text-primary"></i>Contest Entries
                    </h4>
                    <small class="text-muted">Manage and review contest submissions</small>
                </div>
                <div class="col-md-6">
                    <div class="d-flex justify-content-end">
                        <button class="btn btn-outline-primary" onclick="loadEntries()">
                            <i class="fas fa-sync-alt me-1"></i>Refresh
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters Section -->
    <div class="card shadow-sm mb-4">
        <div class="card-header bg-light">
            <h5 class="mb-0">
                <i class="fas fa-filter me-2"></i>Filters
            </h5>
        </div>
        <div class="card-body">
            <div class="row g-3">
                <div class="col-md-3">
                    <label class="form-label">Contest</label>
                    <select id="contest_id" class="form-select">
                        <option value="">All Contests</option>
                    </select>
                </div>
                
                <div class="col-md-2">
                    <label class="form-label">Status</label>
                    <select id="status_select" class="form-select">
                        <option value="">All Status</option>
                        <option value="pending">⏳ Pending</option>
                        <option value="approved">✅ Approved</option>
                        <option value="rejected">❌ Rejected</option>
                    </select>
                </div>
                
                <div class="col-md-2">
                    <label class="form-label">From Date</label>
                    <input type="date" id="from_date" class="form-control">
                </div>
                
                <div class="col-md-2">
                    <label class="form-label">To Date</label>
                    <input type="date" id="to_date" class="form-control">
                </div>
                
                <div class="col-md-3">
                    <label class="form-label">&nbsp;</label>
                    <div class="d-flex gap-2">
                        <button class="btn btn-primary flex-grow-1" onclick="loadEntries()">
                            <i class="fas fa-search me-1"></i>Apply Filters
                        </button>
                        <button class="btn btn-secondary" onclick="clearFilters()">
                            <i class="fas fa-times me-1"></i>Clear
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Entries Table -->
    <div class="card shadow-sm">
        <div class="card-header bg-light d-flex justify-content-between align-items-center">
            <h5 class="mb-0">
                <i class="fas fa-list me-2"></i>Contest Entries
                <span id="totalEntries" class="badge bg-primary ms-2">0</span>
            </h5>
            <div class="d-flex gap-2">
                <button class="btn btn-sm btn-outline-success" onclick="bulkApprove()" id="bulkApproveBtn" style="display: none;">
                    <i class="fas fa-check me-1"></i>Bulk Approve
                </button>
                <button class="btn btn-sm btn-outline-danger" onclick="bulkReject()" id="bulkRejectBtn" style="display: none;">
                    <i class="fas fa-times me-1"></i>Bulk Reject
                </button>
            </div>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th width="40">
                                <input type="checkbox" id="selectAll" class="form-check-input">
                            </th>
                            <th width="5%">#</th>
                            <th width="15%">User</th>
                            <th width="20%">Contest</th>
                            <th width="12%">Media</th>
                            <th width="12%">Submitted</th>
                            <th width="10%">Status</th>
                            <th width="8%">Winner</th>
                            <th width="18%">Actions</th>
                        </tr>
                    </thead>
                    <tbody id="entriesTable">
                        <tr>
                            <td colspan="9" class="text-center py-5">
                                <div class="spinner-border text-primary" role="status">
                                    <span class="visually-hidden">Loading...</span>
                                </div>
                                <p class="mt-2 text-muted">Loading entries...</p>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
        <div class="card-footer bg-white">
            <div id="pagination" class="d-flex justify-content-between align-items-center">
                <div id="paginationInfo" class="text-muted small"></div>
                <div id="paginationLinks"></div>
            </div>
        </div>
    </div>
</div>

<!-- Media Preview Modal -->
<div class="modal fade" id="mediaModal" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-light">
                <h5 class="modal-title">
                    <i class="fas fa-image me-2"></i>Media Preview
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body text-center p-3">
                <div id="mediaContainer"></div>
            </div>
        </div>
    </div>
</div>

<!-- Delete Confirmation Modal -->
<div class="modal fade" id="deleteModal" tabindex="-1">
    <div class="modal-dialog modal-sm">
        <div class="modal-content">
            <div class="modal-body text-center py-4">
                <i class="fas fa-exclamation-triangle fa-3x text-warning mb-3"></i>
                <h5>Delete Entry?</h5>
                <p class="text-muted mb-0">This action cannot be undone!</p>
            </div>
            <div class="modal-footer justify-content-center">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-danger" id="confirmDeleteBtn">Delete</button>
            </div>
        </div>
    </div>
</div>

@endsection

@section('scripts')
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
// Global variables
let currentPage = 1;
let deleteId = null;
let mediaModal;
let deleteModal;
let selectedEntries = new Set();

// Initialize on document ready
$(document).ready(function() {
    // Initialize modals
    mediaModal = new bootstrap.Modal(document.getElementById('mediaModal'));
    deleteModal = new bootstrap.Modal(document.getElementById('deleteModal'));
    
    // Load contests for filter
    loadContests();
    
    // Load entries
    loadEntries();
    
    // Select all functionality
    $('#selectAll').on('change', function() {
        const isChecked = $(this).prop('checked');
        $('.entry-checkbox').prop('checked', isChecked);
        
        if (isChecked) {
            $('.entry-checkbox').each(function() {
                selectedEntries.add($(this).val());
            });
        } else {
            selectedEntries.clear();
        }
        
        updateBulkButtons();
    });
    
    // Delete confirmation
    $('#confirmDeleteBtn').on('click', function() {
        if (deleteId) {
            deleteEntry(deleteId);
        }
    });
    
    // Date range validation
    $('#from_date').on('change', function() {
        $('#to_date').attr('min', $(this).val());
    });
    
    $('#to_date').on('change', function() {
        $('#from_date').attr('max', $(this).val());
    });
});

// Load contests for filter dropdown
function loadContests() {
    $.ajax({
        url: "{{ route('admin.api.contests.index') }}",
        type: 'GET',
        data: { per_page: 100 },
        dataType: 'json',
        success: function(response) {
            const contests = response.data?.data || response.data || [];
            let options = '<option value="">All Contests</option>';
            
            contests.forEach(contest => {
                options += `<option value="${contest.id}">${escapeHtml(contest.title)}</option>`;
            });
            
            $('#contest_id').html(options);
        },
        error: function(xhr) {
            console.error('Error loading contests:', xhr);
        }
    });
}

// Load entries with filters
window.loadEntries = function(page = 1) {
    currentPage = page;
    
    const filters = {
        contest_id: $('#contest_id').val(),
        status: $('#status_select').val(),
        from_date: $('#from_date').val(),
        to_date: $('#to_date').val(),
        page: page
    };
    
    // Remove empty filters
    Object.keys(filters).forEach(key => {
        if (!filters[key]) delete filters[key];
    });
    
    $.ajax({
        url: "{{ route('admin.api.entries.index') }}",
        type: 'GET',
        data: filters,
        dataType: 'json',
        beforeSend: function() {
            $('#entriesTable').html(`
                <tr>
                    <td colspan="9" class="text-center py-5">
                        <div class="spinner-border text-primary" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                        <p class="mt-2 text-muted">Loading entries...</p>
                    </td>
                </tr>
            `);
            selectedEntries.clear();
            $('#selectAll').prop('checked', false);
            updateBulkButtons();
        },
        success: function(response) {
            renderEntries(response);
        },
        error: function(xhr) {
            console.error('Error loading entries:', xhr);
            $('#entriesTable').html(`
                <tr>
                    <td colspan="9" class="text-center py-5 text-danger">
                        <i class="fas fa-exclamation-circle fa-3x mb-3"></i>
                        <p>Error loading entries. Please try again.</p>
                        <button class="btn btn-sm btn-outline-primary mt-2" onclick="loadEntries()">
                            <i class="fas fa-sync-alt me-1"></i>Retry
                        </button>
                    </td>
                </tr>
            `);
        }
    });
};

// Render entries table
function renderEntries(response) {
    const entries = response.data?.data || response.data || [];
    let rows = '';
    
    // Update total count
    const total = response.data?.total || entries.length;
    $('#totalEntries').text(total);
    
    if (entries.length === 0) {
        $('#entriesTable').html(`
            <tr>
                <td colspan="9" class="text-center py-5">
                    <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
                    <p class="text-muted">No entries found</p>
                </td>
            </tr>
        `);
        $('#paginationInfo').text('');
        $('#paginationLinks').html('');
        return;
    }
    
    entries.forEach((entry, index) => {
        const serialNumber = (response.data?.from || 1) + index;
        const statusBadge = getStatusBadge(entry.status);
        const submittedDate = formatDate(entry.created_at);
        const mediaUrl = entry.media_url ? `${entry.media_url}` : '';
        const isVideo = entry.media_type === 'video' || mediaUrl.match(/\.(mp4|webm|ogg)$/i);
        const userName = entry.user ? 
            (entry.user.first_name + ' ' + (entry.user.last_name || '')).trim() || entry.user.name || 'Unknown' : 
            'Unknown';
        
        rows += `
            <tr id="entry-row-${entry.id}">
                <td>
                    <input type="checkbox" class="form-check-input entry-checkbox" 
                           value="${entry.id}" onchange="toggleEntrySelection(${entry.id}, this)">
                </td>
                <td>${serialNumber}</td>
                <td>
                    <div class="d-flex align-items-center">
                        ${entry.user?.profile_url ? 
                            `<img src="${entry.user.profile_url}" class="rounded-circle me-2" width="32" height="32" style="object-fit: cover;">` : 
                            `<div class="rounded-circle bg-secondary me-2 d-flex align-items-center justify-content-center" style="width: 32px; height: 32px;">
                                <i class="fas fa-user text-white"></i>
                            </div>`
                        }
                        <div>
                            <strong>${escapeHtml(userName)}</strong>
                            <br>
                            <small class="text-muted">${escapeHtml(entry.user?.email || '')}</small>
                        </div>
                    </div>
                </td>
                <td>
                    <strong>${escapeHtml(entry.contest?.title || 'N/A')}</strong>
                    ${entry.contest?.prize ? `<br><small class="text-muted">Prize: ${escapeHtml(entry.contest.prize)}</small>` : ''}
                </td>
                <td>
                    ${mediaUrl ? `
                        <div class="position-relative" style="cursor: pointer;" onclick="previewMedia('${mediaUrl}', ${isVideo})">
                            ${isVideo ? `
                                <div class="bg-dark rounded d-flex align-items-center justify-content-center" style="width: 60px; height: 60px;">
                                    <i class="fas fa-play text-white fa-2x"></i>
                                </div>
                            ` : `
                                <img src="${mediaUrl}" class="rounded" style="width: 60px; height: 60px; object-fit: cover;" onerror="this.src='https://via.placeholder.com/60?text=No+Image'">
                            `}
                        </div>
                    ` : '<span class="text-muted">No media</span>'}
                </td>
                <td>
                    <small>
                        <i class="far fa-calendar-alt me-1"></i>${submittedDate}
                    </small>
                </td>
                <td>
                    <span class="status-badge" data-entry-id="${entry.id}">${statusBadge}</span>
                </td>
                <td class="text-center">
                    <span class="winner-badge" data-entry-id="${entry.id}">
                        ${entry.is_winner ? '<span class="fs-4">🏆</span>' : '<span class="text-muted">-</span>'}
                    </span>
                </td>
                <td>
                    <div class="btn-group">
                        ${entry.status !== 'approved' ? `
                            <button class="btn btn-sm btn-outline-success" onclick="approveEntry(${entry.id})" title="Approve">
                                <i class="fas fa-check"></i>
                            </button>
                        ` : ''}
                        ${entry.status !== 'rejected' ? `
                            <button class="btn btn-sm btn-outline-danger" onclick="rejectEntry(${entry.id})" title="Reject">
                                <i class="fas fa-times"></i>
                            </button>
                        ` : ''}
                        ${!entry.is_winner ? `
                            <button class="btn btn-sm btn-outline-warning" onclick="markWinner(${entry.id})" title="Mark as Winner">
                                <i class="fas fa-trophy"></i>
                            </button>
                        ` : ''}
                        <button class="btn btn-sm btn-outline-danger" onclick="confirmDelete(${entry.id})" title="Delete">
                            <i class="fas fa-trash"></i>
                        </button>
                    </div>
                </td>
            </tr>
        `;
    });
    
    $('#entriesTable').html(rows);
    
    // Render pagination
    if (response.data && response.data.last_page > 1) {
        renderPagination(response.data);
    } else {
        $('#paginationInfo').text(`Showing ${entries.length} entries`);
        $('#paginationLinks').html('');
    }
}

// Render pagination
function renderPagination(data) {
    const start = data.from || 1;
    const end = data.to || data.data.length;
    const total = data.total || data.data.length;
    
    $('#paginationInfo').text(`Showing ${start} to ${end} of ${total} entries`);
    
    let html = '<nav><ul class="pagination pagination-sm mb-0">';
    
    // Previous
    html += `<li class="page-item ${data.current_page === 1 ? 'disabled' : ''}">
        <a class="page-link" href="javascript:void(0)" onclick="loadEntries(${data.current_page - 1})">
            <i class="fas fa-chevron-left"></i>
        </a>
    </li>`;
    
    // Pages
    for (let i = 1; i <= data.last_page; i++) {
        if (i === 1 || i === data.last_page || (i >= data.current_page - 2 && i <= data.current_page + 2)) {
            html += `<li class="page-item ${i === data.current_page ? 'active' : ''}">
                <a class="page-link" href="javascript:void(0)" onclick="loadEntries(${i})">${i}</a>
            </li>`;
        } else if (i === data.current_page - 3 || i === data.current_page + 3) {
            html += '<li class="page-item disabled"><span class="page-link">...</span></li>';
        }
    }
    
    // Next
    html += `<li class="page-item ${data.current_page === data.last_page ? 'disabled' : ''}">
        <a class="page-link" href="javascript:void(0)" onclick="loadEntries(${data.current_page + 1})">
            <i class="fas fa-chevron-right"></i>
        </a>
    </li>`;
    
    html += '</ul></nav>';
    $('#paginationLinks').html(html);
}

// Clear filters
window.clearFilters = function() {
    $('#contest_id').val('');
    $('#status_select').val('');
    $('#from_date').val('');
    $('#to_date').val('');
    loadEntries(1);
};

// Preview media
window.previewMedia = function(url, isVideo) {
    const container = $('#mediaContainer');
    
    if (isVideo) {
        container.html(`
            <video controls class="img-fluid rounded" style="max-height: 70vh;">
                <source src="${url}" type="video/mp4">
                Your browser does not support the video tag.
            </video>
        `);
    } else {
        container.html(`<img src="${url}" class="img-fluid rounded" style="max-height: 70vh;" onerror="this.src='https://via.placeholder.com/400?text=Image+Not+Found'">`);
    }
    
    mediaModal.show();
};

// Entry selection
window.toggleEntrySelection = function(id, checkbox) {
    if (checkbox.checked) {
        selectedEntries.add(id.toString());
    } else {
        selectedEntries.delete(id.toString());
        $('#selectAll').prop('checked', false);
    }
    updateBulkButtons();
};

// Update bulk buttons visibility
function updateBulkButtons() {
    if (selectedEntries.size > 0) {
        $('#bulkApproveBtn, #bulkRejectBtn').show();
    } else {
        $('#bulkApproveBtn, #bulkRejectBtn').hide();
    }
}

// Bulk approve
window.bulkApprove = function() {
    if (selectedEntries.size === 0) return;
    
    Swal.fire({
        title: 'Bulk Approve',
        text: `Approve ${selectedEntries.size} entries?`,
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: 'Yes, approve'
    }).then((result) => {
        if (result.isConfirmed) {
            updateBulkStatus('approved');
        }
    });
};

// Bulk reject
window.bulkReject = function() {
    if (selectedEntries.size === 0) return;
    
    Swal.fire({
        title: 'Bulk Reject',
        text: `Reject ${selectedEntries.size} entries?`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Yes, reject'
    }).then((result) => {
        if (result.isConfirmed) {
            updateBulkStatus('rejected');
        }
    });
};

// Update bulk status
function updateBulkStatus(status) {
    const promises = Array.from(selectedEntries).map(id => {
        return new Promise((resolve, reject) => {
            $.ajax({
                url: `/admin-api/contest-entries/status/${id}`,
                type: 'POST',
                data: {
                    status: status,
                    _token: "{{ csrf_token() }}"
                },
                success: function() {
                    resolve();
                },
                error: function() {
                    reject();
                }
            });
        });
    });
    
    Promise.all(promises).then(() => {
        showAlert('success', 'Success!', `${selectedEntries.size} entries ${status}`);
        selectedEntries.clear();
        updateBulkButtons();
        $('#selectAll').prop('checked', false);
        loadEntries(currentPage);
    }).catch(() => {
        showAlert('error', 'Error!', 'Failed to update some entries');
    });
}

// Approve entry
window.approveEntry = function(id) {
    updateStatus(id, 'approved', 'Entry approved successfully');
};

// Reject entry
window.rejectEntry = function(id) {
    updateStatus(id, 'rejected', 'Entry rejected successfully');
};

// Update status
function updateStatus(id, status, message) {
    // Show loading state on button
    const btn = $(`button[onclick="approveEntry(${id})"], button[onclick="rejectEntry(${id})"]`);
    const originalHtml = btn.html();
    btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm"></span>');
    
    $.ajax({
        url: `/admin-api/contest-entries/status/${id}`,
        type: 'POST',
        data: {
            status: status,
            _token: "{{ csrf_token() }}"
        },
        complete: function(xhr) {
            btn.prop('disabled', false).html(originalHtml);
            
            // Consider it success if we get a response (even empty)
            // Only treat as error if status code is 4xx or 5xx
            if (xhr.status >= 200 && xhr.status < 300) {
                // Update the UI directly without reloading all data
                updateEntryUI(id, status, null);
                showAlert('success', 'Success!', message, 1500);
            } else {
                let errorMsg = 'Failed to update status';
                try {
                    const response = JSON.parse(xhr.responseText);
                    errorMsg = response.message || errorMsg;
                } catch(e) {}
                showAlert('error', 'Error!', errorMsg);
            }
        }
    });
}

// Update entry UI directly
function updateEntryUI(entryId, newStatus, isWinner) {
    // Update status badge
    const statusBadge = getStatusBadge(newStatus);
    $(`.status-badge[data-entry-id="${entryId}"]`).html(statusBadge);
    
    // Update winner badge if provided
    if (isWinner !== null) {
        const winnerHtml = isWinner ? '<span class="fs-4">🏆</span>' : '<span class="text-muted">-</span>';
        $(`.winner-badge[data-entry-id="${entryId}"]`).html(winnerHtml);
    }
    
    // Update action buttons
    const row = $(`#entry-row-${entryId}`);
    const actionCell = row.find('td:last-child');
    const btnGroup = actionCell.find('.btn-group');
    
    // Rebuild action buttons based on new status
    let buttonsHtml = '';
    
    if (newStatus !== 'approved') {
        buttonsHtml += `<button class="btn btn-sm btn-outline-success" onclick="approveEntry(${entryId})" title="Approve"><i class="fas fa-check"></i></button>`;
    }
    
    if (newStatus !== 'rejected') {
        buttonsHtml += `<button class="btn btn-sm btn-outline-danger" onclick="rejectEntry(${entryId})" title="Reject"><i class="fas fa-times"></i></button>`;
    }
    
    // Keep winner button if not already winner
    const isCurrentlyWinner = row.find('.winner-badge .fs-4').length > 0;
    if (!isCurrentlyWinner && isWinner !== true) {
        buttonsHtml += `<button class="btn btn-sm btn-outline-warning" onclick="markWinner(${entryId})" title="Mark as Winner"><i class="fas fa-trophy"></i></button>`;
    }
    
    buttonsHtml += `<button class="btn btn-sm btn-outline-danger" onclick="confirmDelete(${entryId})" title="Delete"><i class="fas fa-trash"></i></button>`;
    
    btnGroup.html(buttonsHtml);
}

// Mark as winner
window.markWinner = function(id) {
    Swal.fire({
        title: 'Mark as Winner?',
        text: 'This will mark this entry as the contest winner.',
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: 'Yes, mark as winner'
    }).then((result) => {
        if (result.isConfirmed) {
            // Show loading
            const btn = $(`button[onclick="markWinner(${id})"]`);
            const originalHtml = btn.html();
            btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm"></span>');
            
            $.ajax({
                url: `/admin-api/contest-entries/winner/${id}`,
                type: 'POST',
                data: {
                    _token: "{{ csrf_token() }}"
                },
                complete: function(xhr) {
                    btn.prop('disabled', false).html(originalHtml);
                    
                    if (xhr.status >= 200 && xhr.status < 300) {
                        // Update UI directly
                        updateEntryUI(id, null, true);
                        showAlert('success', 'Success!', 'Entry marked as winner', 1500);
                    } else {
                        let errorMsg = 'Failed to mark as winner';
                        try {
                            const response = JSON.parse(xhr.responseText);
                            errorMsg = response.message || errorMsg;
                        } catch(e) {}
                        showAlert('error', 'Error!', errorMsg);
                    }
                }
            });
        }
    });
};

// Confirm delete
window.confirmDelete = function(id) {
    deleteId = id;
    deleteModal.show();
};

// Delete entry
function deleteEntry(id) {
    $.ajax({
        url: `/admin-api/contest-entries/${id}`,
        type: 'POST',
        data: {
            _method: 'DELETE',
            _token: "{{ csrf_token() }}"
        },
        beforeSend: function() {
            $('#confirmDeleteBtn').prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span>Deleting...');
        },
        complete: function(xhr) {
            $('#confirmDeleteBtn').prop('disabled', false).text('Delete');
            
            if (xhr.status >= 200 && xhr.status < 300) {
                deleteModal.hide();
                showAlert('success', 'Deleted!', 'Entry has been deleted', 1500);
                selectedEntries.delete(id.toString());
                updateBulkButtons();
                
                // Remove row from table
                $(`#entry-row-${id}`).fadeOut(300, function() {
                    $(this).remove();
                    
                    // Update total count
                    const currentTotal = parseInt($('#totalEntries').text()) || 0;
                    $('#totalEntries').text(currentTotal - 1);
                    
                    // If no entries left, show empty state
                    if ($('#entriesTable tr').length === 0) {
                        $('#entriesTable').html(`
                            <tr>
                                <td colspan="9" class="text-center py-5">
                                    <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
                                    <p class="text-muted">No entries found</p>
                                </td>
                            </tr>
                        `);
                        $('#paginationInfo').text('');
                        $('#paginationLinks').html('');
                    }
                });
            } else {
                deleteModal.hide();
                let errorMsg = 'Failed to delete entry';
                try {
                    const response = JSON.parse(xhr.responseText);
                    errorMsg = response.message || errorMsg;
                } catch(e) {}
                showAlert('error', 'Error!', errorMsg);
            }
            
            deleteId = null;
        }
    });
}

// Helper Functions
function getStatusBadge(status) {
    const badges = {
        'pending': '<span class="badge bg-warning">⏳ Pending</span>',
        'approved': '<span class="badge bg-success">✅ Approved</span>',
        'rejected': '<span class="badge bg-danger">❌ Rejected</span>'
    };
    return badges[status] || `<span class="badge bg-secondary">${escapeHtml(status || 'Unknown')}</span>`;
}

function formatDate(dateString) {
    if (!dateString) return 'N/A';
    const date = new Date(dateString);
    return date.toLocaleDateString('en-IN', { 
        day: '2-digit', 
        month: 'short', 
        year: 'numeric' 
    });
}

function escapeHtml(text) {
    if (!text) return '';
    const map = {
        '&': '&amp;',
        '<': '&lt;',
        '>': '&gt;',
        '"': '&quot;',
        "'": '&#039;'
    };
    return String(text).replace(/[&<>"']/g, m => map[m]);
}

function showAlert(icon, title, text, timer = 2000) {
    Swal.fire({
        icon: icon,
        title: title,
        text: text,
        timer: timer,
        showConfirmButton: timer === 0
    });
}

</script>

<!-- Responsive CSS -->
<style>
@media (max-width: 768px) {
    .table-responsive {
        font-size: 0.875rem;
    }
    
    .btn-group {
        display: flex;
        flex-wrap: wrap;
    }
    
    .btn-group .btn {
        padding: 0.25rem 0.5rem;
        font-size: 0.75rem;
    }
    
    .pagination {
        font-size: 0.75rem;
    }
    
    .page-link {
        padding: 0.25rem 0.5rem;
    }
    
    .card-header .d-flex {
        flex-direction: column;
        gap: 0.5rem;
    }
}

@media (max-width: 576px) {
    h4 {
        font-size: 1.25rem;
    }
    
    .modal-dialog {
        margin: 0.5rem;
    }
}
</style>
@endsection