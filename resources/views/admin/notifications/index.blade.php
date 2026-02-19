@extends('admin.layouts.master')
@section('title', 'Push Notification Manager')

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.13.4/css/dataTables.bootstrap5.min.css">
<link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/buttons/2.3.6/css/buttons.bootstrap5.min.css">
<link rel="stylesheet" type="text/css"
    href="https://cdn.datatables.net/responsive/2.4.1/css/responsive.bootstrap5.min.css">
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

    .notification-card {
        border-radius: 10px;
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        transition: transform 0.3s ease;
    }

    .notification-card:hover {
        transform: translateY(-5px);
    }

    .table-container {
        background: white;
        border-radius: 10px;
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
        overflow: hidden;
    }

    .dataTables_wrapper .dataTables_filter input {
        border-radius: 20px;
        padding: 5px 15px;
        border: 1px solid #ddd;
    }

    .dataTables_wrapper .dataTables_length select {
        border-radius: 5px;
        padding: 5px;
        border: 1px solid #ddd;
    }

    .badge-info {
        background-color: #17a2b8;
    }

    .badge-warning {
        background-color: #ffc107;
        color: #212529;
    }

    .badge-success {
        background-color: #28a745;
    }

    .badge-primary {
        background-color: #007bff;
    }

    .badge-secondary {
        background-color: #6c757d;
    }

    .btn-purple {
        background-color: #6f42c1;
        color: white;
    }

    .btn-purple:hover {
        background-color: #5a2d9c;
        color: white;
    }

    .action-buttons .btn {
        margin: 2px;
        border-radius: 5px;
    }

    .img-thumbnail {
        border-radius: 8px;
        border: 1px solid #dee2e6;
    }

    .form-check-input:checked {
        background-color: #28a745;
        border-color: #28a745;
    }

    .dt-buttons .btn {
        border-radius: 5px;
        margin-right: 5px;
    }

    .page-item.active .page-link {
        background-color: #6f42c1;
        border-color: #6f42c1;
    }

    .page-link {
        color: #6f42c1;
    }

    .page-link:hover {
        color: #5a2d9c;
    }

    .dataTables_info {
        color: #6c757d;
        padding-top: 15px;
    }

    .dropdown-menu {
        min-width: 150px;
    }

    .status-badge {
        font-size: 0.8rem;
        padding: 0.35em 0.65em;
    }
</style>


@section('content')
    <div class="container-fluid">
        <div class="page-inner">
            <div class="m-4 d-sm-flex align-items-center justify-content-between">
                <h3 class="mb-sm-0">Push Notification Manager</h3>
                <div class="text-sm-end">
                    <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#bulkUploadModal">
                        <i class="fas fa-file-import me-2"></i> Bulk Upload
                    </button>
                    <a href="{{ route('admin.notifications.create') }}" class="btn btn-primary">
                        <i class="fas fa-plus me-2"></i> Add Notification
                    </a>
                </div>
            </div>

            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-body">
                            <!-- Table Container -->
                            <div class="table-container">
                                <table id="notificationsTable" class="table table-striped table-hover align-middle w-100">
                                    <thead class="table-gray">
                                        <tr>
                                            <th width="5%">ID</th>
                                            <th width="15%">Title</th>
                                            <th width="10%">Type</th>
                                            <th width="20%">Message</th>
                                            <th width="10%">Image</th>
                                            <th width="15%">Audience</th>
                                            <th width="10%">Date</th>
                                            <th width="10%">Status</th>
                                            <th width="10%">Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php $count = 1; ?>
                                        @foreach ($notifications as $notification)
                                            <tr>
                                                <td>{{ $count++ }}</td>
                                                <td>{{ Str::limit($notification->title, 20) }}</td>
                                                <td>
                                                    <span
                                                        class="badge bg-{{ $notification->type === 'alert' ? 'warning' : ($notification->type === 'promo' ? 'success' : 'info') }}">
                                                        {{ ucfirst($notification->type) }}
                                                    </span>
                                                </td>
                                                <td>{{ Str::limit($notification->message, 30) }}</td>
                                                <td>
                                                    @if ($notification->image && file_exists(public_path('storage/' . $notification->image)))
                                                        <img src="{{ asset('storage/' . $notification->image) }}"
                                                            class="img-thumbnail" width="60" height="60"
                                                            alt="Notification">
                                                    @else
                                                        <img src="{{ asset('images/45.jpeg') }}" class="img-thumbnail"
                                                            alt="No Image">
                                                    @endif
                                                </td>
                                                <td>
                                                    @if ($notification->audience)
                                                        @if (isset($notification->audience['locations']))
                                                            @foreach (array_slice($notification->audience['locations'], 0, 2) as $location)
                                                                <span
                                                                    class="badge bg-primary mb-1">{{ $location }}</span>
                                                            @endforeach
                                                            @if (count($notification->audience['locations']) > 2)
                                                                <span class="badge bg-secondary">
                                                                    +{{ count($notification->audience['locations']) - 2 }}
                                                                    more
                                                                </span>
                                                            @endif
                                                        @else
                                                            <span class="badge bg-info">Custom</span>
                                                        @endif
                                                    @else
                                                        <span class="badge bg-info">All Users</span>
                                                    @endif
                                                </td>
                                                <td>{{ $notification->created_at->format('Y-m-d') }}</td>
                                                <td>
                                                    @if ($notification->scheduled_at && $notification->scheduled_at > now())
                                                        <span class="badge status-badge bg-secondary">Scheduled</span>
                                                    @else
                                                        <span
                                                            class="badge status-badge bg-{{ $notification->status ? 'success' : 'danger' }}">
                                                            {{ $notification->status ? 'Active' : 'Inactive' }}
                                                        </span>
                                                    @endif
                                                </td>
                                                <td class="action-buttons">
                                                    <div class="dropdown">
                                                        <button class="btn btn-sm btn-secondary dropdown-toggle"
                                                            type="button" data-bs-toggle="dropdown">
                                                            <i class="fas fa-ellipsis-v"></i>
                                                        </button>
                                                        <ul class="dropdown-menu">
                                                            <li>
                                                                <a class="dropdown-item"
                                                                    href="{{ route('admin.notifications.show', $notification) }}">
                                                                    View
                                                                </a>
                                                            </li>
                                                            <li>
                                                                <a class="dropdown-item"
                                                                    href="{{ route('admin.notifications.edit', $notification) }}">
                                                                    Edit
                                                                </a>
                                                            </li>
                                                            <li>
                                                                <a class="dropdown-item schedule-btn" href="#"
                                                                    data-notification-id="{{ $notification->id }}">
                                                                    Schedule
                                                                </a>
                                                            </li>
                                                            <li>
                                                                <hr class="dropdown-divider">
                                                            </li>
                                                            <li>
                                                                <a class="dropdown-item text-danger delete-btn"
                                                                    href="#"
                                                                    data-notification-id="{{ $notification->id }}">
                                                                    Delete
                                                                </a>
                                                            </li>
                                                        </ul>
                                                    </div>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Delete Confirmation Modal -->
    <div class="modal fade" id="deleteModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header text-white" style="color: #5a2d9c;">
                    <h5 class="modal-title"><i class="fas fa-trash-alt me-2"></i>Delete Notification</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p>Are you sure you want to delete this notification? This action cannot be undone.</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <form id="deleteForm" method="POST">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger">Delete</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Schedule Notification Modal -->
    <div class="modal fade" id="scheduleModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header text-white" style="background-color: #6f42c1;">
                    <h5 class="modal-title text-white"><i class="fas fa-clock me-2"></i>Schedule Notification</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <form id="scheduleForm" method="POST">
                    @csrf
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Schedule Date & Time</label>
                            <input type="datetime-local" class="form-control" id="scheduleDateTime" name="scheduled_at"
                                required>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Schedule</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Bulk Upload Modal -->
    <div class="modal fade" id="bulkUploadModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header text-white" style="background-color: rgb(131, 55, 178); color:white;">
                    <h5 class="modal-title text-white">Bulk Notification Upload</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <form action="{{ route('admin.notifications.bulk-upload') }}" method="POST"
                    enctype="multipart/form-data">
                    @csrf
                    <div class="modal-body">
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle me-2"></i> Upload a CSV file containing notification
                            details.
                            <a href="{{ asset('sample-notifications.csv') }}" class="alert-link">Download sample
                                CSV
                                template</a>.
                        </div>
                        <div class="mb-3">
                            <label class="form-label"><i class="fas fa-file-csv me-2 text-success"></i>CSV
                                File</label>
                            <input type="file" class="form-control" name="csv_file" accept=".csv" required>
                            <small class="text-muted">Max file size: 5MB. Columns: Title, Message, Type,
                                ImageURL</small>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-upload me-2"></i>Upload & Process
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <!-- DataTables & Related Scripts -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.4/js/dataTables.bootstrap5.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.3.6/js/dataTables.buttons.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.3.6/js/buttons.bootstrap5.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.3.6/js/buttons.html5.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.3.6/js/buttons.print.min.js"></script>
    <script src="https://cdn.datatables.net/responsive/2.4.1/js/dataTables.responsive.min.js"></script>
    <script src="https://cdn.datatables.net/responsive/2.4.1/js/responsive.bootstrap5.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script>
        $(document).ready(function() {
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

            @if (session('error'))
                Swal.fire({
                    toast: true,
                    icon: 'error',
                    title: "{{ session('error') }}",
                    position: 'top-end',
                    showConfirmButton: false,
                    timer: 3000,
                    timerProgressBar: true
                });
            @endif

            // Initialize DataTable with simple configuration
            var table = $('#notificationsTable').DataTable({
                responsive: true,
                pageLength: 5,
                dom: '<"row"<"col-sm-12 col-md-6"B><"col-sm-12 col-md-6"f>>' +
                    '<"row"<"col-sm-12"tr>>' +
                    '<"row"<"col-sm-12 col-md-5"i><"col-sm-12 col-md-7"p>>',
                buttons: [{
                        extend: 'copy',
                        className: 'btn btn-sm btn-secondary',
                        text: '<i class="fas fa-copy me-1"></i> Copy'
                    },
                    {
                        extend: 'csv',
                        className: 'btn btn-sm btn-info',
                        text: '<i class="fas fa-file-csv me-1"></i> CSV'
                    },
                    {
                        extend: 'excel',
                        className: 'btn btn-sm btn-success',
                        text: '<i class="fas fa-file-excel me-1"></i> Excel'
                    },
                    {
                        extend: 'pdf',
                        className: 'btn btn-sm btn-danger',
                        text: '<i class="fas fa-file-pdf me-1"></i> PDF'
                    },
                    {
                        extend: 'print',
                        className: 'btn btn-sm btn-warning',
                        text: '<i class="fas fa-print me-1"></i> Print'
                    }
                ],
                language: {
                    search: "_INPUT_",
                    searchPlaceholder: "Search notifications...",
                    lengthMenu: "Show _MENU_ entries",
                    info: "Showing _START_ to _END_ of _TOTAL_ notifications",
                    infoEmpty: "Showing 0 to 0 of 0 notifications",
                    infoFiltered: "(filtered from _MAX_ total notifications)"
                },
                order: [
                    [0, 'desc']
                ] // Sort by ID descending by default
            });

            // Delete button handler
            $('#notificationsTable').on('click', '.delete-btn', function(e) {
                e.preventDefault();
                const notificationId = $(this).data('notification-id');
                const form = $('#deleteForm');
                form.attr('action', `/petz-info/public/admin/notifications/${notificationId}`);
                $('#deleteModal').modal('show');
            });

            // Schedule button handler
            $('#notificationsTable').on('click', '.schedule-btn', function(e) {
                e.preventDefault();
                const notificationId = $(this).data('notification-id');
                const form = $('#scheduleForm');
                form.attr('action', '/petz-info/public/admin/notifications/' + notificationId + '/schedule');

                // Set minimum datetime to current time
                const now = new Date();
                const year = now.getFullYear();
                const month = String(now.getMonth() + 1).padStart(2, '0');
                const day = String(now.getDate()).padStart(2, '0');
                const hours = String(now.getHours()).padStart(2, '0');
                const minutes = String(now.getMinutes()).padStart(2, '0');

                const minDateTime = `${year}-${month}-${day}T${hours}:${minutes}`;
                $('#scheduleDateTime').attr('min', minDateTime);

                $('#scheduleModal').modal('show');
            });

            // Status toggle handler
            $('#notificationsTable').on('click', '.status-toggle', function() {
                const notificationId = $(this).data('notification-id');
                const isChecked = $(this).is(':checked');
                const row = $(this).closest('tr');
                const badge = row.find('.status-badge');

                $.ajax({
                    url: `/petz-info/public/admin/notifications/${notificationId}/toggle-status`,
                    method: 'POST',
                    data: {
                        _token: '{{ csrf_token() }}',
                        _method: 'PATCH'
                    },
                    success: function(response) {
                        if (response.success) {
                            if (response.status) {
                                badge.removeClass('bg-danger').addClass('bg-success').text(
                                    'Active');
                            } else {
                                badge.removeClass('bg-success').addClass('bg-danger').text(
                                    'Inactive');
                            }
                            showToast('Status updated successfully!', 'success');
                        }
                    },
                    error: function() {
                        // Revert checkbox state
                        $(`.status-toggle[data-notification-id="${notificationId}"]`).prop(
                            'checked', !isChecked);
                        showToast('Error updating status!', 'error');
                    }
                });
            });

            // Toast notification function
            function showToast(message, type = 'info') {
                const Toast = Swal.mixin({
                    toast: true,
                    position: 'top-end',
                    showConfirmButton: false,
                    timer: 3000,
                    timerProgressBar: true,
                    didOpen: (toast) => {
                        toast.addEventListener('mouseenter', Swal.stopTimer)
                        toast.addEventListener('mouseleave', Swal.resumeTimer)
                    }
                });

                Toast.fire({
                    icon: type,
                    title: message
                });
            }
        });
    </script>
@endsection
