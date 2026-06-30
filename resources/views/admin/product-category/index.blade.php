@extends('admin.layouts.master')

@section('title','Categories Management')

@section('content')
<div class="container-fluid py-4">

    <!-- HEADER -->
    <div class="card mb-3">
        <div class="card-body d-flex justify-content-between align-items-center">
            <div>
                <h4 class="mb-0">Categories</h4>
                <small class="text-muted">Manage categories</small>
            </div>

            <div class="d-flex gap-2">
                <input type="text" id="search" class="form-control" placeholder="Search...">
                <button class="btn btn-primary" onclick="openModal()">Add</button>
            </div>
        </div>
    </div>

    <!-- TABLE -->
    <div class="card">
        <div class="card-body">
            <table class="table table-hover align-middle">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Image</th>
                        <th>Name</th>
                        <th>Status</th>
                        <th width="150">Action</th>
                    </tr>
                </thead>
                <tbody id="categoryTable"></tbody>
            </table>

            <div id="pagination"></div>
        </div>
    </div>

</div>

<!-- MODAL -->
<div class="modal fade" id="categoryModal">
    <div class="modal-dialog">
        <form id="categoryForm" enctype="multipart/form-data">
            @csrf

            <div class="modal-content">
                <div class="modal-header">
                    <h5 id="modalTitle">Add Category</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>

                <div class="modal-body">

                    <input type="hidden" id="edit_id">

                    <div class="mb-3">
                        <label>Name</label>
                        <input type="text" id="name" name="name" class="form-control">
                    </div>

                    <div class="mb-3">
                        <label>Image</label>
                        <input type="file" id="image" name="image" class="form-control">
                        <img id="preview" style="width:80px;margin-top:10px;display:none;">
                    </div>

                    <div class="mb-3">
                        <label>Status</label>
                        <select id="is_active" name="is_active" class="form-control">
                            <option value="1">Active</option>
                            <option value="0">Inactive</option>
                        </select>
                    </div>

                </div>

                <div class="modal-footer">
                    <button class="btn btn-primary">Save</button>
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
    
    // Global variables
    let editId = null;
    let currentPage = 1;
    
    // DOM Elements
    const $categoryForm = $('#categoryForm');
    const $submitButton = $('#submitButton');
    const $modalTitle = $('#modalTitle');
    const $categoryModal = $('#categoryModal');
    const $searchInput = $('#search');
    const $imageInput = $('#image');
    const $previewImage = $('#preview');
    const $categoryTable = $('#categoryTable');
    const $pagination = $('#pagination');
    
    // Get CSRF token
    const csrfToken = $('meta[name="csrf-token"]').attr('content');
    
    // Button HTML templates
    const buttonStates = {
        normal: '<i class="fas fa-save me-1"></i>Save Category',
        processing: '<span class="spinner-border spinner-border-sm me-1" role="status" aria-hidden="true"></span>Processing...'
    };
    
    // ROUTES FROM LARAVEL - Matching your exact route definitions
    const routes = {
        list: "{{ route('admin.api.categories.index') }}",
        store: "{{ route('admin.api.categories.store') }}",
        show: (id) => "{{ route('admin.api.categories.show', ['id' => '__ID__']) }}".replace('__ID__', id),
        update: (id) => "{{ route('admin.api.categories.update', ['id' => '__ID__']) }}".replace('__ID__', id),
        delete: (id) => "{{ route('admin.api.categories.delete', ['id' => '__ID__']) }}".replace('__ID__', id)
    };
    
    // Initialize
    loadCategories();
    
    // Event Handlers
    $searchInput.on('keyup', function() {
        loadCategories();
    });
    
    $imageInput.on('change', function() {
        const file = this.files[0];
        if (file) {
            const reader = new FileReader();
            reader.onload = function(e) {
                $previewImage.attr('src', e.target.result).show();
            };
            reader.readAsDataURL(file);
        }
    });
    
    $categoryForm.on('submit', function(e) {
        e.preventDefault();
        handleFormSubmit();
    });
    
    // Modal reset when closed
    $categoryModal.on('hidden.bs.modal', function() {
        resetForm();
        editId = null;
    });
    
    /**
     * Handle form submission with proper state management
     */
    function handleFormSubmit() {
        const formData = new FormData($categoryForm[0]);
        
        // Determine URL and method
        let url, method;
        
        if (editId) {
            // For update, use PUT method directly since your route accepts PUT
            url = routes.update(editId);
            // Add method spoofing for Laravel if needed
            formData.append('_method', 'PUT');
        } else {
            url = routes.store;
        }
        
        // Add CSRF token to formData if not already in form
        if (!formData.has('_token')) {
            formData.append('_token', csrfToken);
        }
        
        console.log('Submitting to:', url);
        console.log('Edit ID:', editId);
        
        // Set button to processing state
        setSubmitButtonState('processing');
        
        $.ajax({
            url: url,
            type: 'POST', // Use POST with _method spoofing for PUT
            data: formData,
            processData: false,
            contentType: false,
            dataType: 'json',
            headers: {
                'X-CSRF-TOKEN': csrfToken,
                'Accept': 'application/json'
            },
            success: function(response) {
                console.log('Success response:', response);
                
                // Check for different success response formats
                const isSuccess = response.success || response.status === 'success' || response.message;
                
                if (isSuccess) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Success!',
                        text: response.message || 'Category saved successfully',
                        timer: 2000,
                        showConfirmButton: true
                    });
                    
                    // Close modal and reload data
                    $categoryModal.modal('hide');
                    loadCategories(currentPage);
                } else {
                    handleAjaxError(response);
                }
            },
            error: function(xhr, status, error) {
                console.error('Error details:', {
                    status: xhr.status,
                    statusText: xhr.statusText,
                    responseText: xhr.responseText,
                    responseJSON: xhr.responseJSON
                });
                handleAjaxError(xhr);
            },
            complete: function() {
                // Reset button state after completion
                setSubmitButtonState('normal');
            }
        });
    }
    
    /**
     * Set submit button state (normal or processing)
     */
    function setSubmitButtonState(state) {
        if (state === 'processing') {
            $submitButton
                .html(buttonStates.processing)
                .prop('disabled', true)
                .addClass('disabled');
        } else {
            $submitButton
                .html(buttonStates.normal)
                .prop('disabled', false)
                .removeClass('disabled');
        }
    }
    
    /**
     * Handle AJAX errors
     */
    function handleAjaxError(xhr) {
        let errorMessage = 'An error occurred while processing your request.';
        
        if (xhr.responseJSON) {
            if (xhr.responseJSON.message) {
                errorMessage = xhr.responseJSON.message;
            } else if (xhr.responseJSON.errors) {
                // Laravel validation errors
                const errors = xhr.responseJSON.errors;
                errorMessage = Object.values(errors).flat().join('\n');
            }
        } else if (xhr.responseText) {
            try {
                const response = JSON.parse(xhr.responseText);
                errorMessage = response.message || response.error || errorMessage;
            } catch (e) {
                errorMessage = xhr.responseText || errorMessage;
            }
        }
        
        // Status code specific messages
        if (xhr.status === 404) {
            errorMessage = 'Resource not found. Please check the route configuration.';
        } else if (xhr.status === 405) {
            errorMessage = 'Method not allowed. Make sure the route accepts the request method.';
        } else if (xhr.status === 422) {
            errorMessage = 'Validation failed. Please check your input.';
        } else if (xhr.status === 419) {
            errorMessage = 'CSRF token mismatch. Please refresh the page.';
        } else if (xhr.status === 500) {
            errorMessage = 'Server error. Check Laravel logs for details.';
        }
        
        Swal.fire({
            icon: 'error',
            title: 'Error!',
            text: errorMessage
        });
    }
    
    /**
     * Reset form to initial state
     */
    function resetForm() {
        $categoryForm[0].reset();
        $previewImage.hide().attr('src', '#');
        $modalTitle.text('Add Category');
        setSubmitButtonState('normal');
        editId = null;
    }
    
    /**
     * Load categories with AJAX
     */
    function loadCategories(page = 1) {
        currentPage = page;
        const searchTerm = $searchInput.val();
        
        $.ajax({
            url: routes.list,
            type: 'GET',
            data: { 
                page: page, 
                search: searchTerm 
            },
            dataType: 'json',
            beforeSend: function() {
                $categoryTable.html(`
                    <tr>
                        <td colspan="5" class="text-center">
                            <div class="spinner-border text-primary" role="status">
                                <span class="visually-hidden">Loading...</span>
                            </div>
                        </td>
                    </tr>
                `);
            },
            success: function(response) {
                renderCategories(response);
            },
            error: function(xhr) {
                console.error('Error loading categories:', xhr);
                $categoryTable.html(`
                    <tr>
                        <td colspan="5" class="text-center text-danger">
                            Error loading categories. Please try again.
                        </td>
                    </tr>
                `);
            }
        });
    }
    
    /**
     * Render categories table
     */
    function renderCategories(response) {
        const list = response.data?.data || response.data || [];
        
        if (!list || list.length === 0) {
            $categoryTable.html(`
                <tr>
                    <td colspan="5" class="text-center text-muted py-4">
                        <i class="fas fa-inbox fa-2x mb-2 d-block"></i>
                        No categories found
                    </td>
                </tr>
            `);
            $pagination.empty();
            return;
        }
        
        let rows = '';
        
        list.forEach((item, index) => {
            const serialNumber = (response.data?.from || 1) + index;
            const imageHtml = item.image_url || item.image 
                ? `<img src="${item.image_url || item.image}" width="50" height="50" class="rounded" style="object-fit: cover;">` 
                : '<span class="text-muted">No Image</span>';
            
            const statusBadge = item.is_active 
                ? '<span class="badge bg-success">Active</span>' 
                : '<span class="badge bg-danger">Inactive</span>';
            
            rows += `
                <tr>
                    <td>${serialNumber}</td>
                    <td>${imageHtml}</td>
                    <td>${escapeHtml(item.name) || '-'}</td>
                    <td>${statusBadge}</td>
                    <td>
                        <button class="btn btn-sm btn-primary me-1" onclick="editCategory(${item.id})">
                            <i class="fas fa-edit"></i> Edit
                        </button>
                        <button class="btn btn-sm btn-danger" onclick="deleteCategory(${item.id})">
                            <i class="fas fa-trash"></i> Delete
                        </button>
                    </td>
                </tr>
            `;
        });
        
        $categoryTable.html(rows);
        
        if (response.data && response.data.last_page) {
            renderPagination(response.data);
        }
    }
    
    /**
     * Render pagination
     */
    function renderPagination(data) {
        if (!data || data.last_page <= 1) {
            $pagination.empty();
            return;
        }
        
        let html = '<nav aria-label="Category pagination"><ul class="pagination pagination-sm mb-0">';
        
        // Previous button
        html += `
            <li class="page-item ${data.current_page === 1 ? 'disabled' : ''}">
                <a class="page-link" href="javascript:void(0)" onclick="loadCategories(${data.current_page - 1})">
                    <i class="fas fa-chevron-left"></i>
                </a>
            </li>
        `;
        
        // Page numbers
        for (let i = 1; i <= data.last_page; i++) {
            if (i === 1 || i === data.last_page || (i >= data.current_page - 2 && i <= data.current_page + 2)) {
                html += `
                    <li class="page-item ${i === data.current_page ? 'active' : ''}">
                        <a class="page-link" href="javascript:void(0)" onclick="loadCategories(${i})">${i}</a>
                    </li>
                `;
            } else if (i === data.current_page - 3 || i === data.current_page + 3) {
                html += '<li class="page-item disabled"><span class="page-link">...</span></li>';
            }
        }
        
        // Next button
        html += `
            <li class="page-item ${data.current_page === data.last_page ? 'disabled' : ''}">
                <a class="page-link" href="javascript:void(0)" onclick="loadCategories(${data.current_page + 1})">
                    <i class="fas fa-chevron-right"></i>
                </a>
            </li>
        `;
        
        html += '</ul></nav>';
        $pagination.html(html);
    }
    
    /**
     * Escape HTML to prevent XSS
     */
    function escapeHtml(text) {
        if (!text) return '';
        
        const map = {
            '&': '&amp;',
            '<': '&lt;',
            '>': '&gt;',
            '"': '&quot;',
            "'": '&#039;'
        };
        
        return text.replace(/[&<>"']/g, function(m) { return map[m]; });
    }
    
    // Global functions for button clicks
    window.loadCategories = loadCategories;
    
    window.openModal = function() {
        resetForm();
        $modalTitle.text('Add Category');
        $categoryModal.modal('show');
    };
    
    window.editCategory = function(id) {
        // Fetch category data first
        $.ajax({
            url: routes.show(id),
            type: 'GET',
            dataType: 'json',
            headers: {
                'Accept': 'application/json'
            },
            success: function(response) {
                const category = response.data || response;
                
                editId = category.id;
                $('#name').val(category.name);
                $('#is_active').val(category.is_active ? '1' : '0');
                
                if (category.image || category.image_url) {
                    $previewImage.attr('src', category.image_url || category.image).show();
                }
                
                $modalTitle.text('Edit Category');
                $categoryModal.modal('show');
            },
            error: function(xhr) {
                console.error('Error loading category:', xhr);
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'Failed to load category details'
                });
            }
        });
    };
    
    window.deleteCategory = function(id) {
        Swal.fire({
            title: 'Are you sure?',
            text: "You won't be able to revert this!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Yes, delete it!'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: routes.delete(id),
                    type: 'DELETE', // Direct DELETE method
                    dataType: 'json',
                    headers: {
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept': 'application/json'
                    },
                    success: function(response) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Deleted!',
                            text: response.message || 'Category has been deleted.',
                            timer: 2000
                        });
                        loadCategories(currentPage);
                    },
                    error: function(xhr) {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: xhr.responseJSON?.message || 'Failed to delete category'
                        });
                    }
                });
            }
        });
    };
    
});
</script>
@endsection