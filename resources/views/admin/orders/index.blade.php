@extends('admin.layouts.master')

@section('title', 'Orders Management')

@section('content')
<div class="container-fluid py-4">
    
    <!-- Header Section -->
    <div class="card shadow-sm mb-4">
        <div class="card-body">
            <div class="row align-items-center">
                <div class="col-md-6">
                    <h4 class="mb-0">
                        <i class="fas fa-shopping-cart me-2 text-primary"></i>Orders Management
                    </h4>
                    <small class="text-muted">Manage and track customer orders</small>
                </div>
                <div class="col-md-6">
                    <div class="d-flex justify-content-end gap-2">
                        <button class="btn btn-outline-primary" onclick="loadOrders()">
                            <i class="fas fa-sync-alt me-1"></i>Refresh
                        </button>
                        <button class="btn btn-outline-success" onclick="exportOrders()">
                            <i class="fas fa-download me-1"></i>Export CSV
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="card shadow-sm bg-primary text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-white-50">Total Orders</h6>
                            <h3 class="mb-0" id="totalOrders">0</h3>
                        </div>
                        <i class="fas fa-shopping-bag fa-2x opacity-50"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card shadow-sm bg-success text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-white-50">Total Revenue</h6>
                            <h3 class="mb-0" id="totalRevenue">₹0</h3>
                        </div>
                        <i class="fas fa-rupee-sign fa-2x opacity-50"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card shadow-sm bg-warning text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-white-50">Pending Orders</h6>
                            <h3 class="mb-0" id="pendingOrders">0</h3>
                        </div>
                        <i class="fas fa-clock fa-2x opacity-50"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card shadow-sm bg-info text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-white-50">Delivered</h6>
                            <h3 class="mb-0" id="deliveredOrders">0</h3>
                        </div>
                        <i class="fas fa-check-circle fa-2x opacity-50"></i>
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
                    <label class="form-label">Search</label>
                    <div class="position-relative">
                        <i class="fas fa-search position-absolute top-50 start-0 translate-middle-y ms-3 text-muted"></i>
                        <input type="text" id="search" class="form-control ps-5" placeholder="Order number or customer...">
                    </div>
                </div>
                
                <div class="col-md-2">
                    <label class="form-label">Order Status</label>
                    <select id="order_status" class="form-select">
                        <option value="">All Status</option>
                        <option value="pending">⏳ Pending</option>
                        <option value="processing">🔄 Processing</option>
                        <option value="shipped">🚚 Shipped</option>
                        <option value="delivered">✅ Delivered</option>
                        <option value="cancelled">❌ Cancelled</option>
                    </select>
                </div>
                
                <div class="col-md-2">
                    <label class="form-label">Payment Status</label>
                    <select id="payment_status" class="form-select">
                        <option value="">All Payments</option>
                        <option value="pending">⏳ Pending</option>
                        <option value="paid">✅ Paid</option>
                        <option value="failed">❌ Failed</option>
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
                
                <div class="col-md-1">
                    <label class="form-label">&nbsp;</label>
                    <div class="d-flex gap-2">
                        <button class="btn btn-primary flex-grow-1" onclick="loadOrders()" title="Apply Filters">
                            <i class="fas fa-search"></i>
                        </button>
                        <button class="btn btn-secondary" onclick="clearFilters()" title="Clear Filters">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Orders Table -->
    <div class="card shadow-sm">
        <div class="card-header bg-light d-flex justify-content-between align-items-center">
            <h5 class="mb-0">
                <i class="fas fa-list me-2"></i>Orders List
                <span id="totalCount" class="badge bg-primary ms-2">0</span>
            </h5>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th width="5%">#</th>
                            <th width="15%">Order No.</th>
                            <th width="18%">Customer</th>
                            <th width="12%">Amount</th>
                            <th width="12%">Order Date</th>
                            <th width="13%">Order Status</th>
                            <th width="12%">Payment</th>
                            <th width="13%">Actions</th>
                        </tr>
                    </thead>
                    <tbody id="ordersTable">
                        <tr>
                            <td colspan="8" class="text-center py-5">
                                <div class="spinner-border text-primary" role="status">
                                    <span class="visually-hidden">Loading...</span>
                                </div>
                                <p class="mt-2 text-muted">Loading orders...</p>
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

<!-- Order Detail Modal -->
<div class="modal fade" id="orderModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-light">
                <h5 class="modal-title">
                    <i class="fas fa-shopping-cart me-2"></i>Order Details
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" id="orderDetail">
                <!-- Content will be loaded dynamically -->
            </div>
            <div class="modal-footer bg-light">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="fas fa-times me-1"></i>Close
                </button>
                <div id="printButtons"></div>
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
let orderModal;
let currentOrderId = null;

// Route URLs from Laravel
const routes = {
    ordersIndex: "{{ route('admin.api.orders.index') }}",
    ordersShow: "{{ route('admin.api.orders.show', ['id' => '__ID__']) }}",
    ordersStatus: "{{ route('admin.api.orders.status', ['id' => '__ID__']) }}",
    ordersPayment: "{{ route('admin.api.orders.payment', ['id' => '__ID__']) }}",
    ordersPrint: "{{ route('admin-page.orders.print', ['id' => '__ID__']) }}",
    ordersPdf: "{{ route('admin-page.orders.pdf', ['id' => '__ID__']) }}"
};

// CSRF Token
const csrfToken = "{{ csrf_token() }}";

// Initialize on document ready
$(document).ready(function() {
    // Initialize modal
    orderModal = new bootstrap.Modal(document.getElementById('orderModal'));
    
    // Load orders
    loadOrders();
    
    // Search on enter
    $('#search').on('keyup', function(e) {
        if (e.key === 'Enter') {
            loadOrders(1);
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

// Load orders with filters
window.loadOrders = function(page = 1) {
    currentPage = page;
    
    const filters = {
        search: $('#search').val(),
        order_status: $('#order_status').val(),
        payment_status: $('#payment_status').val(),
        from_date: $('#from_date').val(),
        to_date: $('#to_date').val(),
        page: page
    };
    
    // Remove empty filters
    Object.keys(filters).forEach(key => {
        if (!filters[key]) delete filters[key];
    });
    
    $.ajax({
        url: routes.ordersIndex,
        type: 'GET',
        data: filters,
        dataType: 'json',
        beforeSend: function() {
            $('#ordersTable').html(`
                <tr>
                    <td colspan="8" class="text-center py-5">
                        <div class="spinner-border text-primary" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                        <p class="mt-2 text-muted">Loading orders...</p>
                    </td>
                </tr>
            `);
        },
        success: function(response) {
            renderOrders(response);
            updateStatistics(response);
        },
        error: function(xhr) {
            console.error('Error loading orders:', xhr);
            $('#ordersTable').html(`
                <tr>
                    <td colspan="8" class="text-center py-5 text-danger">
                        <i class="fas fa-exclamation-circle fa-3x mb-3"></i>
                        <p>Error loading orders. Please try again.</p>
                        <button class="btn btn-sm btn-outline-primary mt-2" onclick="loadOrders()">
                            <i class="fas fa-sync-alt me-1"></i>Retry
                        </button>
                    </td>
                </tr>
            `);
        }
    });
};

// Update statistics
function updateStatistics(response) {
    const orders = response.data?.data || response.data || [];
    const total = response.data?.total || orders.length;
    
    $('#totalOrders').text(total);
    $('#totalCount').text(total);
    
    // Calculate statistics
    let totalRevenue = 0;
    let pendingCount = 0;
    let deliveredCount = 0;
    
    orders.forEach(order => {
        totalRevenue += parseFloat(order.final_amount) || 0;
        if (order.order_status === 'pending') pendingCount++;
        if (order.order_status === 'delivered') deliveredCount++;
    });
    
    $('#totalRevenue').text('₹' + totalRevenue.toLocaleString('en-IN'));
    $('#pendingOrders').text(pendingCount);
    $('#deliveredOrders').text(deliveredCount);
}

// Render orders table
function renderOrders(response) {
    const orders = response.data?.data || response.data || [];
    let rows = '';
    
    if (orders.length === 0) {
        $('#ordersTable').html(`
            <tr>
                <td colspan="8" class="text-center py-5">
                    <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
                    <p class="text-muted">No orders found</p>
                </td>
            </tr>
        `);
        $('#paginationInfo').text('');
        $('#paginationLinks').html('');
        return;
    }
    
    orders.forEach((order, index) => {
        const serialNumber = (response.data?.from || 1) + index;
        const orderDate = formatDate(order.created_at);
        const customerName = order.user ? 
            (order.user.first_name + ' ' + (order.user.last_name || '')).trim() || order.user.name || 'Guest' : 
            'Guest';
        
        // Generate URLs with ID
        const printUrl = routes.ordersPrint.replace('__ID__', order.id);
        const pdfUrl = routes.ordersPdf.replace('__ID__', order.id);
        
        rows += `
            <tr id="order-row-${order.id}">
                <td>${serialNumber}</td>
                <td>
                    <span class="fw-semibold">${escapeHtml(order.order_number)}</span>
                    <br>
                    <small class="text-muted">ID: ${order.id}</small>
                </td>
                <td>
                    <div class="d-flex align-items-center">
                        <div class="rounded-circle bg-light me-2 d-flex align-items-center justify-content-center" style="width: 32px; height: 32px;">
                            <i class="fas fa-user text-secondary"></i>
                        </div>
                        <div>
                            <strong>${escapeHtml(customerName)}</strong>
                            <br>
                            <small class="text-muted">${escapeHtml(order.user?.email || order.guest_email || 'N/A')}</small>
                        </div>
                    </div>
                </td>
                <td>
                    <span class="fw-bold">₹${parseFloat(order.final_amount).toLocaleString('en-IN')}</span>
                    ${order.discount_amount > 0 ? `<br><small class="text-success">Discount: ₹${parseFloat(order.discount_amount).toLocaleString('en-IN')}</small>` : ''}
                </td>
                <td>
                    <small>
                        <i class="far fa-calendar-alt me-1"></i>${orderDate}
                    </small>
                </td>
                <td>
                    <select class="form-select form-select-sm status-select" 
                            onchange="updateOrderStatus(${order.id}, this.value)"
                            style="min-width: 120px;">
                        ${statusOptions(order.order_status)}
                    </select>
                </td>
                <td>
                    <select class="form-select form-select-sm payment-select"
                            onchange="updatePaymentStatus(${order.id}, this.value)"
                            style="min-width: 100px;">
                        ${paymentOptions(order.payment_status)}
                    </select>
                </td>
                <td>
                    <div class="btn-group">
                        <button class="btn btn-sm btn-outline-primary" onclick="viewOrder(${order.id})" title="View Details">
                            <i class="fas fa-eye"></i>
                        </button>
                        <a href="${printUrl}" class="btn btn-sm btn-outline-secondary" target="_blank" title="Print">
                            <i class="fas fa-print"></i>
                        </a>
                        <a href="${pdfUrl}" class="btn btn-sm btn-outline-danger" title="Download PDF">
                            <i class="fas fa-file-pdf"></i>
                        </a>
                    </div>
                </td>
            </tr>
        `;
    });
    
    $('#ordersTable').html(rows);
    
    // Render pagination
    if (response.data && response.data.last_page > 1) {
        renderPagination(response.data);
    } else {
        $('#paginationInfo').text(`Showing ${orders.length} orders`);
        $('#paginationLinks').html('');
    }
}

// Render pagination
function renderPagination(data) {
    const start = data.from || 1;
    const end = data.to || data.data.length;
    const total = data.total || data.data.length;
    
    $('#paginationInfo').text(`Showing ${start} to ${end} of ${total} orders`);
    
    let html = '<nav><ul class="pagination pagination-sm mb-0">';
    
    // Previous
    html += `<li class="page-item ${data.current_page === 1 ? 'disabled' : ''}">
        <a class="page-link" href="javascript:void(0)" onclick="loadOrders(${data.current_page - 1})">
            <i class="fas fa-chevron-left"></i>
        </a>
    </li>`;
    
    // Pages
    for (let i = 1; i <= data.last_page; i++) {
        if (i === 1 || i === data.last_page || (i >= data.current_page - 2 && i <= data.current_page + 2)) {
            html += `<li class="page-item ${i === data.current_page ? 'active' : ''}">
                <a class="page-link" href="javascript:void(0)" onclick="loadOrders(${i})">${i}</a>
            </li>`;
        } else if (i === data.current_page - 3 || i === data.current_page + 3) {
            html += '<li class="page-item disabled"><span class="page-link">...</span></li>';
        }
    }
    
    // Next
    html += `<li class="page-item ${data.current_page === data.last_page ? 'disabled' : ''}">
        <a class="page-link" href="javascript:void(0)" onclick="loadOrders(${data.current_page + 1})">
            <i class="fas fa-chevron-right"></i>
        </a>
    </li>`;
    
    html += '</ul></nav>';
    $('#paginationLinks').html(html);
}

// Clear filters
window.clearFilters = function() {
    $('#search').val('');
    $('#order_status').val('');
    $('#payment_status').val('');
    $('#from_date').val('');
    $('#to_date').val('');
    loadOrders(1);
};

// Status options for select
function statusOptions(selected) {
    const statuses = {
        'pending': '⏳ Pending',
        'processing': '🔄 Processing',
        'shipped': '🚚 Shipped',
        'delivered': '✅ Delivered',
        'cancelled': '❌ Cancelled'
    };
    
    let options = '';
    Object.keys(statuses).forEach(status => {
        options += `<option value="${status}" ${status == selected ? 'selected' : ''}>${statuses[status]}</option>`;
    });
    return options;
}

// Payment options for select
function paymentOptions(selected) {
    const payments = {
        'pending': '⏳ Pending',
        'paid': '✅ Paid',
        'failed': '❌ Failed'
    };
    
    let options = '';
    Object.keys(payments).forEach(status => {
        options += `<option value="${status}" ${status == selected ? 'selected' : ''}>${payments[status]}</option>`;
    });
    return options;
}

// Get status badge
function getStatusBadge(status) {
    const badges = {
        'pending': '<span class="badge bg-warning">⏳ Pending</span>',
        'processing': '<span class="badge bg-info">🔄 Processing</span>',
        'shipped': '<span class="badge bg-primary">🚚 Shipped</span>',
        'delivered': '<span class="badge bg-success">✅ Delivered</span>',
        'cancelled': '<span class="badge bg-danger">❌ Cancelled</span>'
    };
    return badges[status] || `<span class="badge bg-secondary">${status}</span>`;
}

// Get payment badge
function getPaymentBadge(status) {
    const badges = {
        'pending': '<span class="badge bg-warning">⏳ Pending</span>',
        'paid': '<span class="badge bg-success">✅ Paid</span>',
        'failed': '<span class="badge bg-danger">❌ Failed</span>'
    };
    return badges[status] || `<span class="badge bg-secondary">${status}</span>`;
}

// View order details
window.viewOrder = function(id) {
    currentOrderId = id;
    
    const showUrl = routes.ordersShow.replace('__ID__', id);
    const printUrl = routes.ordersPrint.replace('__ID__', id);
    const pdfUrl = routes.ordersPdf.replace('__ID__', id);
    
    $.ajax({
        url: showUrl,
        type: 'GET',
        dataType: 'json',
        beforeSend: function() {
            $('#orderDetail').html(`
                <div class="text-center py-4">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                    <p class="mt-2">Loading order details...</p>
                </div>
            `);
        },
        success: function(response) {
            const order = response.data || response;
            renderOrderDetail(order);
            
            // Update print buttons with route URLs
            $('#printButtons').html(`
                <a href="${printUrl}" class="btn btn-outline-secondary" target="_blank">
                    <i class="fas fa-print me-1"></i>Print
                </a>
                <a href="${pdfUrl}" class="btn btn-outline-danger">
                    <i class="fas fa-file-pdf me-1"></i>Download PDF
                </a>
            `);
            
            orderModal.show();
        },
        error: function(xhr) {
            $('#orderDetail').html(`
                <div class="text-center py-4 text-danger">
                    <i class="fas fa-exclamation-circle fa-3x mb-3"></i>
                    <p>Error loading order details</p>
                </div>
            `);
        }
    });
};

// Render order detail
function renderOrderDetail(order) {
    const orderDate = formatDateTime(order.created_at);
    const customerName = order.user ? 
        (order.user.first_name + ' ' + (order.user.last_name || '')).trim() || order.user.name : 
        'Guest';
    
    let html = `
        <div class="row mb-3">
            <div class="col-md-6">
                <h6 class="text-muted mb-2">Order Information</h6>
                <p class="mb-1"><strong>Order Number:</strong> ${escapeHtml(order.order_number)}</p>
                <p class="mb-1"><strong>Order Date:</strong> ${orderDate}</p>
                <p class="mb-1"><strong>Order Status:</strong> ${getStatusBadge(order.order_status)}</p>
                <p class="mb-1"><strong>Payment Status:</strong> ${getPaymentBadge(order.payment_status)}</p>
                <p class="mb-1"><strong>Payment Method:</strong> ${escapeHtml(order.payment_method || 'N/A')}</p>
            </div>
            <div class="col-md-6">
                <h6 class="text-muted mb-2">Customer Information</h6>
                <p class="mb-1"><strong>Name:</strong> ${escapeHtml(customerName)}</p>
                <p class="mb-1"><strong>Email:</strong> ${escapeHtml(order.user?.email || order.guest_email || 'N/A')}</p>
                <p class="mb-1"><strong>Phone:</strong> ${escapeHtml(order.user?.phone || order.shipping_phone || 'N/A')}</p>
            </div>
        </div>
        
        ${order.shipping_address ? `
        <div class="row mb-3">
            <div class="col-12">
                <h6 class="text-muted mb-2">Shipping Address</h6>
                <p class="mb-1">${escapeHtml(order.shipping_address)}</p>
                <p class="mb-1">${escapeHtml(order.shipping_city || '')} ${order.shipping_state || ''} - ${order.shipping_pincode || ''}</p>
            </div>
        </div>
        ` : ''}
        
        <h6 class="text-muted mb-2">Order Items</h6>
        <div class="table-responsive">
            <table class="table table-bordered">
                <thead class="table-light">
                    <tr>
                        <th>Product</th>
                        <th width="100">Quantity</th>
                        <th width="120">Price</th>
                        <th width="120">Total</th>
                    </tr>
                </thead>
                <tbody>
    `;
    
    let subtotal = 0;
    if (order.items && order.items.length > 0) {
        order.items.forEach(item => {
            const total = parseFloat(item.price) * parseInt(item.quantity);
            subtotal += total;
            
            html += `
                <tr>
                    <td>
                        <strong>${escapeHtml(item.product_name)}</strong>
                        ${item.variant_name ? `<br><small class="text-muted">${escapeHtml(item.variant_name)}</small>` : ''}
                    </td>
                    <td class="text-center">${item.quantity}</td>
                    <td class="text-end">₹${parseFloat(item.price).toLocaleString('en-IN')}</td>
                    <td class="text-end">₹${total.toLocaleString('en-IN')}</td>
                </tr>
            `;
        });
    } else {
        html += `<tr><td colspan="4" class="text-center text-muted">No items found</td></tr>`;
    }
    
    html += `
                </tbody>
                <tfoot class="table-light">
                    <tr>
                        <td colspan="3" class="text-end"><strong>Subtotal:</strong></td>
                        <td class="text-end">₹${subtotal.toLocaleString('en-IN')}</td>
                    </tr>
    `;
    
    if (order.discount_amount > 0) {
        html += `
                    <tr>
                        <td colspan="3" class="text-end text-success"><strong>Discount:</strong></td>
                        <td class="text-end text-success">-₹${parseFloat(order.discount_amount).toLocaleString('en-IN')}</td>
                    </tr>
        `;
    }
    
    if (order.shipping_charge > 0) {
        html += `
                    <tr>
                        <td colspan="3" class="text-end"><strong>Shipping:</strong></td>
                        <td class="text-end">₹${parseFloat(order.shipping_charge).toLocaleString('en-IN')}</td>
                    </tr>
        `;
    }
    
    html += `
                    <tr>
                        <td colspan="3" class="text-end"><strong>Total:</strong></td>
                        <td class="text-end"><strong>₹${parseFloat(order.final_amount).toLocaleString('en-IN')}</strong></td>
                    </tr>
                </tfoot>
            </table>
        </div>
    `;
    
    if (order.notes) {
        html += `
        <div class="mt-3">
            <h6 class="text-muted mb-2">Order Notes</h6>
            <p class="mb-0">${escapeHtml(order.notes)}</p>
        </div>
        `;
    }
    
    $('#orderDetail').html(html);
}

// Update order status
window.updateOrderStatus = function(id, status) {
    const select = $(`#order-row-${id} .status-select`);
    const originalValue = select.val();
    const statusUrl = routes.ordersStatus.replace('__ID__', id);
    
    // Show loading
    select.prop('disabled', true);
    
    $.ajax({
        url: statusUrl,
        type: 'POST',
        data: {
            order_status: status,
            _token: csrfToken
        },
        complete: function(xhr) {
            select.prop('disabled', false);
            
            if (xhr.status >= 200 && xhr.status < 300) {
                showToast('success', 'Status updated successfully');
                loadOrders(currentPage);
            } else {
                // Revert on error
                select.val(originalValue);
                showToast('error', 'Failed to update status');
            }
        }
    });
};

// Update payment status
window.updatePaymentStatus = function(id, status) {
    const select = $(`#order-row-${id} .payment-select`);
    const originalValue = select.val();
    const paymentUrl = routes.ordersPayment.replace('__ID__', id);
    
    // Show loading
    select.prop('disabled', true);
    
    $.ajax({
        url: paymentUrl,
        type: 'POST',
        data: {
            payment_status: status,
            _token: csrfToken
        },
        complete: function(xhr) {
            select.prop('disabled', false);
            
            if (xhr.status >= 200 && xhr.status < 300) {
                showToast('success', 'Payment status updated');
            } else {
                // Revert on error
                select.val(originalValue);
                showToast('error', 'Failed to update payment status');
            }
        }
    });
};

// Export orders
window.exportOrders = function() {
    const filters = {
        search: $('#search').val(),
        order_status: $('#order_status').val(),
        payment_status: $('#payment_status').val(),
        from_date: $('#from_date').val(),
        to_date: $('#to_date').val(),
        export: 'csv'
    };
    
    // Build query string
    const params = new URLSearchParams();
    Object.keys(filters).forEach(key => {
        if (filters[key]) params.append(key, filters[key]);
    });
    
    window.location.href = routes.ordersIndex + "?" + params.toString();
};

// Helper Functions
function formatDate(dateString) {
    if (!dateString) return 'N/A';
    const date = new Date(dateString);
    return date.toLocaleDateString('en-IN', { 
        day: '2-digit', 
        month: 'short', 
        year: 'numeric' 
    });
}

function formatDateTime(dateString) {
    if (!dateString) return 'N/A';
    const date = new Date(dateString);
    return date.toLocaleString('en-IN', { 
        day: '2-digit', 
        month: 'short', 
        year: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
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

function showToast(icon, title, timer = 1500) {
    Swal.fire({
        icon: icon,
        title: title,
        toast: true,
        position: 'top-end',
        showConfirmButton: false,
        timer: timer
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
    
    .status-select, .payment-select {
        min-width: 100px !important;
        font-size: 0.75rem;
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