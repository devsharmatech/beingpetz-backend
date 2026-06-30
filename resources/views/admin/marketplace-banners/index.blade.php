@extends('admin.layouts.master')

@section('title','Marketplace Banners Management')

@section('content')
<div class="container-fluid py-4">

    <!-- HEADER CARD -->
    <div class="card mb-3 shadow-sm">
        <div class="card-body">
            <div class="row align-items-center">
                <div class="col-md-6">
                    <h4 class="mb-0">
                        <i class="fas fa-images me-2 text-primary"></i>Marketplace Banners
                    </h4>
                    <small class="text-muted">Manage your marketplace promotional banners</small>
                </div>
                
                <div class="col-md-6">
                    <div class="d-flex justify-content-end gap-2">
                        <div class="position-relative">
                            <i class="fas fa-search position-absolute top-50 start-0 translate-middle-y ms-3 text-muted"></i>
                            <input type="text" 
                                   id="search" 
                                   class="form-control ps-5" 
                                   placeholder="Search banners..."
                                   style="min-width: 250px;">
                        </div>
                        <button class="btn btn-primary" onclick="openModal()">
                            <i class="fas fa-plus me-1"></i>Add Banner
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- TABLE CARD -->
    <div class="card shadow-sm">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th width="50">#</th>
                            <th width="120">Image</th>
                            <th>Title</th>
                            <th>Link</th>
                            <th width="100">Position</th>
                            <th width="100">Status</th>
                            <th width="150">Actions</th>
                        </tr>
                    </thead>
                    <tbody id="bannerTable">
                        <!-- Data will be loaded here -->
                    </tbody>
                </table>
            </div>
        </div>
        
        <!-- Pagination -->
        <div class="card-footer bg-white">
            <div id="pagination" class="d-flex justify-content-center"></div>
        </div>
    </div>

</div>

<!-- BANNER MODAL -->
<div class="modal fade" id="bannerModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <form id="bannerForm" enctype="multipart/form-data">
            @csrf
            
            <div class="modal-content">
                <div class="modal-header bg-light">
                    <h5 class="modal-title" id="modalTitle">
                        <i class="fas fa-plus-circle me-2"></i>Add New Banner
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>

                <div class="modal-body">
                    <input type="hidden" id="edit_id" name="edit_id">
                    
                    <div class="row">
                        <div class="col-md-8">
                            <div class="mb-3">
                                <label class="form-label fw-semibold">
                                    Banner Title <span class="text-danger">*</span>
                                </label>
                                <input type="text" 
                                       name="title" 
                                       id="banner_title" 
                                       class="form-control" 
                                       placeholder="Enter banner title"
                                       required>
                                <small class="text-muted">Maximum 255 characters</small>
                            </div>

                            <div class="mb-3">
                                <label class="form-label fw-semibold">Banner Link</label>
                                <input type="text" 
                                       name="link" 
                                       id="link" 
                                       class="form-control" 
                                       placeholder="https://example.com">
                                <small class="text-muted">Optional: URL where banner will redirect</small>
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label fw-semibold">
                                            Position <span class="text-danger">*</span>
                                        </label>
                                        <input type="number" 
                                               name="position" 
                                               id="position" 
                                               class="form-control" 
                                               placeholder="1"
                                               min="1"
                                               required>
                                        <small class="text-muted">Display order (1 = first)</small>
                                    </div>
                                </div>
                                
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label fw-semibold">Status</label>
                                        <select name="is_active" id="is_active" class="form-select">
                                            <option value="1">Active</option>
                                            <option value="0">Inactive</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label class="form-label fw-semibold">Banner Image</label>
                                <input type="file" 
                                       name="image" 
                                       id="image" 
                                       class="form-control" 
                                       accept="image/jpeg,image/png,image/jpg,image/gif,image/webp">
                                <small class="text-muted">Recommended size: 1200x400px</small>
                                
                                <div class="mt-3 border rounded p-2 bg-light">
                                    <img id="preview" 
                                         class="img-fluid rounded" 
                                         style="width:100%; max-height:150px; object-fit:cover; display:none;">
                                    <div id="noImage" class="text-center py-4 text-muted">
                                        <i class="fas fa-image fa-2x mb-2"></i>
                                        <p class="mb-0 small">No image selected</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="fas fa-times me-1"></i>Cancel
                    </button>
                    <button type="submit" id="submitBtn" class="btn btn-primary">
                        <i class="fas fa-save me-1"></i>Save Banner
                    </button>
                </div>
            </div>
            
        </form>
    </div>
</div>

@endsection

@section('scripts')
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
$(document).ready(function() {
    
    // Global Variables
    let editId = null;
    let currentPage = 1;
    const csrfToken = $('meta[name="csrf-token"]').attr('content');
    
    // Button States
    const buttonStates = {
        normal: '<i class="fas fa-save me-1"></i>Save Banner',
        processing: '<span class="spinner-border spinner-border-sm me-1"></span>Processing...'
    };
    
    // Routes
    const routes = {
        list: "{{ route('admin.api.banners.index') }}",
        store: "{{ route('admin.api.banners.store') }}",
        update: (id) => "{{ route('admin.api.banners.update', ['id' => '__ID__']) }}".replace('__ID__', id),
        delete: (id) => "{{ route('admin.api.banners.delete', ['id' => '__ID__']) }}".replace('__ID__', id)
    };
    
    // Initialize
    loadBanners();
    
    // Search with debounce
    let searchTimeout;
    $('#search').on('keyup', function() {
        clearTimeout(searchTimeout);
        searchTimeout = setTimeout(() => {
            loadBanners();
        }, 300);
    });
    
    // Image Preview
    $('#image').on('change', function() {
        const file = this.files[0];
        if (file) {
            // Check file size (max 5MB)
            if (file.size > 5 * 1024 * 1024) {
                Swal.fire({
                    icon: 'error',
                    title: 'File Too Large',
                    text: 'Image size should not exceed 5MB'
                });
                this.value = '';
                return;
            }
            
            // Check file type
            const validTypes = ['image/jpeg', 'image/png', 'image/jpg', 'image/gif', 'image/webp'];
            if (!validTypes.includes(file.type)) {
                Swal.fire({
                    icon: 'error',
                    title: 'Invalid File Type',
                    text: 'Please select a valid image file (JPEG, PNG, GIF, WEBP)'
                });
                this.value = '';
                return;
            }
            
            const reader = new FileReader();
            reader.onload = function(e) {
                $('#preview').attr('src', e.target.result).show();
                $('#noImage').hide();
            };
            reader.readAsDataURL(file);
        } else {
            $('#preview').hide();
            $('#noImage').show();
        }
    });
    
    // Form Submit
    $('#bannerForm').on('submit', function(e) {
        e.preventDefault();
        
        const $btn = $('#submitBtn');
        const originalHtml = $btn.html();
        
        // Validate required fields
        const title = $('#banner_title').val().trim();
        const position = $('#position').val();
        
        if (!title) {
            Swal.fire({
                icon: 'warning',
                title: 'Validation Error',
                text: 'Banner title is required'
            });
            return;
        }
        
        if (!position || position < 1) {
            Swal.fire({
                icon: 'warning',
                title: 'Validation Error',
                text: 'Please enter a valid position (minimum 1)'
            });
            return;
        }
        
        // Set loading state
        $btn.prop('disabled', true).html(buttonStates.processing);
        
        const formData = new FormData(this);
        const url = editId ? routes.update(editId) : routes.store;
        
        if (editId) {
            formData.append('_method', 'PUT');
        }
        
        // Add CSRF token if not present
        if (!formData.has('_token')) {
            formData.append('_token', csrfToken);
        }
        
        console.log('Submitting to:', url);
        console.log('Edit ID:', editId);
        
        $.ajax({
            url: url,
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            dataType: 'json',
            headers: {
                'X-CSRF-TOKEN': csrfToken,
                'Accept': 'application/json'
            },
            success: function(response) {
                console.log('Success:', response);
                
                if (response.success) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Success!',
                        text: response.message || 'Banner saved successfully',
                        timer: 2000,
                        showConfirmButton: true
                    });
                    
                    $('#bannerModal').modal('hide');
                    loadBanners(currentPage);
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: response.message || 'Failed to save banner'
                    });
                }
            },
            error: function(xhr) {
                console.error('Error:', xhr);
                
                let errorMessage = 'An error occurred while saving';
                
                if (xhr.status === 422 && xhr.responseJSON?.errors) {
                    const errors = xhr.responseJSON.errors;
                    errorMessage = Object.values(errors).flat().join('\n');
                } else if (xhr.responseJSON?.message) {
                    errorMessage = xhr.responseJSON.message;
                } else if (xhr.status === 404) {
                    errorMessage = 'API endpoint not found';
                } else if (xhr.status === 500) {
                    errorMessage = 'Server error. Please try again.';
                }
                
                Swal.fire({
                    icon: 'error',
                    title: 'Error!',
                    text: errorMessage
                });
            },
            complete: function() {
                $btn.prop('disabled', false).html(buttonStates.normal);
            }
        });
    });
    
    // Load Banners
    function loadBanners(page = 1) {
        currentPage = page;
        const searchTerm = $('#search').val();
        
        $.ajax({
            url: routes.list,
            type: 'GET',
            data: {
                page: page,
                search: searchTerm
            },
            dataType: 'json',
            beforeSend: function() {
                $('#bannerTable').html(`
                    <tr>
                        <td colspan="7" class="text-center py-5">
                            <div class="spinner-border text-primary" role="status">
                                <span class="visually-hidden">Loading...</span>
                            </div>
                            <p class="mt-2 text-muted">Loading banners...</p>
                        </td>
                    </tr>
                `);
            },
            success: function(response) {
                renderBanners(response);
            },
            error: function(xhr) {
                console.error('Load error:', xhr);
                $('#bannerTable').html(`
                    <tr>
                        <td colspan="7" class="text-center text-danger py-5">
                            <i class="fas fa-exclamation-circle fa-2x mb-2"></i>
                            <p>Error loading banners. Please refresh the page.</p>
                        </td>
                    </tr>
                `);
            }
        });
    }
    
    // Render Banners
    function renderBanners(response) {
        const list = response.data?.data || response.data || [];
        let rows = '';
        
        if (!list.length) {
            $('#bannerTable').html(`
                <tr>
                    <td colspan="7" class="text-center py-5">
                        <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
                        <p class="text-muted">No banners found</p>
                        <button class="btn btn-primary btn-sm" onclick="openModal()">
                            <i class="fas fa-plus me-1"></i>Add New Banner
                        </button>
                    </td>
                </tr>
            `);
            $('#pagination').empty();
            return;
        }
        
        list.forEach((item, index) => {
            const serialNumber = (response.data?.from || 1) + index;
            const statusBadge = item.is_active 
                ? '<span class="badge bg-success">Active</span>' 
                : '<span class="badge bg-secondary">Inactive</span>';
            
            // Create clean data object for edit
            const bannerData = {
                id: item.id,
                title: item.title,
                link: item.link || '',
                position: item.position,
                is_active: item.is_active,
                image_url: item.image_url || ''
            };
            
            rows += `
                <tr>
                    <td>${serialNumber}</td>
                    <td>
                        ${item.image_url 
                            ? `<img src="${item.image_url}" class="rounded" width="80" height="50" style="object-fit:cover;" alt="${escapeHtml(item.title)}">` 
                            : '<span class="text-muted">No Image</span>'
                        }
                    </td>
                    <td>
                        <strong>${escapeHtml(item.title)}</strong>
                    </td>
                    <td>
                        ${item.link 
                            ? `<a href="${escapeHtml(item.link)}" target="_blank" class="text-primary text-decoration-none">
                                   <i class="fas fa-external-link-alt me-1"></i>${truncateText(item.link, 30)}
                               </a>` 
                            : '<span class="text-muted">-</span>'
                        }
                    </td>
                    <td>
                        <span class="badge bg-info">Position ${item.position}</span>
                    </td>
                    <td>${statusBadge}</td>
                    <td>
                        <div class="btn-group" role="group">
                            <button type="button" 
                                    class="btn btn-sm btn-outline-primary" 
                                    onclick='editBanner(${JSON.stringify(bannerData).replace(/'/g, "\\'")})'
                                    title="Edit Banner">
                                <i class="fas fa-edit"></i>
                            </button>
                            <button type="button" 
                                    class="btn btn-sm btn-outline-danger" 
                                    onclick='deleteBanner(${item.id})'
                                    title="Delete Banner">
                                <i class="fas fa-trash"></i>
                            </button>
                        </div>
                    </td>
                </tr>
            `;
        });
        
        $('#bannerTable').html(rows);
        
        if (response.data && response.data.last_page > 1) {
            renderPagination(response.data);
        } else {
            $('#pagination').empty();
        }
    }
    
    // Render Pagination
    function renderPagination(data) {
        let html = '<nav><ul class="pagination pagination-sm mb-0">';
        
        // Previous
        html += `
            <li class="page-item ${data.current_page === 1 ? 'disabled' : ''}">
                <a class="page-link" href="javascript:void(0)" onclick="loadBanners(${data.current_page - 1})">
                    <i class="fas fa-chevron-left"></i>
                </a>
            </li>
        `;
        
        // Page numbers
        for (let i = 1; i <= data.last_page; i++) {
            if (i === 1 || i === data.last_page || (i >= data.current_page - 2 && i <= data.current_page + 2)) {
                html += `
                    <li class="page-item ${i === data.current_page ? 'active' : ''}">
                        <a class="page-link" href="javascript:void(0)" onclick="loadBanners(${i})">${i}</a>
                    </li>
                `;
            } else if (i === data.current_page - 3 || i === data.current_page + 3) {
                html += '<li class="page-item disabled"><span class="page-link">...</span></li>';
            }
        }
        
        // Next
        html += `
            <li class="page-item ${data.current_page === data.last_page ? 'disabled' : ''}">
                <a class="page-link" href="javascript:void(0)" onclick="loadBanners(${data.current_page + 1})">
                    <i class="fas fa-chevron-right"></i>
                </a>
            </li>
        `;
        
        html += '</ul></nav>';
        $('#pagination').html(html);
    }
    
    // Utility Functions
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
    
    function truncateText(text, length) {
        if (!text) return '';
        return text.length > length ? text.substring(0, length) + '...' : text;
    }
    
    // Global Functions
    window.loadBanners = loadBanners;
    
    window.openModal = function() {
        editId = null;
        $('#bannerForm')[0].reset();
        $('#edit_id').val('');
        $('#modalTitle').html('<i class="fas fa-plus-circle me-2"></i>Add New Banner');
        $('#preview').hide();
        $('#noImage').show();
        $('#submitBtn').prop('disabled', false).html(buttonStates.normal);
        new bootstrap.Modal(document.getElementById('bannerModal')).show();
    };
    
    window.editBanner = function(data) {
        console.log('Editing banner:', data);
        
        editId = data.id;
        $('#edit_id').val(data.id);
        $('#banner_title').val(data.title);
        $('#link').val(data.link || '');
        $('#position').val(data.position);
        $('#is_active').val(data.is_active ? '1' : '0');
        
        if (data.image_url) {
            $('#preview').attr('src', data.image_url).show();
            $('#noImage').hide();
        } else {
            $('#preview').hide();
            $('#noImage').show();
        }
        
        // Clear file input
        $('#image').val('');
        
        $('#modalTitle').html('<i class="fas fa-edit me-2"></i>Edit Banner');
        $('#submitBtn').prop('disabled', false).html(buttonStates.normal);
        new bootstrap.Modal(document.getElementById('bannerModal')).show();
    };
    
    window.deleteBanner = function(id) {
        Swal.fire({
            title: 'Are you sure?',
            text: "This banner will be permanently deleted!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Yes, delete it!',
            cancelButtonText: 'Cancel'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: routes.delete(id),
                    type: 'POST',
                    data: {
                        _method: 'DELETE',
                        _token: csrfToken
                    },
                    dataType: 'json',
                    headers: {
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept': 'application/json'
                    },
                    beforeSend: function() {
                        Swal.fire({
                            title: 'Deleting...',
                            text: 'Please wait',
                            allowOutsideClick: false,
                            didOpen: () => Swal.showLoading()
                        });
                    },
                    success: function(response) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Deleted!',
                            text: response.message || 'Banner deleted successfully',
                            timer: 2000,
                            showConfirmButton: true
                        });
                        loadBanners(currentPage);
                    },
                    error: function(xhr) {
                        console.error('Delete error:', xhr);
                        Swal.fire({
                            icon: 'error',
                            title: 'Error!',
                            text: xhr.responseJSON?.message || 'Failed to delete banner'
                        });
                    }
                });
            }
        });
    };
    
    // Modal reset on close
    $('#bannerModal').on('hidden.bs.modal', function() {
        if (!editId) {
            $('#bannerForm')[0].reset();
            $('#preview').hide();
            $('#noImage').show();
        }
    });
    
});
</script>
@endsection