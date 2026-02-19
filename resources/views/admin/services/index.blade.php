@extends('admin.layouts.master')
@section('title')
    Services Management
@endsection


<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<link href="{{ URL::asset('build/libs/datatables.net-bs4/css/dataTables.bootstrap4.min.css') }}" rel="stylesheet">
<style>
    .swal2-toast {
        font-size: 12px !important;
        padding: 6px 10px !important;
        min-width: auto !important;
        width: 220px !important;
        line-height: 1.3em !important;
    }

    .swal2-toast .swal2-icon {
        width: 24px !important;
        height: 24px !important;
        margin-right: 6px !important;
    }

    .swal2-toast .swal2-title {
        font-size: 13px !important;
    }

    td {
        font-size: small !important;
    }

    .service-icon {
        width: 50px;
        height: 50px;
        border-radius: 10px;
        object-fit: contain;
        border: 2px solid #e9ecef;
    }

    .service-icon-svg {
        width: 50px;
        height: 50px;
        padding: 8px;
        background: #f8f9fa;
        border-radius: 10px;
        border: 2px solid #e9ecef;
    }

    .status-badge {
        font-size: 0.75rem;
        padding: 0.25rem 0.5rem;
    }

    .icon-preview {
        max-width: 80px;
        max-height: 80px;
        margin-top: 10px;
        border-radius: 8px;
        padding: 5px;
        background: #f8f9fa;
        border: 1px solid #dee2e6;
    }

    .providers-table th {
        font-size: 0.8rem;
        font-weight: 600;
    }

    .providers-table td {
        font-size: 0.8rem;
        vertical-align: middle;
    }
</style>


@section('content')
    <div class="container-fluid py-4">
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h3 class="page-title mb-0" style="color:black!important;">Services Management</h3>
                        <div class="d-flex gap-2">
                            <button type="button" class="btn btn-primary" data-bs-toggle="modal"
                                data-bs-target="#serviceModal">
                                <i class="fas fa-plus me-2"></i> Add New Service
                            </button>

                            <button type="button" class="btn btn-success" data-bs-toggle="modal"
                                data-bs-target="#uploadProvidersModal">
                                <i class="fas fa-upload me-2"></i> Upload Providers
                            </button>
                            <a href="{{ route('admin.services.download-template') }}" class="btn btn-primary">Download
                                sample
                                template
                            </a>
                        </div>
                    </div>

                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover datatable">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>Icon</th>
                                        <th>Service Name</th>
                                        <th>Description</th>
                                        <th>Providers</th>
                                        <th>Location</th>
                                        <th>Created Date</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($services as $service)
                                        <tr>
                                            <td>{{ $loop->iteration }}</td>
                                            <td>
                                                @if ($service->icon)
                                                    @if (pathinfo($service->icon, PATHINFO_EXTENSION) === 'svg')
                                                        <div class="service-icon-svg">
                                                            <img src="{{ asset($service->icon) }}"
                                                                alt="{{ $service->name }}"
                                                                style="width: 55px; height: 55px;">
                                                        </div>
                                                    @else
                                                        <img src="{{ asset($service->icon) }}" alt="{{ $service->name }}"
                                                            class="service-icon" style="width: 45px; height: 55px;">
                                                    @endif
                                                @else
                                                    <div
                                                        class="service-icon-svg bg-light d-flex align-items-center justify-content-center">
                                                        <i class="fas fa-concierge-bell text-muted"></i>
                                                    </div>
                                                @endif
                                            </td>
                                            <td><strong>{{ $service->name }}</strong></td>
                                            <td>
                                                @if ($service->description)
                                                    <span title="{{ $service->description }}">
                                                        {{ Str::limit($service->description, 50) }}
                                                    </span>
                                                @else
                                                    <span class="text-muted">No description</span>
                                                @endif
                                            </td>
                                            <td>
                                                <span class="badge bg-info">{{ $service->providers_count }}</span>
                                            </td>
                                            <td>{{ $service->location ?? 'N/A' }}</td>
                                            <td>{{ $service->created_at->format('M d, Y') }}</td>
                                            <td>
                                                <div class="d-flex gap-2 align-items-center">
                                                    <button type="button" class="btn btn-sm btn-warning edit-btn px-2"
                                                        data-bs-toggle="modal" data-bs-target="#serviceModal"
                                                        data-id="{{ $service->id }}" data-name="{{ $service->name }}"
                                                        data-description="{{ $service->description }}"
                                                        data-providers_count="{{ $service->providers_count }}"
                                                        data-location="{{ $service->location }}"
                                                        data-iconnn="{{ $service->icon }}"
                                                        title="Edit {{ $service->name }}">
                                                        <i class="fas fa-edit"></i>
                                                    </button>

                                                    <button type="button"
                                                        class="btn btn-sm btn-info view-providers-btn px-2"
                                                        data-service-id="{{ $service->id }}"
                                                        data-service-name="{{ $service->name }}"
                                                        title="View Providers for {{ $service->name }}">
                                                        <i class="fas fa-users"></i>
                                                    </button>

                                                    <form action="{{ route('admin.services.destroy', $service->id) }}"
                                                        method="POST" class="delete-form m-0">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="btn btn-sm btn-danger delete-btn px-2"
                                                            title="Delete {{ $service->name }}">
                                                            <i class="fas fa-trash"></i>
                                                        </button>
                                                    </form>
                                                </div>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="8" class="text-center py-4 text-muted">
                                                <i class="fas fa-concierge-bell fa-2x mb-3"></i>
                                                <p>No services found. Add your first service!</p>
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Service Modal -->
    <div class="modal fade" id="serviceModal" tabindex="-1" aria-labelledby="serviceModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="serviceModalLabel" style="color:black!important;">Add New Service</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="serviceForm" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div id="formMethod"></div>
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-4">
                                    <label for="name" class="form-label">Service Name <span
                                            class="text-danger">*</span></label>
                                    <input type="text" class="form-control" id="name" name="name" required>
                                </div>
                                <div class="mb-3">
                                    <label for="providers_count" class="form-label">No. of Providers</label>
                                    <input type="number" class="form-control" id="providers_count"
                                        name="providers_count" min="0">
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="location" class="form-label">Location</label>
                                    <input type="text" class="form-control" id="location" name="location"
                                        placeholder="e.g., New York, London">
                                </div>

                                <div class="mb-3">
                                    <label for="icon" class="form-label">Service Icon</label>
                                    <input type="file" class="form-control" id="icon" name="icon"
                                        accept=".svg,.png,.jpg,.jpeg">
                                    <small class="text-muted">Accepted formats: SVG, PNG, JPG (Max: 2MB)</small>
                                    <div id="iconPreview" class="mt-2" style="display: none;">
                                        <p class="small text-muted mb-1">Preview:</p>
                                        <img id="iconPreviewImg" class="icon-preview" src="" alt="Icon preview">
                                    </div>
                                    <div id="currentIcon" class="mt-2" style="display: none;">
                                        <p class="small text-muted mb-1">Current Icon:</p>
                                        <img id="currentIconImg" class="icon-preview" src="" alt="Current icon">
                                    </div>
                                </div>
                            </div>

                            <div class="col-12">
                                <div class="mb-3">
                                    <label for="description" class="form-label">Description</label>
                                    <textarea class="form-control" id="description" name="description" rows="3"
                                        placeholder="Describe the service..."></textarea>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-primary" id="submitBtn">Add Service</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Upload Providers Modal -->
    <div class="modal fade" id="uploadProvidersModal" tabindex="-1" aria-labelledby="uploadProvidersModalLabel"
        aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="uploadProvidersModalLabel" style="color:black!important;">Upload Service
                        Providers</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="{{ route('admin.services.upload-providers') }}" method="POST"
                    enctype="multipart/form-data">
                    @csrf
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="service_id" class="form-label">Select Service <span
                                    class="text-danger">*</span></label>
                            <select class="form-control" id="service_id" name="service_id" required>
                                <option value="">Select a Service</option>
                                @foreach ($services as $service)
                                    <option value="{{ $service->id }}">{{ $service->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="mb-3">
                            <label for="providers_file" class="form-label">Upload CSV File <span
                                    class="text-danger">*</span></label>
                            <input type="file" class="form-control" id="providers_file" name="providers_file"
                                accept=".csv" required>
                            <small class="text-muted">
                                Upload CSV file with provider details.
                                <a href="#" id="downloadTemplate" class="text-primary">Download sample template</a>
                            </small>
                        </div>

                        <div class="alert alert-info">
                            <h6>CSV Format Required:</h6>
                            <ul class="mb-0 small">
                                <li><strong>Columns must be in this order:</strong> name, email, phone, address</li>
                                <li>First row should be header with exact column names</li>
                                <li>File should be UTF-8 encoded</li>
                                <li>Maximum 1000 providers per file</li>
                            </ul>
                        </div>

                        <!-- Sample Data Preview -->
                        <div class="mt-3">
                            <h6>Sample Data Format:</h6>
                            <div class="table-responsive">
                                <table class="table table-bordered table-sm">
                                    <thead>
                                        <tr>
                                            <th>name</th>
                                            <th>email</th>
                                            <th>phone</th>
                                            <th>address</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td>John Doe</td>
                                            <td>john@example.com</td>
                                            <td>+1234567890</td>
                                            <td>123 Main Street, New York</td>
                                        </tr>
                                        <tr>
                                            <td>Jane Smith</td>
                                            <td>jane@example.com</td>
                                            <td>+0987654321</td>
                                            <td>456 Park Avenue, London</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-success">Upload Providers</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Providers List Modal -->
    <div class="modal fade" id="providersListModal" tabindex="-1" aria-labelledby="providersListModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-xl">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="providersListModalLabel" style="color:black!important;">
                        Providers for: <span id="serviceNameTitle"></span>
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <div>
                            <span class="badge bg-primary" id="providersCountBadge">0 Providers</span>
                        </div>
                        <button type="button" class="btn btn-success btn-sm" id="addNewProviderBtn">
                            <i class="fas fa-plus me-1"></i> Add New Provider
                        </button>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-bordered table-hover providers-table" id="providersTable">
                            <thead class="table-light">
                                <tr>
                                    <th>#</th>
                                    <th>Name</th>
                                    <th>Email</th>
                                    <th>Phone</th>
                                    <th>Address</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody id="providersTableBody">
                                <!-- Providers will be loaded here via AJAX -->
                            </tbody>
                        </table>
                    </div>

                    <div id="noProvidersMessage" class="text-center py-4" style="display: none;">
                        <i class="fas fa-users fa-3x text-muted mb-3"></i>
                        <h5 class="text-muted">No Providers Found</h5>
                        <p class="text-muted">This service doesn't have any providers yet.</p>
                        <button type="button" class="btn btn-primary" id="uploadProvidersFromList">
                            <i class="fas fa-upload me-1"></i> Upload Providers CSV
                        </button>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Add/Edit Provider Modal -->
    <div class="modal fade" id="providerFormModal" tabindex="-1" aria-labelledby="providerFormModalLabel"
        aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="providerFormModalLabel" style="color:black!important;">Add New Provider
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="providerForm" method="POST">
                    @csrf
                    <div id="providerFormMethod"></div>
                    <input type="hidden" id="provider_service_id" name="service_id">

                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="provider_name" class="form-label">Provider Name <span
                                    class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="provider_name" name="name" required>
                        </div>

                        <div class="mb-3">
                            <label for="provider_email" class="form-label">Email</label>
                            <input type="email" class="form-control" id="provider_email" name="email">
                        </div>

                        <div class="mb-3">
                            <label for="provider_phone" class="form-label">Phone</label>
                            <input type="text" class="form-control" id="provider_phone" name="phone">
                        </div>

                        <div class="mb-3">
                            <label for="provider_address" class="form-label">Address</label>
                            <textarea class="form-control" id="provider_address" name="address" rows="3"></textarea>
                        </div>

                        <div class="mb-3">
                            <label for="provider_status" class="form-label">Status</label>
                            <select class="form-control" id="provider_status" name="is_active">
                                <option value="1">Active</option>
                                <option value="0">Inactive</option>
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary" id="providerSubmitBtn">Add Provider</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="{{ URL::asset('build/libs/datatables.net/js/jquery.dataTables.min.js') }}"></script>
    <script src="{{ URL::asset('build/libs/datatables.net-bs4/js/dataTables.bootstrap4.min.js') }}"></script>

    <script>
        @if (session('success'))
            Swal.fire({
                toast: true,
                icon: 'success',
                title: "{{ session('success') }}",
                position: 'top-end',
                showConfirmButton: false,
                timer: 3000,
                timerProgressBar: true
            });
        @endif

        $(document).ready(function() {
            // Initialize DataTable
            $('.datatable').DataTable({
                responsive: true,
                columnDefs: [{
                    orderable: false,
                    targets: [1, 7]
                }, {
                    searchable: false,
                    targets: [1, 7]
                }],
                order: [
                    [0, 'asc']
                ]
            });

            // Icon preview functionality
            $('#icon').on('change', function(e) {
                const file = e.target.files[0];
                if (file) {
                    const reader = new FileReader();
                    reader.onload = function(e) {
                        $('#iconPreviewImg').attr('src', e.target.result);
                        $('#iconPreview').show();
                    }
                    reader.readAsDataURL(file);
                } else {
                    $('#iconPreview').hide();
                }
            });

            // Service modal reset
            $('#serviceModal').on('hidden.bs.modal', function() {
                resetModal();
            });

            // Edit service button
            $('.edit-btn').on('click', function() {
                const id = $(this).data('id');
                const name = $(this).data('name');
                const description = $(this).data('description');
                const providers_count = $(this).data('providers_count');
                const location = $(this).data('location');
                const icon = $(this).data('iconnn');

                $('#serviceModalLabel').text('Edit Service');
                $('#name').val(name);
                $('#description').val(description);
                $('#providers_count').val(providers_count);
                $('#location').val(location);

                if (icon) {
                    $('#currentIconImg').attr('src', "{{ asset('') }}" + icon);
                    $('#currentIcon').show();
                } else {
                    $('#currentIcon').hide();
                }

                $('#iconPreview').hide();
                $('#formMethod').html('<input type="hidden" name="_method" value="PUT">');
                $('#serviceForm').attr('action', `/petz-info/public/admin/services/${id}`);
                $('#submitBtn').text('Update Service');
            });

            // Delete service confirmation
            $('.delete-form').on('submit', function(e) {
                e.preventDefault();
                const form = this;

                Swal.fire({
                    title: 'Are you sure?',
                    text: "You won't be able to revert this!",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#3085d6',
                    cancelButtonColor: '#d33',
                    confirmButtonText: 'Yes, delete it!'
                }).then((result) => {
                    if (result.isConfirmed) {
                        form.submit();
                    }
                });
            });

            // Reset service modal
            function resetModal() {
                $('#serviceModalLabel').text('Add New Service');
                $('#serviceForm').attr('action', "{{ route('admin.services.store') }}");
                $('#formMethod').html('');
                $('#name, #description, #providers_count, #location').val('');
                $('#icon').val('');
                $('#iconPreview').hide();
                $('#currentIcon').hide();
                $('#submitBtn').text('Add Service');
            }

            // Upload providers modal reset
            $('#uploadProvidersModal').on('hidden.bs.modal', function() {
                $('#service_id').val('');
                $('#providers_file').val('');
            });

            // Success message for provider upload
            @if (session('success') && str_contains(session('success'), 'providers'))
                Swal.fire({
                    toast: true,
                    icon: 'success',
                    title: "{{ session('success') }}",
                    position: 'top-end',
                    showConfirmButton: false,
                    timer: 4000,
                    timerProgressBar: true
                });
            @endif

            // Template download functionality
            $('#downloadTemplate').on('click', function(e) {
                e.preventDefault();

                // CSV template content
                const csvContent = "name,email,phone,address\n" +
                    "John Doe,john@example.com,+1234567890,123 Main Street, New York\n" +
                    "Jane Smith,jane@example.com,+0987654321,456 Park Avenue, London\n" +
                    "Mike Johnson,mike@example.com,+1122334455,789 Oak Road, Chicago\n" +
                    "Sarah Wilson,sarah@example.com,+5566778899,321 Pine Lane, Miami";

                // Create blob and download
                const blob = new Blob([csvContent], {
                    type: 'text/csv;charset=utf-8;'
                });
                const link = document.createElement('a');
                const url = URL.createObjectURL(blob);

                link.setAttribute('href', url);
                link.setAttribute('download', 'service-providers-template.csv');
                link.style.visibility = 'hidden';

                document.body.appendChild(link);
                link.click();
                document.body.removeChild(link);

                // Show success message
                Swal.fire({
                    toast: true,
                    icon: 'success',
                    title: 'Template downloaded successfully!',
                    position: 'top-end',
                    showConfirmButton: false,
                    timer: 3000,
                    timerProgressBar: true
                });
            });

            // ========== PROVIDERS MANAGEMENT ==========

            // View Providers Button Click
            $(document).on('click', '.view-providers-btn', function() {
                const serviceId = $(this).data('service-id');
                const serviceName = $(this).data('service-name');

                $('#serviceNameTitle').text(serviceName);
                $('#provider_service_id').val(serviceId);

                loadProviders(serviceId);
                $('#providersListModal').modal('show');
            });

            // Load Providers via AJAX
            function loadProviders(serviceId) {
                $.ajax({
                    url: `/petz-info/public/admin/services/${serviceId}/providers`,
                    type: 'GET',
                    success: function(response) {
                        const providers = response.providers;
                        const tableBody = $('#providersTableBody');
                        const noProvidersMessage = $('#noProvidersMessage');
                        const providersCountBadge = $('#providersCountBadge');

                        providersCountBadge.text(`${providers.length} Providers`);

                        if (providers.length === 0) {
                            tableBody.hide();
                            noProvidersMessage.show();
                        } else {
                            tableBody.show();
                            noProvidersMessage.hide();

                            tableBody.empty();
                            providers.forEach((provider, index) => {
                                const statusBadge = provider.is_active ?
                                    '<span class="badge bg-success">Active</span>' :
                                    '<span class="badge bg-secondary">Inactive</span>';

                                const row = `
                                    <tr>
                                        <td>${index + 1}</td>
                                        <td>${provider.name}</td>
                                        <td>${provider.email || 'N/A'}</td>
                                        <td>${provider.phone || 'N/A'}</td>
                                        <td>${provider.address ? provider.address.substring(0, 50) + (provider.address.length > 50 ? '...' : '') : 'N/A'}</td>
                                        <td>${statusBadge}</td>
                                        <td>
                                            <div class="d-flex gap-1">
                                                <button class="btn btn-sm btn-warning edit-provider-btn" 
                                                    data-provider-id="${provider.id}"
                                                    data-provider-name="${provider.name}"
                                                    data-provider-email="${provider.email}"
                                                    data-provider-phone="${provider.phone}"
                                                    data-provider-address="${provider.address}"
                                                    data-provider-status="${provider.is_active}">
                                                    <i class="fas fa-edit"></i>
                                                </button>
                                                <button class="btn btn-sm btn-danger delete-provider-btn" 
                                                    data-provider-id="${provider.id}"
                                                    data-provider-name="${provider.name}">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                `;
                                tableBody.append(row);
                            });
                        }
                    },
                    error: function(xhr) {
                        Swal.fire('Error!', 'Failed to load providers.', 'error');
                    }
                });
            }

            // Add New Provider Button
            $('#addNewProviderBtn').on('click', function() {
                $('#providerFormModalLabel').text('Add New Provider');
                $('#providerForm').attr('action', "{{ route('admin.providers.store') }}");
                $('#providerFormMethod').html('');
                $('#provider_name').val('');
                $('#provider_email').val('');
                $('#provider_phone').val('');
                $('#provider_address').val('');
                $('#provider_status').val('1');
                $('#providerSubmitBtn').text('Add Provider');
                $('#providerFormModal').modal('show');
            });

            // Edit Provider Button
            $(document).on('click', '.edit-provider-btn', function() {
                const providerId = $(this).data('provider-id');
                const providerName = $(this).data('provider-name');
                const providerEmail = $(this).data('provider-email');
                const providerPhone = $(this).data('provider-phone');
                const providerAddress = $(this).data('provider-address');
                const providerStatus = $(this).data('provider-status');

                $('#providerFormModalLabel').text(`Edit Provider: ${providerName}`);
                $('#providerForm').attr('action', `/petz-info/public/admin/providers/${providerId}`);
                $('#providerFormMethod').html('<input type="hidden" name="_method" value="PUT">');
                $('#provider_name').val(providerName);
                $('#provider_email').val(providerEmail);
                $('#provider_phone').val(providerPhone);
                $('#provider_address').val(providerAddress);
                $('#provider_status').val(providerStatus);
                $('#providerSubmitBtn').text('Update Provider');
                $('#providerFormModal').modal('show');
            });

            // Provider Form Submit
            $('#providerForm').on('submit', function(e) {
                e.preventDefault();
                const formData = $(this).serialize();
                const url = $(this).attr('action');
                const method = $(this).find('input[name="_method"]').val() || 'POST';

                $.ajax({
                    url: url,
                    type: method,
                    data: formData,
                    success: function(response) {
                        $('#providerFormModal').modal('hide');
                        Swal.fire({
                            toast: true,
                            icon: 'success',
                            title: response.message,
                            position: 'top-end',
                            showConfirmButton: false,
                            timer: 3000,
                            timerProgressBar: true
                        });

                        // Reload providers list
                        const serviceId = $('#provider_service_id').val();
                        loadProviders(serviceId);

                        // Reload main services table to update providers count
                        setTimeout(() => {
                            window.location.reload();
                        }, 1000);
                    },
                    error: function(xhr) {
                        let errorMessage = 'Something went wrong!';
                        if (xhr.responseJSON && xhr.responseJSON.errors) {
                            errorMessage = Object.values(xhr.responseJSON.errors).join('<br>');
                        }
                        Swal.fire('Error!', errorMessage, 'error');
                    }
                });
            });

            // Delete Provider
            $(document).on('click', '.delete-provider-btn', function() {
                const providerId = $(this).data('provider-id');
                const providerName = $(this).data('provider-name');

                Swal.fire({
                    title: 'Are you sure?',
                    text: `Delete provider "${providerName}"?`,
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#3085d6',
                    cancelButtonColor: '#d33',
                    confirmButtonText: 'Yes, delete it!'
                }).then((result) => {
                    if (result.isConfirmed) {
                        $.ajax({
                            url: `/petz-info/public/admin/providers/${providerId}`,
                            type: 'DELETE',
                            data: {
                                _token: '{{ csrf_token() }}'
                            },
                            success: function(response) {
                                Swal.fire({
                                    toast: true,
                                    icon: 'success',
                                    title: response.message,
                                    position: 'top-end',
                                    showConfirmButton: false,
                                    timer: 3000,
                                    timerProgressBar: true
                                });

                                // Reload providers list
                                const serviceId = $('#provider_service_id').val();
                                loadProviders(serviceId);

                                // Reload main services table to update providers count
                                setTimeout(() => {
                                    window.location.reload();
                                }, 1000);
                            },
                            error: function(xhr) {
                                let errorMessage = 'Something went wrong!';
                                if (xhr.responseJSON && xhr.responseJSON.message) {
                                    errorMessage = xhr.responseJSON.message;
                                }
                                Swal.fire('Error!', errorMessage, 'error');
                            }
                        });
                    }
                });
            });

            // Upload Providers from List
            $('#uploadProvidersFromList').on('click', function() {
                $('#providersListModal').modal('hide');
                setTimeout(() => {
                    $('#uploadProvidersModal').modal('show');
                }, 500);
            });

            // Provider form modal reset
            $('#providerFormModal').on('hidden.bs.modal', function() {
                $('#providerForm')[0].reset();
                $('#providerFormMethod').html('');
                $('#providerForm').attr('action', "{{ route('admin.providers.store') }}");
                $('#providerSubmitBtn').text('Add Provider');
            });
        });
    </script>
@endsection
