@extends('admin.layouts.master')

@section('title', 'Products Management')

@section('content')
<div class="container-fluid py-4">
    
    <!-- Header Section -->
    <div class="card shadow-sm mb-4">
        <div class="card-body">
            <div class="row align-items-center">
                <div class="col-md-4">
                    <h4 class="mb-0">
                        <i class="fas fa-box me-2 text-primary"></i>Products Management
                    </h4>
                    <small class="text-muted">Manage your product catalog and variants</small>
                </div>
                
                <div class="col-md-8">
                    <div class="row g-2 justify-content-end">
                        <div class="col-md-3">
                            <select id="filterCompany" class="form-select">
                                <option value="">All Companies</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <select id="filterCategory" class="form-select">
                                <option value="">All Categories</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <select id="filterStatus" class="form-select">
                                <option value="">All Status</option>
                                <option value="1">Active</option>
                                <option value="0">Inactive</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <div class="d-flex gap-2">
                                <div class="position-relative flex-grow-1">
                                    <input type="text" id="searchInput" class="form-control " placeholder="Search...">
                                </div>
                                <button class="btn btn-primary" onclick="openProductModal()">
                                    <i class="fas fa-plus"></i> Add
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Products Table -->
    <div class="card shadow-sm">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th width="50">#</th>
                            <th width="80">Image</th>
                            <th>Product Details</th>
                            <th width="150">Company/Category</th>
                            <th width="120">Variants</th>
                            <th width="100">Status</th>
                            <th width="180">Actions</th>
                        </tr>
                    </thead>
                    <tbody id="productsTable"></tbody>
                </table>
            </div>
        </div>
        
        <div class="card-footer bg-white">
            <div id="pagination" class="d-flex justify-content-center"></div>
        </div>
    </div>
</div>

<!-- Product Modal -->
<div class="modal fade" id="productModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <form id="productForm" enctype="multipart/form-data">
            @csrf
            <div class="modal-content">
                <div class="modal-header bg-light">
                    <h5 class="modal-title" id="productModalTitle">
                        <i class="fas fa-box me-2"></i>Add New Product
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-8">
                            <div class="mb-3">
                                <label class="form-label">Product Name <span class="text-danger">*</span></label>
                                <input type="text" name="name" id="name" class="form-control" required>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label">Base Price</label>
                                        <input type="number" name="base_price" id="base_price" class="form-control" step="0.01" min="0">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label">Sale Price</label>
                                        <input type="number" name="sale_price" id="sale_price" class="form-control" step="0.01" min="0">
                                    </div>
                                </div>
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label">Stock</label>
                                <input type="number" name="stock" id="stock" class="form-control" min="0">
                            </div>
                            
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label">Company <span class="text-danger">*</span></label>
                                        <select name="company_id" id="company_id" class="form-select" required>
                                            <option value="">Select Company</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label">Category <span class="text-danger">*</span></label>
                                        <select name="category_id" id="category_id" class="form-select" required>
                                            <option value="">Select Category</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label">Description</label>
                                <textarea name="description" id="description" class="form-control" rows="3"></textarea>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-3">
                                    <div class="form-check">
                                        <input type="checkbox" name="is_featured" id="is_featured" class="form-check-input" value="1">
                                        <label class="form-check-label" for="is_featured">Featured</label>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-check">
                                        <input type="checkbox" name="is_trending" id="is_trending" class="form-check-input" value="1">
                                        <label class="form-check-label" for="is_trending">Trending</label>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-check">
                                        <input type="checkbox" name="is_best_seller" id="is_best_seller" class="form-check-input" value="1">
                                        <label class="form-check-label" for="is_best_seller">Best Seller</label>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-check">
                                        <input type="checkbox" name="is_new" id="is_new" class="form-check-input" value="1">
                                        <label class="form-check-label" for="is_new">New</label>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label">Status</label>
                                <select name="is_active" id="is_active" class="form-select">
                                    <option value="1">Active</option>
                                    <option value="0">Inactive</option>
                                </select>
                            </div>
                        </div>
                        
                       <!-- In the Product Modal - Replace the image section -->
<div class="col-md-4">
    <div class="mb-3">
        <label class="form-label">Primary Image</label>
        <input type="file" name="primary_image" id="productImage" class="form-control" accept="image/*">
        <small class="text-muted">Max size: 5MB</small>
        
        <div class="mt-3 border rounded p-2 bg-light">
            <img id="imagePreview" class="img-fluid rounded" style="width:100%; max-height:200px; object-fit:cover; display:none;">
        </div>
    </div>
    
    <div class="mb-3">
        <label class="form-label">Gallery Images</label>
        <input type="file" name="gallery_images[]" id="galleryImages" class="form-control" accept="image/*" multiple>
        <small class="text-muted">You can select multiple images</small>
        
        <div id="galleryPreview" class="row g-2 mt-2"></div>
    </div>
    
    <!-- Existing Gallery Images (for edit) -->
    <div id="existingGallery" class="row g-2 mt-2"></div>
</div>
                    </div>
                </div>
                
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="fas fa-times me-1"></i>Cancel
                    </button>
                    <button type="submit" id="productSubmitBtn" class="btn btn-primary">
                        <i class="fas fa-save me-1"></i>Save Product
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Variant Modal -->
<div class="modal fade" id="variantModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-light">
                <h5 class="modal-title">
                    <i class="fas fa-list me-2"></i>Manage Product Variants
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            
            <div class="modal-body">
                <div class="mb-4">
                    <h6 class="fw-bold mb-3">Existing Variants</h6>
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <thead class="table-light">
                                <tr>
                                    <th width="60">Image</th>
                                    <th>SKU / Attributes</th>
                                    <th width="100">Price</th>
                                    <th width="100">Sale Price</th>
                                    <th width="80">Stock</th>
                                    <th width="80">Action</th>
                                </tr>
                            </thead>
                            <tbody id="variantsTable"></tbody>
                        </table>
                    </div>
                </div>
                
                <hr>
                <h6 class="fw-bold mb-3">Add New Variant</h6>
                
                <form id="variantForm" enctype="multipart/form-data">
                    @csrf
                    <input type="hidden" name="product_id" id="variant_product_id">
                    
                    <div class="row">
                        <div class="col-md-8">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label">SKU <span class="text-danger">*</span></label>
                                        <input type="text" name="sku" class="form-control" required>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label">Stock <span class="text-danger">*</span></label>
                                        <input type="number" name="stock" class="form-control" min="0" required>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label">Price <span class="text-danger">*</span></label>
                                        <input type="number" name="price" class="form-control" step="0.01" min="0" required>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label">Sale Price</label>
                                        <input type="number" name="sale_price" class="form-control" step="0.01" min="0">
                                    </div>
                                </div>
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label">Attributes</label>
                                <div id="attributeValuesContainer">
                                    <div class="text-muted">Loading attributes...</div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label class="form-label">Variant Image</label>
                                <input type="file" name="image" id="variantImage" class="form-control" accept="image/*">
                                
                                <div class="mt-3 border rounded p-2 bg-light">
                                    <img id="variantImagePreview" class="img-fluid rounded" style="width:100%; max-height:150px; object-fit:cover; display:none;">
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="text-end">
                        <button type="submit" id="variantSubmitBtn" class="btn btn-primary">
                            <i class="fas fa-plus me-1"></i>Add Variant
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

@endsection

@section('scripts')
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
$(document).ready(function() {
    
    let editId = null;
    let currentPage = 1;
    let currentProductId = null;
    let attributesList = [];
    const csrfToken = $('meta[name="csrf-token"]').attr('content');
    
    const buttonStates = {
        normal: '<i class="fas fa-save me-1"></i>Save Product',
        processing: '<span class="spinner-border spinner-border-sm me-1"></span>Processing...'
    };
    
    const routes = {
        list: "{{ route('admin.api.products.index') }}",
        store: "{{ route('admin.api.products.store') }}",
        show: (id) => "{{ route('admin.api.products.show', ['id' => '__ID__']) }}".replace('__ID__', id),
        update: (id) => "{{ route('admin.api.products.update', ['id' => '__ID__']) }}".replace('__ID__', id),
        delete: (id) => "{{ route('admin.api.products.delete', ['id' => '__ID__']) }}".replace('__ID__', id),
        formData: "{{ route('admin.api.products.form-data') }}",
        attributes: "{{ route('admin.api.attributes.index') }}",
        variantStore: "{{ route('admin.api.variants.store') }}",
        variantDelete: (id) => "{{ route('admin.api.variants.delete', ['id' => '__ID__']) }}".replace('__ID__', id)
    };
    
    // Initialize
    loadFormData();
    loadProducts();
    loadAttributes();
    
    // Event Handlers
    $('#searchInput').on('keyup', debounce(loadProducts, 300));
    $('#filterCompany, #filterCategory, #filterStatus').on('change', function() {
    loadProducts(1);
});
    $('#productForm').on('submit', handleProductSubmit);
    $('#variantForm').on('submit', handleVariantSubmit);
    $('#productImage').on('change', function() { previewImage(this, '#imagePreview'); });
    $('#variantImage').on('change', function() { previewImage(this, '#variantImagePreview'); });
    
    function loadAttributes() {
        $.get(routes.attributes, function(response) {
            if (response.success || response.data) {
                attributesList = response.data || [];
            }
        });
    }
    
    function loadFormData() {
        $.get(routes.formData, function(response) {
            if (response.success) {
                const companies = response.data.companies.map(c => `<option value="${c.id}">${escapeHtml(c.name)}</option>`).join('');
                const categories = response.data.categories.map(c => `<option value="${c.id}">${escapeHtml(c.name)}</option>`).join('');
                
                $('#company_id, #filterCompany').append(companies);
                $('#category_id, #filterCategory').append(categories);
            }
        });
    }
    
    function loadProducts(page = 1) {
        currentPage = page;
        
        $.ajax({
            url: routes.list,
            type: 'GET',
            data: {
                page: page,
                search: $('#searchInput').val(),
                company_id: $('#filterCompany').val(),
                category_id: $('#filterCategory').val(),
                is_active: $('#filterStatus').val()
            },
            dataType: 'json',
            beforeSend: function() {
                $('#productsTable').html(`<tr><td colspan="7" class="text-center py-5"><div class="spinner-border text-primary"></div><p class="mt-2">Loading...</p></td></tr>`);
            },
            success: renderProducts,
            error: handleError
        });
    }
    
    function renderProducts(response) {
        const list = response.data?.data || [];
        let rows = '';
        
        if (!list.length) {
            $('#productsTable').html(`<tr><td colspan="7" class="text-center py-5"><i class="fas fa-box-open fa-3x text-muted mb-3"></i><p class="text-muted">No products found</p></td></tr>`);
            $('#pagination').empty();
            return;
        }
        
        list.forEach((item, index) => {
            const serialNumber = (response.data?.from || 1) + index;
            const variantCount = item.variants?.length || 0;
            const statusBadge = item.is_active ? '<span class="badge bg-success">Active</span>' : '<span class="badge bg-secondary">Inactive</span>';
            
            let badges = '';
            if (item.is_featured) badges += '<span class="badge bg-warning me-1">Featured</span>';
            if (item.is_trending) badges += '<span class="badge bg-info me-1">Trending</span>';
            if (item.is_best_seller) badges += '<span class="badge bg-success me-1">Best Seller</span>';
            if (item.is_new) badges += '<span class="badge bg-primary me-1">New</span>';
            
            rows += `
                <tr>
                    <td>${serialNumber}</td>
                    <td>${item.image_url ? `<img src="${item.image_url}" width="50" height="50" class="rounded" style="object-fit:cover;">` : '<span class="text-muted">No Image</span>'}</td>
                    <td>
                        <strong>${escapeHtml(item.name)}</strong><br>
                        <small class="text-muted">${badges || '-'}</small>
                    </td>
                    <td><small>${escapeHtml(item.company?.name || '-')}</small><br><small class="text-muted">${escapeHtml(item.category?.name || '-')}</small></td>
                    <td>
                        <span class="badge bg-info">${variantCount} Variant(s)</span>
                        ${item.base_price ? `<br><small>Base: ₹${parseFloat(item.base_price).toFixed(2)}</small>` : ''}
                    </td>
                    <td>${statusBadge}</td>
                    <td>
                        <div class="btn-group">
                            <button class="btn btn-sm btn-outline-primary" onclick="editProduct(${item.id})"><i class="fas fa-edit"></i></button>
                            <button class="btn btn-sm btn-outline-success" onclick="openVariantModal(${item.id})"><i class="fas fa-list"></i></button>
                            <button class="btn btn-sm btn-outline-danger" onclick="deleteProduct(${item.id})"><i class="fas fa-trash"></i></button>
                        </div>
                    </td>
                </tr>
            `;
        });
        
        $('#productsTable').html(rows);
        
        if (response.data && response.data.last_page > 1) {
            renderPagination(response.data);
        } else {
            $('#pagination').empty();
        }
    }
    
    function renderPagination(data) {
        let html = '<nav><ul class="pagination pagination-sm mb-0">';
        
        html += `<li class="page-item ${data.current_page === 1 ? 'disabled' : ''}"><a class="page-link" href="javascript:void(0)" onclick="loadProducts(${data.current_page - 1})"><i class="fas fa-chevron-left"></i></a></li>`;
        
        for (let i = 1; i <= data.last_page; i++) {
            if (i === 1 || i === data.last_page || (i >= data.current_page - 2 && i <= data.current_page + 2)) {
                html += `<li class="page-item ${i === data.current_page ? 'active' : ''}"><a class="page-link" href="javascript:void(0)" onclick="loadProducts(${i})">${i}</a></li>`;
            } else if (i === data.current_page - 3 || i === data.current_page + 3) {
                html += '<li class="page-item disabled"><span class="page-link">...</span></li>';
            }
        }
        
        html += `<li class="page-item ${data.current_page === data.last_page ? 'disabled' : ''}"><a class="page-link" href="javascript:void(0)" onclick="loadProducts(${data.current_page + 1})"><i class="fas fa-chevron-right"></i></a></li>`;
        html += '</ul></nav>';
        $('#pagination').html(html);
    }
    
    function handleProductSubmit(e) {
        e.preventDefault();
        
        const $btn = $('#productSubmitBtn');
        const formData = new FormData(this);
        const url = editId ? routes.update(editId) : routes.store;
        
        if (editId) formData.append('_method', 'PUT');
        
        $btn.prop('disabled', true).html(buttonStates.processing);
        
        $.ajax({
            url: url,
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            dataType: 'json',
            headers: {'X-CSRF-TOKEN': csrfToken},
            success: function(response) {
                if (response.success) {
                    Swal.fire({icon: 'success', title: 'Success!', text: response.message, timer: 2000});
                    $('#productModal').modal('hide');
                    loadProducts(currentPage);
                }
            },
            error: handleValidationError,
            complete: function() {
                $btn.prop('disabled', false).html(buttonStates.normal);
            }
        });
    }
    
    function buildAttributeSelects() {
        let html = '';
        
        attributesList.forEach(attr => {
            if (attr.values && attr.values.length) {
                const options = attr.values.map(v => `<option value="${v.id}">${escapeHtml(v.value)}</option>`).join('');
                html += `
                    <div class="mb-3">
                        <label class="form-label">${escapeHtml(attr.name)}</label>
                        <select name="attribute_values[]" class="form-select attribute-select" data-attribute-id="${attr.id}">
                            <option value="">Select ${escapeHtml(attr.name)}</option>
                            ${options}
                        </select>
                    </div>
                `;
            }
        });
        
        return html || '<div class="text-muted">No attributes available</div>';
    }
    
    function handleVariantSubmit(e) {
        e.preventDefault();
        
        const $btn = $('#variantSubmitBtn');
        const formData = new FormData(this);
        
        // Collect selected attribute values
        const attributeValueIds = [];
        $('.attribute-select').each(function() {
            const value = $(this).val();
            if (value) attributeValueIds.push(value);
        });
        
        if (attributeValueIds.length === 0) {
            Swal.fire({icon: 'warning', title: 'Warning', text: 'Please select at least one attribute'});
            return;
        }
        
        formData.append('attribute_value_ids', JSON.stringify(attributeValueIds));
        
        $btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span>Adding...');
        
        $.ajax({
            url: routes.variantStore,
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            dataType: 'json',
            headers: {'X-CSRF-TOKEN': csrfToken},
            success: function(response) {
                if (response.success) {
                    Swal.fire({icon: 'success', title: 'Success!', text: 'Variant added', toast: true, position: 'top-end', showConfirmButton: false, timer: 1500});
                    $('#variantForm')[0].reset();
                    $('#variantImagePreview').hide();
                    loadVariants(currentProductId);
                    loadProducts(currentPage);
                }
            },
            error: handleValidationError,
            complete: function() {
                $btn.prop('disabled', false).html('<i class="fas fa-plus me-1"></i>Add Variant');
            }
        });
    }
    
    function loadVariants(productId) {
        $.get(routes.show(productId), function(response) {
            if (response.success) {
                const variants = response.data.variants || [];
                let html = '';
                
                if (variants.length) {
                    variants.forEach(v => {
                        const attrNames = v.attribute_values?.map(av => av.value).join(' / ') || 'No attributes';
                        
                        html += `
                            <tr>
                                <td>${v.image_url ? `<img src="${v.image_url}" width="40" class="rounded">` : '-'}</td>
                                <td>
                                    <strong>${escapeHtml(v.sku)}</strong><br>
                                    <small class="text-muted">${escapeHtml(attrNames)}</small>
                                </td>
                                <td>$${parseFloat(v.price).toFixed(2)}</td>
                                <td>${v.sale_price ? '$' + parseFloat(v.sale_price).toFixed(2) : '-'}</td>
                                <td><span class="badge ${v.stock > 0 ? 'bg-success' : 'bg-danger'}">${v.stock}</span></td>
                                <td><button class="btn btn-sm btn-outline-danger" onclick="deleteVariant(${v.id})"><i class="fas fa-trash"></i></button></td>
                            </tr>
                        `;
                    });
                } else {
                    html = '<tr><td colspan="6" class="text-center py-3 text-muted">No variants added yet</td></tr>';
                }
                
                $('#variantsTable').html(html);
            }
        });
    }
    
    window.loadProducts = loadProducts;
    
    window.openProductModal = function() {
        editId = null;
        $('#productForm')[0].reset();
        $('#productModalTitle').text('Add New Product');
        $('#imagePreview').hide();
        $('#productSubmitBtn').html(buttonStates.normal);
        $('input[type="checkbox"]').prop('checked', false);
        new bootstrap.Modal(document.getElementById('productModal')).show();
    };
    
    window.editProduct = function(id) {
        $.get(routes.show(id), function(response) {
            if (response.success) {
                const product = response.data;
                editId = product.id;
                
                $('#name').val(product.name);
                $('#base_price').val(product.base_price);
                $('#sale_price').val(product.sale_price);
                $('#stock').val(product.stock);
                $('#company_id').val(product.company_id);
                $('#category_id').val(product.category_id);
                $('#description').val(product.description);
                $('#is_active').val(product.is_active ? '1' : '0');
                
                $('#is_featured').prop('checked', product.is_featured == 1);
                $('#is_trending').prop('checked', product.is_trending == 1);
                $('#is_best_seller').prop('checked', product.is_best_seller == 1);
                $('#is_new').prop('checked', product.is_new == 1);
                
                if (product.image_url) {
                    $('#imagePreview').attr('src', product.image_url).show();
                } else {
                    $('#imagePreview').hide();
                }
                
                $('#productModalTitle').text('Edit Product');
                new bootstrap.Modal(document.getElementById('productModal')).show();
            }
        });
    };
    
    window.openVariantModal = function(productId) {
        currentProductId = productId;
        $('#variant_product_id').val(productId);
        $('#variantForm')[0].reset();
        $('#variantImagePreview').hide();
        
        // Build attribute selects
        $('#attributeValuesContainer').html(buildAttributeSelects());
        
        loadVariants(productId);
        new bootstrap.Modal(document.getElementById('variantModal')).show();
    };
    
    window.deleteProduct = function(id) {
        Swal.fire({
            title: 'Delete Product?',
            text: "This will also delete all variants!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            confirmButtonText: 'Yes, delete!'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: routes.delete(id),
                    type: 'POST',
                    data: {_method: 'DELETE', _token: csrfToken},
                    beforeSend: () => Swal.fire({title: 'Deleting...', allowOutsideClick: false, didOpen: () => Swal.showLoading()}),
                    success: () => {
                        Swal.fire({icon: 'success', title: 'Deleted!', timer: 2000});
                        loadProducts(currentPage);
                    },
                    error: handleError
                });
            }
        });
    };
    
    window.deleteVariant = function(id) {
        Swal.fire({
            title: 'Delete Variant?',
            text: "This action cannot be undone!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            confirmButtonText: 'Yes, delete!'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: routes.variantDelete(id),
                    type: 'POST',
                    data: {_method: 'DELETE', _token: csrfToken},
                    success: () => {
                        Swal.fire({icon: 'success', title: 'Deleted!', toast: true, position: 'top-end', showConfirmButton: false, timer: 1500});
                        loadVariants(currentProductId);
                        loadProducts(currentPage);
                    },
                    error: handleError
                });
            }
        });
    };
    
    function debounce(func, wait) {
        let timeout;
        return function() {
            clearTimeout(timeout);
            timeout = setTimeout(() => func.apply(this, arguments), wait);
        };
    }
    
    function previewImage(input, previewId) {
        if (input.files && input.files[0]) {
            const reader = new FileReader();
            reader.onload = e => $(previewId).attr('src', e.target.result).show();
            reader.readAsDataURL(input.files[0]);
        }
    }
    
    function escapeHtml(text) {
        if (!text) return '';
        const map = {'&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#039;'};
        return String(text).replace(/[&<>"']/g, m => map[m]);
    }
    
    function handleError(xhr) {
        Swal.fire({icon: 'error', title: 'Error!', text: xhr.responseJSON?.message || 'An error occurred'});
    }
    
    function handleValidationError(xhr) {
        if (xhr.status === 422 && xhr.responseJSON?.errors) {
            const errors = Object.values(xhr.responseJSON.errors).flat().join('\n');
            Swal.fire({icon: 'error', title: 'Validation Error', text: errors});
        } else {
            handleError(xhr);
        }
    }
    
});
</script>
@endsection