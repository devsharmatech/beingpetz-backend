@extends('admin.layouts.master')

@section('title', 'Contests Management')

@section('content')
<div class="container-fluid py-4">
    
    <!-- Header Section -->
    <div class="card shadow-sm mb-4">
        <div class="card-body">
            <div class="row align-items-center">
                <div class="col-md-6">
                    <h4 class="mb-0">
                        <i class="fas fa-trophy me-2 text-warning"></i>Contests Management
                    </h4>
                    <small class="text-muted">Manage your contests and competitions</small>
                </div>
                <div class="col-md-6">
                    <div class="d-flex justify-content-end gap-2 flex-wrap">
                        <div class="position-relative" style="min-width: 250px;">
                            <i class="fas fa-search position-absolute top-50 start-0 translate-middle-y ms-3 text-muted"></i>
                            <input type="text" id="searchInput" class="form-control ps-5" placeholder="Search contests...">
                        </div>
                        <select id="statusFilter" class="form-select" style="width: auto;">
                            <option value="">All Status</option>
                            <option value="open">Open</option>
                            <option value="upcoming">Upcoming</option>
                            <option value="closed">Closed</option>
                        </select>
                        <button class="btn btn-primary" onclick="openModal()">
                            <i class="fas fa-plus me-1"></i>Add Contest
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Contests Table -->
    <div class="card shadow-sm">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th width="5%">#</th>
                            <th width="12%">Banner</th>
                            <th width="25%">Contest Details</th>
                            <th width="15%">Prize</th>
                            <th width="15%">Duration</th>
                            <th width="10%">Status</th>
                            <th width="18%">Actions</th>
                        </tr>
                    </thead>
                    <tbody id="contestsTable">
                        <tr>
                            <td colspan="7" class="text-center py-5">
                                <div class="spinner-border text-primary" role="status">
                                    <span class="visually-hidden">Loading...</span>
                                </div>
                                <p class="mt-2 text-muted">Loading contests...</p>
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

<!-- Contest Modal -->
<div class="modal fade" id="contestModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <form id="contestForm" enctype="multipart/form-data">
            @csrf
            <input type="hidden" name="_method" id="formMethod" value="POST">
            <div class="modal-content">
                <div class="modal-header bg-light">
                    <h5 class="modal-title" id="modalTitle">
                        <i class="fas fa-trophy me-2 text-warning"></i>Add New Contest
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                
                <div class="modal-body">
                    <div class="row g-3">
                        <!-- Basic Information -->
                        <div class="col-12">
                            <h6 class="fw-bold mb-3 text-primary">
                                <i class="fas fa-info-circle me-2"></i>Basic Information
                            </h6>
                        </div>
                        
                        <div class="col-12">
                            <label class="form-label">Contest Title <span class="text-danger">*</span></label>
                            <input type="text" name="title" id="title" class="form-control" placeholder="Enter contest title" required>
                        </div>
                        
                        <div class="col-md-12">
                            <label class="form-label">Short Description</label>
                            <textarea name="short_description" id="short_description" class="form-control" rows="2" placeholder="Brief description for preview"></textarea>
                        </div>
                        
                        <div class="col-12">
                            <label class="form-label">Full Description</label>
                            <textarea name="description" id="description" class="form-control" rows="4" placeholder="Detailed description of the contest"></textarea>
                        </div>
                        
                        <div class="col-md-6">
                            <label class="form-label">Prize <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="fas fa-gift"></i></span>
                                <input type="text" name="prize" id="prize" class="form-control" placeholder="e.g., ₹10,000" required>
                            </div>
                        </div>
                        
                        <!-- Date Range -->
                        <div class="col-12 mt-3">
                            <h6 class="fw-bold mb-3 text-primary">
                                <i class="fas fa-calendar me-2"></i>Duration
                            </h6>
                        </div>
                        
                        <div class="col-md-6">
                            <label class="form-label">Start Date <span class="text-danger">*</span></label>
                            <input type="date" name="start_date" id="start_date" class="form-control" required>
                        </div>
                        
                        <div class="col-md-6">
                            <label class="form-label">End Date <span class="text-danger">*</span></label>
                            <input type="date" name="end_date" id="end_date" class="form-control" required>
                        </div>
                        
                        <!-- Images Section -->
                        <div class="col-12 mt-3">
                            <h6 class="fw-bold mb-3 text-primary">
                                <i class="fas fa-images me-2"></i>Images
                            </h6>
                        </div>
                        
                        <div class="col-md-6">
                            <label class="form-label">Banner Image <span class="text-danger" id="bannerRequired">*</span></label>
                            <input type="file" name="banner" id="banner" class="form-control" accept="image/*" onchange="previewImage(this, 'bannerPreview', 'bannerPreviewContainer')">
                            <small class="text-muted">Recommended size: 1200x400px</small>
                            <div id="bannerPreviewContainer" class="mt-2 border rounded p-2 bg-light text-center" style="display: none;">
                                <img id="bannerPreview" class="img-fluid rounded" style="max-height: 120px; width: 100%; object-fit: cover;">
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <label class="form-label">Thumbnail Image</label>
                            <input type="file" name="thumbnail" id="thumbnail" class="form-control" accept="image/*" onchange="previewImage(this, 'thumbnailPreview', 'thumbnailPreviewContainer')">
                            <small class="text-muted">Recommended size: 400x400px</small>
                            <div id="thumbnailPreviewContainer" class="mt-2 border rounded p-2 bg-light text-center" style="display: none;">
                                <img id="thumbnailPreview" class="img-fluid rounded" style="max-height: 120px; width: 100%; object-fit: cover;">
                            </div>
                        </div>
                        
                        <!-- Status -->
                        <div class="col-12 mt-3">
                            <h6 class="fw-bold mb-3 text-primary">
                                <i class="fas fa-toggle-on me-2"></i>Status
                            </h6>
                        </div>
                        
                        <div class="col-md-6">
                            <label class="form-label">Contest Status</label>
                            <select name="status" id="status_select" class="form-select">
                                <option value="open">🟢 Open</option>
                                <option value="upcoming">🔵 Upcoming</option>
                                <option value="closed">🔴 Closed</option>
                            </select>
                        </div>
                    </div>
                </div>
                
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="fas fa-times me-1"></i>Cancel
                    </button>
                    <button type="submit" id="submitBtn" class="btn btn-primary">
                        <i class="fas fa-save me-1"></i>Save Contest
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Delete Confirmation Modal -->
<div class="modal fade" id="deleteModal" tabindex="-1">
    <div class="modal-dialog modal-sm">
        <div class="modal-content">
            <div class="modal-body text-center py-4">
                <i class="fas fa-exclamation-triangle fa-3x text-warning mb-3"></i>
                <h5>Delete Contest?</h5>
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
<!-- Include SweetAlert2 -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
// Global variables
let editId = null;
let deleteId = null;
let currentPage = 1;
let deleteModal;
let contestModal;

// Helper Functions
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

function getStatusBadge(status) {
    const badges = {
        'open': '<span class="badge bg-success">🟢 Open</span>',
        'upcoming': '<span class="badge bg-info">🔵 Upcoming</span>',
        'closed': '<span class="badge bg-secondary">🔴 Closed</span>'
    };
    return badges[status] || `<span class="badge bg-secondary">${escapeHtml(status || 'N/A')}</span>`;
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

function debounce(func, wait) {
    let timeout;
    return function() {
        clearTimeout(timeout);
        timeout = setTimeout(() => func.apply(this, arguments), wait);
    };
}

function showAlert(icon, title, text, timer = 2000) {
    Swal.fire({
        icon: icon,
        title: title,
        text: text,
        timer: timer,
        showConfirmButton: timer === 0 ? true : false
    });
}

// Image preview function
window.previewImage = function(input, previewId, containerId) {
    const preview = document.getElementById(previewId);
    const container = document.getElementById(containerId);
    
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = function(e) {
            preview.src = e.target.result;
            container.style.display = 'block';
        };
        reader.readAsDataURL(input.files[0]);
    } else {
        container.style.display = 'none';
    }
};

// Render pagination
function renderPagination(data) {
    if (!data) return;
    
    const start = data.from || 1;
    const end = data.to || data.data?.length || 0;
    const total = data.total || 0;
    
    $('#paginationInfo').text(`Showing ${start} to ${end} of ${total} contests`);
    
    let html = '<nav><ul class="pagination pagination-sm mb-0">';
    
    // Previous
    html += `<li class="page-item ${data.current_page === 1 ? 'disabled' : ''}">
        <a class="page-link" href="javascript:void(0)" onclick="loadContests(${data.current_page - 1})">
            <i class="fas fa-chevron-left"></i>
        </a>
    </li>`;
    
    // Pages
    for (let i = 1; i <= data.last_page; i++) {
        if (i === 1 || i === data.last_page || (i >= data.current_page - 2 && i <= data.current_page + 2)) {
            html += `<li class="page-item ${i === data.current_page ? 'active' : ''}">
                <a class="page-link" href="javascript:void(0)" onclick="loadContests(${i})">${i}</a>
            </li>`;
        } else if (i === data.current_page - 3 || i === data.current_page + 3) {
            html += '<li class="page-item disabled"><span class="page-link">...</span></li>';
        }
    }
    
    // Next
    html += `<li class="page-item ${data.current_page === data.last_page ? 'disabled' : ''}">
        <a class="page-link" href="javascript:void(0)" onclick="loadContests(${data.current_page + 1})">
            <i class="fas fa-chevron-right"></i>
        </a>
    </li>`;
    
    html += '</ul></nav>';
    $('#paginationLinks').html(html);
}

// Render contests table
function renderContests(response) {
    const contests = response.data?.data || response.data || [];
    let rows = '';
    
    if (contests.length === 0) {
        $('#contestsTable').html(`
            <tr>
                <td colspan="7" class="text-center py-5">
                    <i class="fas fa-trophy fa-3x text-muted mb-3"></i>
                    <p class="text-muted">No contests found</p>
                    <button class="btn btn-sm btn-primary mt-2" onclick="openModal()">
                        <i class="fas fa-plus me-1"></i>Create Contest
                    </button>
                </td>
            </tr>
        `);
        $('#paginationInfo').text('');
        $('#paginationLinks').html('');
        return;
    }
    
    contests.forEach((contest, index) => {
        const serialNumber = (response.data?.from || 1) + index;
        const statusBadge = getStatusBadge(contest.status);
        const startDate = formatDate(contest.start_date);
        const endDate = formatDate(contest.end_date);
        const bannerUrl = contest.banner_url ? `${contest.banner_url}` : `${contest.banner}`;
        
        rows += `
            <tr>
                <td>${serialNumber}</td>
                <td>
                    ${bannerUrl ? 
                        `<img src="${bannerUrl}" class="rounded" style="width: 80px; height: 50px; object-fit: cover;" alt="Banner">` : 
                        '<span class="text-muted">No banner</span>'}
                </td>
                <td>
                    <strong>${escapeHtml(contest.title)}</strong>
                    ${contest.short_description ? 
                        `<br><small class="text-muted">${escapeHtml(contest.short_description).substring(0, 50)}...</small>` : ''}
                </td>
                <td>
                    <span class="fw-semibold">${escapeHtml(contest.prize || 'N/A')}</span>
                </td>
                <td>
                    <small>
                        <i class="far fa-calendar-alt me-1"></i>${startDate}<br>
                        <i class="far fa-calendar-check me-1"></i>${endDate}
                    </small>
                </td>
                <td>${statusBadge}</td>
                <td>
                    <div class="btn-group">
                        <button class="btn btn-sm btn-outline-primary" onclick="editContest(${contest.id})" title="Edit">
                            <i class="fas fa-edit"></i>
                        </button>
                        <button class="btn btn-sm btn-outline-danger" onclick="confirmDelete(${contest.id})" title="Delete">
                            <i class="fas fa-trash"></i>
                        </button>
                    </div>
                </td>
            </tr>
        `;
    });
    
    $('#contestsTable').html(rows);
    
    // Render pagination
    if (response.data && response.data.last_page > 1) {
        renderPagination(response.data);
    } else {
        $('#paginationInfo').text(`Showing ${contests.length} contests`);
        $('#paginationLinks').html('');
    }
}

// Load contests function
window.loadContests = function(page = 1) {
    currentPage = page;
    const search = $('#searchInput').val();
    const status = $('#statusFilter').val();
    
    $.ajax({
        url: "{{ route('admin.api.contests.index') }}",
        type: 'GET',
        data: {
            page: page,
            search: search,
            status: status
        },
        dataType: 'json',
        beforeSend: function() {
            $('#contestsTable').html(`
                <tr>
                    <td colspan="7" class="text-center py-5">
                        <div class="spinner-border text-primary" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                        <p class="mt-2 text-muted">Loading contests...</p>
                    </td>
                </tr>
            `);
        },
        success: function(response) {
            renderContests(response);
        },
        error: function(xhr) {
            console.error('Error loading contests:', xhr);
            $('#contestsTable').html(`
                <tr>
                    <td colspan="7" class="text-center py-5 text-danger">
                        <i class="fas fa-exclamation-circle fa-3x mb-3"></i>
                        <p>Error loading contests. Please try again.</p>
                        <button class="btn btn-sm btn-outline-primary mt-2" onclick="loadContests()">
                            <i class="fas fa-sync-alt me-1"></i>Retry
                        </button>
                    </td>
                </tr>
            `);
        }
    });
};

// Open modal for new contest
window.openModal = function() {
    editId = null;
    $('#contestForm')[0].reset();
    $('#formMethod').val('POST');
    $('#modalTitle').html('<i class="fas fa-trophy me-2 text-warning"></i>Add New Contest');
    $('#submitBtn').html('<i class="fas fa-save me-1"></i>Save Contest');
    $('#bannerPreviewContainer').hide();
    $('#thumbnailPreviewContainer').hide();
    $('#banner').attr('required', true);
    $('#bannerRequired').show();
    $('#status_select').val('open');
    contestModal.show();
};

// Edit contest
window.editContest = function(id) {
    $.ajax({
        url: "{{ route('admin.api.contests.show', ['id' => '__ID__']) }}".replace('__ID__', id),
        type: 'GET',
        dataType: 'json',
        beforeSend: function() {
            Swal.fire({
                title: 'Loading...',
                allowOutsideClick: false,
                didOpen: () => Swal.showLoading()
            });
        },
        success: function(response) {
            Swal.close();
            const contest = response.data || response;
            
            editId = contest.id;
            $('#formMethod').val('PUT');
            $('#title').val(contest.title);
            $('#short_description').val(contest.short_description);
            $('#description').val(contest.description);
            $('#prize').val(contest.prize);
            $('#start_date').val(contest.start_date);
            $('#end_date').val(contest.end_date);
            $('#status_select').val(contest.status || 'open');
            
            // Remove required from banner for edit
            $('#banner').removeAttr('required');
            $('#bannerRequired').hide();
            
            // Show existing images
            if (contest.banner) {
                $('#bannerPreview').attr('src', `${contest.banner_url}`);
                $('#bannerPreviewContainer').show();
            } else {
                $('#bannerPreviewContainer').hide();
            }
            
            if (contest.thumbnail) {
                $('#thumbnailPreview').attr('src', `${contest.thumbnail_url}`);
                $('#thumbnailPreviewContainer').show();
            } else {
                $('#thumbnailPreviewContainer').hide();
            }
            
            $('#modalTitle').html('<i class="fas fa-edit me-2 text-warning"></i>Edit Contest');
            $('#submitBtn').html('<i class="fas fa-save me-1"></i>Update Contest');
            contestModal.show();
        },
        error: function(xhr) {
            Swal.close();
            console.error('Error response:', xhr);
            
            // Check if response is HTML
            if (xhr.responseText && xhr.responseText.includes('<!DOCTYPE')) {
                showAlert('error', 'Error!', 'Server returned HTML instead of JSON. Check route configuration.', 0);
            } else {
                showAlert('error', 'Error!', 'Failed to load contest details', 0);
            }
        }
    });
};

// Save contest
function saveContest() {
    const formData = new FormData($('#contestForm')[0]);
    
    let url = editId 
        ? "{{ route('admin.api.contests.update', ['id' => '__ID__']) }}".replace('__ID__', editId)
        : "{{ route('admin.api.contests.store') }}";
    
    $.ajax({
        url: url,
        type: 'POST',
        data: formData,
        processData: false,
        contentType: false,
        dataType: 'json',
        beforeSend: function() {
            $('#submitBtn').prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span>Saving...');
        },
        success: function(response) {
            contestModal.hide();
            showAlert('success', 'Success!', response.message || 'Contest saved successfully');
            loadContests(currentPage);
        },
        error: function(xhr) {
            let errorMsg = 'An error occurred';
            
            if (xhr.status === 422 && xhr.responseJSON?.errors) {
                errorMsg = Object.values(xhr.responseJSON.errors).flat().join('\n');
            } else if (xhr.responseJSON?.message) {
                errorMsg = xhr.responseJSON.message;
            }
            
            showAlert('error', 'Error!', errorMsg, 0);
        },
        complete: function() {
            $('#submitBtn').prop('disabled', false).html('<i class="fas fa-save me-1"></i>' + (editId ? 'Update Contest' : 'Save Contest'));
        }
    });
}

// Confirm delete
window.confirmDelete = function(id) {
    deleteId = id;
    deleteModal.show();
};

// Delete contest
function deleteContest() {
    if (!deleteId) return;
    
    $.ajax({
        url: "{{ route('admin.api.contests.delete', ['id' => '__ID__']) }}".replace('__ID__', deleteId),
        type: 'POST',
        data: {
            _method: 'DELETE',
            _token: "{{ csrf_token() }}"
        },
        beforeSend: function() {
            $('#confirmDeleteBtn').prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span>Deleting...');
        },
        success: function(response) {
            deleteModal.hide();
            showAlert('success', 'Deleted!', 'Contest has been deleted');
            loadContests(currentPage);
        },
        error: function(xhr) {
            deleteModal.hide();
            showAlert('error', 'Error!', xhr.responseJSON?.message || 'Failed to delete contest', 0);
        },
        complete: function() {
            $('#confirmDeleteBtn').prop('disabled', false).text('Delete');
            deleteId = null;
        }
    });
}

// Initialize everything when document is ready
$(document).ready(function() {
    
    // Initialize modals
    deleteModal = new bootstrap.Modal(document.getElementById('deleteModal'));
    contestModal = new bootstrap.Modal(document.getElementById('contestModal'));
    
    // Set minimum date for date inputs
    const today = new Date().toISOString().split('T')[0];
    $('#start_date').attr('min', today);
    $('#end_date').attr('min', today);
    
    // Date validation
    $('#start_date').on('change', function() {
        $('#end_date').attr('min', $(this).val());
        if ($('#end_date').val() && $('#end_date').val() < $(this).val()) {
            $('#end_date').val($(this).val());
        }
    });
    
    // Load contests initially
    loadContests();
    
    // Search with debounce
    $('#searchInput').on('keyup', debounce(function() {
        loadContests(1);
    }, 500));
    
    // Filter by status
    $('#statusFilter').on('change', function() {
        loadContests(1);
    });
    
    // Form submission
    $('#contestForm').on('submit', function(e) {
        e.preventDefault();
        saveContest();
    });
    
    // Delete confirmation
    $('#confirmDeleteBtn').on('click', function() {
        deleteContest();
    });
    
});

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
    
    #searchInput, #statusFilter {
        width: 100% !important;
        min-width: auto !important;
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