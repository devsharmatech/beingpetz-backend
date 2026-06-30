@extends('admin.layouts.master')

@section('title','Company Management')

@section('content')
<div class="container-fluid py-4">

    <!-- HEADER -->
    <div class="card mb-3">
        <div class="card-body d-flex justify-content-between align-items-center">
            <div>
                <h4 class="mb-0">Companies</h4>
                <small class="text-muted">Manage companies</small>
            </div>

            <div class="d-flex gap-2">
                <input type="text" id="search" class="form-control" placeholder="Search...">
                <button class="btn btn-primary" onclick="openModal()">Add Company</button>
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
                        <th>Logo</th>
                        <th>Banner</th>
                        <th>Name</th>
                        <th>Description</th>
                        <th>Status</th>
                        <th width="160">Action</th>
                    </tr>
                </thead>
                <tbody id="companyTable"></tbody>
            </table>

            <div id="pagination" class="mt-3"></div>
        </div>
    </div>

</div>

<!-- MODAL -->
<div class="modal fade" id="companyModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <form id="companyForm" enctype="multipart/form-data">
            @csrf

            <div class="modal-content">
                <div class="modal-header">
                    <h5 id="modalTitle">Add Company</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>

                <div class="modal-body row">

                    <input type="hidden" id="edit_id">

                    <!-- NAME -->
                    <div class="col-md-6 mb-3">
                        <label>Company Name</label>
                        <input type="text" name="name" id="name" class="form-control">
                    </div>

                    <!-- STATUS -->
                    <div class="col-md-6 mb-3">
                        <label>Status</label>
                        <select name="is_active" id="is_active" class="form-control">
                            <option value="1">Active</option>
                            <option value="0">Inactive</option>
                        </select>
                    </div>

                    <!-- DESCRIPTION -->
                    <div class="col-md-12 mb-3">
                        <label>Description</label>
                        <textarea name="description" id="description" rows="4" class="form-control"></textarea>
                    </div>

                    <!-- LOGO -->
                    <div class="col-md-6 mb-3">
                        <label>Logo</label>
                        <input type="file" name="logo" id="logo" class="form-control">
                        <img id="logo_preview" style="width:60px;margin-top:10px;display:none;">
                    </div>

                    <!-- BANNER -->
                    <div class="col-md-6 mb-3">
                        <label>Banner</label>
                        <input type="file" name="banner" id="banner" class="form-control">
                        <img id="banner_preview" style="width:100%;max-height:120px;margin-top:10px;display:none;">
                    </div>

                </div>

                <div class="modal-footer">
                    <button type="submit" id="submitButton" class="btn btn-primary">
                        Save Company
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
$(document).ready(function(){

let editId = null;
let currentPage = 1;

// Get CSRF token
const csrfToken = $('meta[name="csrf-token"]').attr('content');

// ROUTES - Using named routes properly
const routes = {
    list: "{{ route('admin.api.companies.index') }}",
    store: "{{ route('admin.api.companies.store') }}",
    show: (id) => "{{ route('admin.api.companies.show', ['id' => '__ID__']) }}".replace('__ID__', id),
    update: (id) => "{{ route('admin.api.companies.update', ['id' => '__ID__']) }}".replace('__ID__', id),
    delete: (id) => "{{ route('admin.api.companies.delete', ['id' => '__ID__']) }}".replace('__ID__', id)
};

// BUTTON STATE
const btnText = {
    normal: '<i class="fas fa-save me-1"></i>Save Company',
    loading: '<span class="spinner-border spinner-border-sm me-1"></span>Processing...'
};

// INIT
loadCompanies();

// SEARCH
$('#search').on('keyup', function() {
    loadCompanies();
});

// IMAGE PREVIEW
$('#logo').on('change', function() { 
    preview(this, '#logo_preview'); 
});

$('#banner').on('change', function() { 
    preview(this, '#banner_preview'); 
});

// FORM SUBMIT
$('#companyForm').on('submit', function(e) {
    e.preventDefault();

    let $btn = $('#submitButton');
    let originalText = $btn.html();
    $btn.prop('disabled', true).html(btnText.loading);

    let formData = new FormData(this);
    
    // Add CSRF token if not present
    if (!formData.has('_token')) {
        formData.append('_token', csrfToken);
    }

    let url, method;
    
    if (editId) {
        // For update - use the correct route
        url = routes.update(editId);
        formData.append('_method', 'PUT');
        
        console.log('Updating company ID:', editId);
        console.log('Update URL:', url);
    } else {
        // For create
        url = routes.store;
        console.log('Creating new company');
        console.log('Store URL:', url);
    }

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
        success: function(res) {
            console.log('Success response:', res);
            
            if (res.success || res.status === 'success') {
                Swal.fire({
                    icon: 'success',
                    title: 'Success!',
                    text: res.message || 'Company saved successfully',
                    timer: 2000,
                    showConfirmButton: true
                });
                
                closeModal();
                loadCompanies();
            } else {
                Swal.fire('Error', res.message || 'Something went wrong', 'error');
            }
        },
        error: function(xhr) {
            console.error('Error response:', xhr);
            
            let errorMessage = 'An error occurred';
            
            if (xhr.status === 422 && xhr.responseJSON?.errors) {
                // Validation errors
                const errors = xhr.responseJSON.errors;
                errorMessage = Object.values(errors).flat().join('\n');
            } else if (xhr.responseJSON?.message) {
                errorMessage = xhr.responseJSON.message;
            } else if (xhr.status === 404) {
                errorMessage = 'Update endpoint not found. Check route configuration.';
            } else if (xhr.status === 405) {
                errorMessage = 'Method not allowed. Check if route accepts PUT requests.';
            } else if (xhr.status === 419) {
                errorMessage = 'Session expired. Please refresh the page.';
            } else if (xhr.status === 500) {
                errorMessage = 'Server error. Check Laravel logs.';
            }
            
            Swal.fire({
                icon: 'error',
                title: 'Error!',
                text: errorMessage
            });
        },
        complete: function() {
            $btn.prop('disabled', false).html(btnText.normal);
        }
    });
});

// Reset form when modal is closed
$('#companyModal').on('hidden.bs.modal', function() {
    editId = null;
    $('#companyForm')[0].reset();
    $('#logo_preview, #banner_preview').hide();
    $('#submitButton').prop('disabled', false).html(btnText.normal);
});

// LOAD DATA
function loadCompanies(page = 1) {
    currentPage = page;
    
    $.ajax({
        url: routes.list,
        type: 'GET',
        data: { 
            page: page, 
            search: $('#search').val() 
        },
        dataType: 'json',
        beforeSend: function() {
            $('#companyTable').html(`
                <tr>
                    <td colspan="7" class="text-center py-4">
                        <div class="spinner-border text-primary" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                    </td>
                </tr>
            `);
        },
        success: function(res) {
            let list = res.data?.data || res.data || [];
            let rows = '';

            if (!list || list.length === 0) {
                $('#companyTable').html('<tr><td colspan="7" class="text-center py-4">No companies found</td></tr>');
                $('#pagination').empty();
                return;
            }

            list.forEach((item, i) => {
                const serialNumber = (res.data?.from || 1) + i;
                
                // Create a clean data object for editing
                const companyData = {
                    id: item.id,
                    name: item.name,
                    description: item.description,
                    is_active: item.is_active,
                    logo_url: item.logo_url || null,
                    banner_url: item.banner_url || null
                };
                
                rows += `
                <tr>
                    <td>${serialNumber}</td>
                    <td>${item.logo_url ? `<img src="${item.logo_url}" width="40" height="40" style="object-fit: cover;" class="rounded">` : '-'}</td>
                    <td>${item.banner_url ? `<img src="${item.banner_url}" width="80" height="40" style="object-fit: cover;" class="rounded">` : '-'}</td>
                    <td>${escapeHtml(item.name) || '-'}</td>
                    <td style="max-width:200px;">${escapeHtml(item.description?.substring(0, 50)) || '-'}${item.description?.length > 50 ? '...' : ''}</td>
                    <td>
                        <span class="badge ${item.is_active ? 'bg-success' : 'bg-danger'}">
                            ${item.is_active ? 'Active' : 'Inactive'}
                        </span>
                    </td>
                    <td>
                        <button class="btn btn-sm btn-primary me-1" onclick='editCompanyData(${JSON.stringify(companyData).replace(/'/g, "\\'")})'>
                            <i class="fas fa-edit"></i> Edit
                        </button>
                        <button class="btn btn-sm btn-danger" onclick='deleteCompany(${item.id})'>
                            <i class="fas fa-trash"></i> Delete
                        </button>
                    </td>
                </tr>`;
            });

            $('#companyTable').html(rows);
            
            if (res.data && res.data.last_page) {
                renderPagination(res.data);
            }
        },
        error: function(xhr) {
            console.error('Error loading companies:', xhr);
            $('#companyTable').html(`
                <tr>
                    <td colspan="7" class="text-center text-danger py-4">
                        Error loading companies. Please try again.
                    </td>
                </tr>
            `);
        }
    });
}

// Escape HTML to prevent XSS
function escapeHtml(text) {
    if (!text) return '';
    
    const map = {
        '&': '&amp;',
        '<': '&lt;',
        '>': '&gt;',
        '"': '&quot;',
        "'": '&#039;'
    };
    
    return String(text).replace(/[&<>"']/g, function(m) { return map[m]; });
}

// EDIT - Fixed function name and implementation
window.editCompanyData = function(data) {
    console.log('Editing company:', data);
    
    editId = data.id;
    
    $('#modalTitle').text('Edit Company');
    $('#name').val(data.name);
    $('#description').val(data.description || '');
    $('#is_active').val(data.is_active ? '1' : '0');
    
    // Handle logo preview
    if (data.logo_url) {
        $('#logo_preview').attr('src', data.logo_url).show();
    } else {
        $('#logo_preview').hide();
    }
    
    // Handle banner preview
    if (data.banner_url) {
        $('#banner_preview').attr('src', data.banner_url).show();
    } else {
        $('#banner_preview').hide();
    }
    
    // Clear file inputs
    $('#logo').val('');
    $('#banner').val('');
    
    new bootstrap.Modal(document.getElementById('companyModal')).show();
};

// DELETE
window.deleteCompany = function(id) {
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
                type: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json'
                },
                success: function(res) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Deleted!',
                        text: res.message || 'Company has been deleted.',
                        timer: 2000
                    });
                    loadCompanies();
                },
                error: function(xhr) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: xhr.responseJSON?.message || 'Failed to delete company'
                    });
                }
            });
        }
    });
};

// MODAL
window.openModal = function() {
    editId = null;
    
    $('#companyForm')[0].reset();
    $('#logo_preview, #banner_preview').hide();
    $('#modalTitle').text('Add Company');
    $('#submitButton').prop('disabled', false).html(btnText.normal);
    
    // Clear file inputs
    $('#logo').val('');
    $('#banner').val('');
    
    new bootstrap.Modal(document.getElementById('companyModal')).show();
};

function closeModal() {
    bootstrap.Modal.getInstance(document.getElementById('companyModal')).hide();
}

// IMAGE PREVIEW
function preview(input, target) {
    if (input.files && input.files[0]) {
        let reader = new FileReader();
        reader.onload = function(e) {
            $(target).attr('src', e.target.result).show();
        };
        reader.readAsDataURL(input.files[0]);
    }
}

// PAGINATION
function renderPagination(data) {
    if (!data || data.last_page <= 1) {
        $('#pagination').empty();
        return;
    }
    
    let html = '<nav><ul class="pagination pagination-sm mb-0">';
    
    // Previous button
    if (data.current_page > 1) {
        html += `<li class="page-item"><a class="page-link" href="javascript:void(0)" onclick="loadCompanies(${data.current_page - 1})">&laquo;</a></li>`;
    }
    
    // Page numbers
    for (let i = 1; i <= data.last_page; i++) {
        if (i === 1 || i === data.last_page || (i >= data.current_page - 2 && i <= data.current_page + 2)) {
            html += `<li class="page-item ${i === data.current_page ? 'active' : ''}">
                        <a class="page-link" href="javascript:void(0)" onclick="loadCompanies(${i})">${i}</a>
                    </li>`;
        } else if (i === data.current_page - 3 || i === data.current_page + 3) {
            html += '<li class="page-item disabled"><span class="page-link">...</span></li>';
        }
    }
    
    // Next button
    if (data.current_page < data.last_page) {
        html += `<li class="page-item"><a class="page-link" href="javascript:void(0)" onclick="loadCompanies(${data.current_page + 1})">&raquo;</a></li>`;
    }
    
    html += '</ul></nav>';
    $('#pagination').html(html);
}

// Expose loadCompanies globally
window.loadCompanies = loadCompanies;

});
</script>

@endsection