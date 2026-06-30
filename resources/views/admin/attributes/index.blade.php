@extends('admin.layouts.master')

@section('title','Attributes Management')

@section('content')
<div class="container-fluid py-4">

    <!-- HEADER -->
    <div class="card mb-3">
        <div class="card-body d-flex justify-content-between align-items-center">
            <div>
                <h4 class="mb-0">Attributes</h4>
                <small class="text-muted">Manage attributes & values</small>
            </div>

            <div class="d-flex gap-2">
                <input type="text" id="search" class="form-control" placeholder="Search...">
                <button class="btn btn-primary" onclick="openAttributeModal()">Add Attribute</button>
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
                        <th>Name</th>
                        <th>Values</th>
                        <th width="220">Action</th>
                    </tr>
                </thead>
                <tbody id="attributeTable"></tbody>
            </table>
        </div>
    </div>

</div>

<!-- ATTRIBUTE MODAL -->
<div class="modal fade" id="attributeModal">
    <div class="modal-dialog">
        <form id="attributeForm">
            @csrf

            <div class="modal-content">
                <div class="modal-header">
                    <h5 id="attrTitle">Add Attribute</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>

                <div class="modal-body">

                    <input type="hidden" id="attr_id">

                    <div class="mb-3">
                        <label>Name</label>
                        <input type="text" id="name" name="name" class="form-control">
                    </div>

                </div>

                <div class="modal-footer">
                    <button type="submit" id="attrBtn" class="btn btn-primary">
                        Save Attribute
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- VALUE MODAL -->
<div class="modal fade" id="valueModal">
    <div class="modal-dialog">
        <div class="modal-content">

            <div class="modal-header">
                <h5>Manage Values</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body">

                <input type="hidden" id="value_attr_id">

                <!-- ADD VALUE -->
                <div class="input-group mb-3">
                    <input type="text" id="value" class="form-control" placeholder="Enter value">
                    <button type="button" class="btn btn-success" id="addValueBtn">Add</button>
                </div>

                <!-- VALUE LIST -->
                <div id="valueList"></div>

            </div>

        </div>
    </div>
</div>

@endsection
@section('scripts')

<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
$(document).ready(function(){

    let editId = null;
    let currentAttrId = null;
    const csrfToken = $('meta[name="csrf-token"]').attr('content');

    // ROUTES - Using proper named routes
    const routes = {
        list: "{{ route('admin.api.attributes.index') }}",
        store: "{{ route('admin.api.attributes.store') }}",
        update: (id) => "{{ route('admin.api.attributes.update', ['id' => '__ID__']) }}".replace('__ID__', id),
        delete: (id) => "{{ route('admin.api.attributes.delete', ['id' => '__ID__']) }}".replace('__ID__', id),
        valueStore: "{{ route('admin.api.attribute.values.store') }}",
        valueDelete: (id) => "{{ route('admin.api.attribute.values.delete', ['id' => '__ID__']) }}".replace('__ID__', id)
    };

    // BUTTON STATES
    const buttonStates = {
        normal: '<i class="fas fa-save me-1"></i>Save Attribute',
        processing: '<span class="spinner-border spinner-border-sm me-1"></span>Processing...'
    };

    // INITIALIZE
    loadAttributes();

    // SEARCH
    $('#search').on('keyup', function() {
        loadAttributes();
    });

    // LOAD ATTRIBUTES
    function loadAttributes() {
        $.ajax({
            url: routes.list,
            type: 'GET',
            data: { 
                search: $('#search').val() 
            },
            dataType: 'json',
            beforeSend: function() {
                $('#attributeTable').html(`
                    <tr>
                        <td colspan="4" class="text-center py-4">
                            <div class="spinner-border text-primary" role="status">
                                <span class="visually-hidden">Loading...</span>
                            </div>
                        </td>
                    </tr>
                `);
            },
            success: function(res) {
                let rows = '';
                let list = res.data || [];

                if (!list.length) {
                    $('#attributeTable').html('<tr><td colspan="4" class="text-center py-4">No attributes found</td></tr>');
                    return;
                }

                list.forEach((item, i) => {
                    let values = '';
                    
                    if (item.values && item.values.length) {
                        values = item.values.map(v => `
                            <span class="badge bg-info me-1 mb-1">
                                ${escapeHtml(v.value)}
                                <i class="fas fa-times ms-1" 
                                   style="cursor:pointer" 
                                   onclick="deleteValue(${v.id}); event.stopPropagation();"></i>
                            </span>
                        `).join('');
                    } else {
                        values = '<span class="text-muted">No values</span>';
                    }

                    // Create clean data object for edit
                    const attrData = {
                        id: item.id,
                        name: item.name
                    };

                    rows += `
                    <tr>
                        <td>${i + 1}</td>
                        <td>${escapeHtml(item.name)}</td>
                        <td>${values}</td>
                        <td>
                            <div class="btn-group" role="group">
                                <button type="button" class="btn btn-sm btn-primary" onclick='editAttribute(${JSON.stringify(attrData)})'>
                                    <i class="fas fa-edit"></i> Edit
                                </button>
                                <button type="button" class="btn btn-sm btn-success" onclick='openValueModal(${item.id})'>
                                    <i class="fas fa-list"></i> Values
                                </button>
                                <button type="button" class="btn btn-sm btn-danger" onclick='deleteAttribute(${item.id})'>
                                    <i class="fas fa-trash"></i> Delete
                                </button>
                            </div>
                        </td>
                    </tr>`;
                });

                $('#attributeTable').html(rows);
            },
            error: function(xhr) {
                console.error('Error loading attributes:', xhr);
                $('#attributeTable').html(`
                    <tr>
                        <td colspan="4" class="text-center text-danger py-4">
                            Error loading attributes. Please refresh the page.
                        </td>
                    </tr>
                `);
            }
        });
    }

    // ATTRIBUTE FORM SUBMIT
    $('#attributeForm').on('submit', function(e) {
        e.preventDefault();

        let $btn = $('#attrBtn');
        $btn.prop('disabled', true).html(buttonStates.processing);

        let formData = $(this).serialize();
        let url = editId ? routes.update(editId) : routes.store;

        if (editId) {
            formData += '&_method=PUT';
        }

        // Add CSRF token if not in form
        if (!formData.includes('_token')) {
            formData += `&_token=${csrfToken}`;
        }

        console.log('Submitting to:', url);
        console.log('Edit ID:', editId);

        $.ajax({
            url: url,
            type: 'POST',
            data: formData,
            dataType: 'json',
            headers: {
                'X-CSRF-TOKEN': csrfToken,
                'Accept': 'application/json'
            },
            success: function(res) {
                if (res.success) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Success!',
                        text: res.message || 'Attribute saved successfully',
                        timer: 2000,
                        showConfirmButton: true
                    });
                    
                    closeModal('#attributeModal');
                    loadAttributes();
                    editId = null;
                } else {
                    Swal.fire('Error', res.message || 'Something went wrong', 'error');
                }
            },
            error: function(xhr) {
                let errorMessage = 'An error occurred';
                
                if (xhr.status === 422 && xhr.responseJSON?.errors) {
                    const errors = xhr.responseJSON.errors;
                    errorMessage = Object.values(errors).flat().join('\n');
                } else if (xhr.responseJSON?.message) {
                    errorMessage = xhr.responseJSON.message;
                } else if (xhr.status === 404) {
                    errorMessage = 'Endpoint not found. Check route configuration.';
                } else if (xhr.status === 405) {
                    errorMessage = 'Method not allowed. Check if route accepts PUT requests.';
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

    // ADD VALUE
    $('#addValueBtn').on('click', function() {
        let val = $('#value').val().trim();

        if (!val) {
            Swal.fire({
                icon: 'warning',
                title: 'Validation Error',
                text: 'Please enter a value'
            });
            return;
        }

        let $btn = $(this);
        let originalText = $btn.text();
        $btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm"></span>');

        $.ajax({
            url: routes.valueStore,
            type: 'POST',
            data: {
                attribute_id: currentAttrId,
                value: val,
                _token: csrfToken
            },
            dataType: 'json',
            success: function(res) {
                if (res.success) {
                    $('#value').val('');
                    loadValues(currentAttrId);
                    loadAttributes();
                    
                    Swal.fire({
                        icon: 'success',
                        title: 'Success!',
                        text: 'Value added successfully',
                        toast: true,
                        position: 'top-end',
                        showConfirmButton: false,
                        timer: 1500
                    });
                } else {
                    Swal.fire('Error', res.message || 'Failed to add value', 'error');
                }
            },
            error: function(xhr) {
                let errorMessage = 'Failed to add value';
                if (xhr.responseJSON?.message) {
                    errorMessage = xhr.responseJSON.message;
                }
                Swal.fire('Error', errorMessage, 'error');
            },
            complete: function() {
                $btn.prop('disabled', false).html(originalText);
            }
        });
    });

    // LOAD VALUES FOR ATTRIBUTE
    function loadValues(id) {
        $.ajax({
            url: routes.list,
            type: 'GET',
            dataType: 'json',
            success: function(res) {
                let attr = res.data.find(a => a.id == id);
                
                if (!attr) {
                    $('#valueList').html('<p class="text-muted">Attribute not found</p>');
                    return;
                }

                let html = '';
                
                if (attr.values && attr.values.length) {
                    attr.values.forEach(v => {
                        html += `
                        <div class="d-flex justify-content-between align-items-center mb-2 p-2 border rounded">
                            <span>${escapeHtml(v.value)}</span>
                            <button type="button" class="btn btn-sm btn-outline-danger" onclick="deleteValue(${v.id})">
                                <i class="fas fa-trash"></i>
                            </button>
                        </div>`;
                    });
                } else {
                    html = '<p class="text-muted text-center">No values added yet</p>';
                }

                $('#valueList').html(html);
            },
            error: function() {
                $('#valueList').html('<p class="text-danger">Error loading values</p>');
            }
        });
    }

    // ESCAPE HTML
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

    // GLOBAL FUNCTIONS
    window.openAttributeModal = function() {
        editId = null;
        $('#attributeForm')[0].reset();
        $('#attr_id').val('');
        $('#attrTitle').html('<i class="fas fa-plus me-2"></i>Add Attribute');
        $('#attrBtn').prop('disabled', false).html(buttonStates.normal);
        new bootstrap.Modal(document.getElementById('attributeModal')).show();
    };

    window.editAttribute = function(data) {
        editId = data.id;
        $('#attr_id').val(data.id);
        $('#name').val(data.name);
        $('#attrTitle').html('<i class="fas fa-edit me-2"></i>Edit Attribute');
        $('#attrBtn').prop('disabled', false).html(buttonStates.normal);
        new bootstrap.Modal(document.getElementById('attributeModal')).show();
    };

    window.openValueModal = function(id) {
        currentAttrId = id;
        $('#value_attr_id').val(id);
        loadValues(id);
        new bootstrap.Modal(document.getElementById('valueModal')).show();
    };

    window.deleteAttribute = function(id) {
        Swal.fire({
            title: 'Are you sure?',
            text: "This will also delete all associated values!",
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
                            didOpen: () => {
                                Swal.showLoading();
                            }
                        });
                    },
                    success: function(res) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Deleted!',
                            text: res.message || 'Attribute has been deleted.',
                            timer: 2000,
                            showConfirmButton: true
                        });
                        loadAttributes();
                    },
                    error: function(xhr) {
                        console.error('Delete error:', xhr);
                        
                        let errorMessage = 'Failed to delete attribute';
                        
                        if (xhr.status === 404) {
                            errorMessage = 'Attribute not found';
                        } else if (xhr.status === 403) {
                            errorMessage = 'You are not authorized to delete this attribute';
                        } else if (xhr.responseJSON?.message) {
                            errorMessage = xhr.responseJSON.message;
                        }
                        
                        Swal.fire({
                            icon: 'error',
                            title: 'Error!',
                            text: errorMessage
                        });
                    }
                });
            }
        });
    };

    window.deleteValue = function(id) {
        Swal.fire({
            title: 'Delete value?',
            text: "This action cannot be undone!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Yes, delete it!'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: routes.valueDelete(id),
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
                    success: function(res) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Deleted!',
                            text: 'Value has been deleted.',
                            toast: true,
                            position: 'top-end',
                            showConfirmButton: false,
                            timer: 1500
                        });
                        
                        if (currentAttrId) {
                            loadValues(currentAttrId);
                        }
                        loadAttributes();
                    },
                    error: function(xhr) {
                        console.error('Delete value error:', xhr);
                        
                        Swal.fire({
                            icon: 'error',
                            title: 'Error!',
                            text: xhr.responseJSON?.message || 'Failed to delete value'
                        });
                    }
                });
            }
        });
    };

    function closeModal(selector) {
        const modal = bootstrap.Modal.getInstance(document.querySelector(selector));
        if (modal) {
            modal.hide();
        }
    }

    // Reset form when modal is closed
    $('#attributeModal').on('hidden.bs.modal', function() {
        if (!editId) {
            $('#attributeForm')[0].reset();
        }
    });

    // Allow Enter key to add value
    $('#value').on('keypress', function(e) {
        if (e.which === 13) {
            e.preventDefault();
            $('#addValueBtn').click();
        }
    });

});
</script>

@endsection