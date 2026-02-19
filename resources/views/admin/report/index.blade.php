@extends('admin.layouts.master')
@section('title')
    Report Management
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

    .status-badge {
        font-size: 0.75rem;
        padding: 0.25rem 0.5rem;
    }

    .badge-pending {
        background-color: #ffc107;
        color: #212529;
    }

    .badge-resolved {
        background-color: #28a745;
        color: white;
    }

    .badge-rejected {
        background-color: #dc3545;
        color: white;
    }

    .action-btns {
        white-space: nowrap;
    }

    .no-data-message {
        margin-top: 20px;
    }

    .preview-modal .modal-content {
        border-radius: 10px;
    }

    .preview-header {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        border-radius: 10px 10px 0 0;
    }

    .preview-body {
        padding: 20px;
        background-color: #f8f9fa;
    }

    .content-preview {
        background: white;
        border: 1px solid #e9ecef;
        border-radius: 8px;
        padding: 15px;
        margin-bottom: 15px;
    }

    .content-meta {
        font-size: 0.875rem;
        color: #6c757d;
        border-top: 1px solid #e9ecef;
        padding-top: 10px;
        margin-top: 10px;
    }

    .action-buttons {
        display: flex;
        gap: 10px;
        justify-content: center;
        margin-top: 15px;
    }
</style>

@section('content')
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="m-4 d-sm-flex align-items-center justify-content-between">
                    <h4 class="mb-sm-0">Report Management</h4>
                    <div class="text-sm-end">
                        <a href="{{ route('admin.reports.export') }}" class="btn btn-success">
                            <i class="fas fa-download"></i> Export Logs
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        <h4 class="card-title">All Reports</h4>

                        @if ($reports->count() > 0)
                            <div class="table-responsive">
                                <table class="table table-bordered table-striped datatable">
                                    <thead>
                                        <tr>
                                            <th>ID</th>
                                            <th>Reported By</th>
                                            <th>Type</th>
                                            <th>Reported Content</th>
                                            <th>Reason</th>
                                            <th>Status</th>
                                            <th>Date</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($reports as $report)
                                            <tr>
                                                <td>{{ $report->id }}</td>
                                                <td>
                                                    @if ($report->reported_user)
                                                        {{ $report->reported_user->first_name }}
                                                        {{ $report->reported_user->last_name }}
                                                        <br><small
                                                            class="text-muted">{{ $report->reported_user->email }}</small>
                                                    @else
                                                        N/A
                                                    @endif
                                                </td>
                                                <td>
                                                    <span class="badge bg-primary">{{ ucfirst($report->type) }}</span>
                                                </td>
                                                <td>
                                                    @switch($report->type)
                                                        @case('post')
                                                            {!! $report->post->content ?? 'N/A' !!}
                                                        @break

                                                        @case('comment')
                                                            {!! Str::limit($report->comment->comment ?? 'N/A', 50) !!}
                                                        @break

                                                        @case('community')
                                                            {!! $report->community->name ?? 'N/A' !!}
                                                        @break

                                                        @case('pet')
                                                            {!! $report->pet->name ?? 'N/A' !!}
                                                        @break

                                                        @case('profile')
                                                            @if ($report->profile)
                                                                {!! $report->profile->first_name !!}
                                                                {!! $report->profile->last_name !!}
                                                            @else
                                                                N/A
                                                            @endif
                                                        @break

                                                        @case('message')
                                                            {!! Str::limit($report->message->message_text ?? 'N/A', 50) !!}
                                                        @break

                                                        @default
                                                            N/A
                                                    @endswitch
                                                </td>
                                                <td>{{ Str::limit($report->reason, 100) }}</td>
                                                <td>
                                                    <span class="badge status-badge badge-{{ $report->status }}">
                                                        {{ ucfirst($report->status) }}
                                                    </span>
                                                </td>
                                                <td>{{ $report->created_at->format('M d, Y h:i A') }}</td>
                                                <td class="action-btns">
                                                    <div class="btn-group" role="group">
                                                        <!-- Preview Button -->
                                                        <button type="button" class="btn btn-sm btn-info preview-btn"
                                                            data-report-id="{{ $report->id }}" title="Preview Content"
                                                            style="height: 22px;">
                                                            <i class="fas fa-eye"></i>
                                                        </button>

                                                        <!-- Status Update Buttons -->
                                                        <form
                                                            action="{{ route('admin.reports.updateStatus', $report->id) }}"
                                                            method="POST" class="d-inline">
                                                            @csrf
                                                            @method('PUT')
                                                            <input type="hidden" name="status" value="resolved">
                                                            <button type="submit" class="btn btn-sm btn-success"
                                                                title="Mark Resolved">
                                                                <i class="fas fa-check"></i>
                                                            </button>
                                                        </form>

                                                        <form
                                                            action="{{ route('admin.reports.updateStatus', $report->id) }}"
                                                            method="POST" class="d-inline">
                                                            @csrf
                                                            @method('PUT')
                                                            <input type="hidden" name="status" value="rejected">
                                                            <button type="submit" class="btn btn-sm btn-warning"
                                                                title="Mark Rejected">
                                                                <i class="fas fa-times"></i>
                                                            </button>
                                                        </form>

                                                        <!-- Delete Content Button -->
                                                        <form
                                                            action="{{ route('admin.reports.deleteContent', $report->id) }}"
                                                            method="POST" class="delete-content-form d-inline">
                                                            @csrf
                                                            @method('POST')
                                                            <button type="submit" class="btn btn-sm btn-danger"
                                                                title="Delete Content">
                                                                <i class="fas fa-trash-alt"></i>
                                                            </button>
                                                        </form>

                                                        <!-- Delete Report Button -->
                                                        <form action="{{ route('admin.reports.destroy', $report->id) }}"
                                                            method="POST" class="delete-report-form d-inline">
                                                            @csrf
                                                            @method('DELETE')
                                                            <button type="submit" class="btn btn-sm btn-outline-danger"
                                                                title="Delete Report Only">
                                                                <i class="fas fa-trash"></i>
                                                            </button>
                                                        </form>
                                                    </div>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @else
                            <div class="alert alert-info text-center">
                                <i class="fas fa-info-circle"></i> No reports found.
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Preview Modal -->
    <div class="modal fade preview-modal" id="previewModal" tabindex="-1" aria-labelledby="previewModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header preview-header">
                    <h5 class="modal-title" id="previewModalLabel">
                        <i class="fas fa-eye me-2"></i>Content Preview
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                        aria-label="Close"></button>
                </div>
                <div class="modal-body preview-body">
                    <div class="content-preview">
                        <h6 id="previewType" class="text-primary mb-3"></h6>
                        <div id="previewContent" class="mb-3"></div>
                        <div class="content-meta">
                            <div id="previewAuthor"></div>
                            <div id="previewDate"></div>
                        </div>
                    </div>
                    <div class="action-buttons">

                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                            <i class="fas fa-times me-1"></i>Close
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="{{ URL::asset('build/libs/datatables.net/js/jquery.dataTables.min.js') }}"></script>
    <script src="{{ URL::asset('build/libs/datatables.net-bs4/js/dataTables.bootstrap4.min.js') }}"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>

    <script>
        let currentReportId = null;

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

        $(document).ready(function() {
            $('.datatable').DataTable({
                responsive: true,
                order: [
                    [0, 'desc']
                ],
                @if ($reports->count() == 0)
                    searching: false,
                    paging: false,
                    info: false
                @endif
            });

            // Preview content
            $('.preview-btn').on('click', function() {
                currentReportId = $(this).data('report-id');

                $.ajax({
                    url: "{{ url('admin/reports') }}/" + currentReportId + "/preview",
                    type: 'GET',
                    success: function(response) {
                        $('#previewType').html('<strong>Type:</strong> ' + response.type.charAt(
                            0).toUpperCase() + response.type.slice(1));
                        $('#previewContent').html('<strong>Content:</strong><br>' + (response
                            .content || 'No content available'));
                        $('#previewAuthor').html('<strong>Author:</strong> ' + (response
                            .author || 'Unknown'));
                        $('#previewDate').html('<strong>Created:</strong> ' + (response
                            .created_at ? new Date(response.created_at)
                            .toLocaleString() : 'Unknown'));

                        $('#previewModal').modal('show');
                    },
                    error: function() {
                        Swal.fire('Error', 'Failed to load preview', 'error');
                    }
                });
            });

            // Resolve button in modal
            $('#resolveBtn').on('click', function() {
                if (currentReportId) {
                    $.ajax({
                        url: "{{ url('admin/reports') }}/" + currentReportId + "/status",
                        type: 'PUT',
                        data: {
                            _token: "{{ csrf_token() }}",
                            status: 'resolved'
                        },
                        success: function() {
                            $('#previewModal').modal('hide');
                            location.reload();
                        },
                        error: function() {
                            Swal.fire('Error', 'Failed to update status', 'error');
                        }
                    });
                }
            });

            // Delete content button in modal
            $('#deleteContentBtn').on('click', function() {
                if (currentReportId) {
                    Swal.fire({
                        title: 'Are you sure?',
                        text: "This will delete the reported content but keep the report log!",
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonColor: '#3085d6',
                        cancelButtonColor: '#d33',
                        confirmButtonText: 'Yes, delete content!'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            $.ajax({
                                url: "{{ url('admin/reports') }}/" + currentReportId +
                                    "/delete-content",
                                type: 'POST',
                                data: {
                                    _token: "{{ csrf_token() }}"
                                },
                                success: function() {
                                    $('#previewModal').modal('hide');
                                    location.reload();
                                },
                                error: function() {
                                    Swal.fire('Error', 'Failed to delete content',
                                        'error');
                                }
                            });
                        }
                    });
                }
            });

            // Delete report only
            $('.delete-report-form').on('submit', function(e) {
                e.preventDefault();
                const form = this;

                Swal.fire({
                    title: 'Delete Report Only?',
                    text: "This will delete the report log but keep the content!",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#3085d6',
                    cancelButtonColor: '#d33',
                    confirmButtonText: 'Yes, delete report!'
                }).then((result) => {
                    if (result.isConfirmed) {
                        form.submit();
                    }
                });
            });

            // Delete content
            $('.delete-content-form').on('submit', function(e) {
                e.preventDefault();
                const form = this;

                Swal.fire({
                    title: 'Delete Content?',
                    text: "This will delete the reported content but keep the report log!",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#3085d6',
                    cancelButtonColor: '#d33',
                    confirmButtonText: 'Yes, delete content!'
                }).then((result) => {
                    if (result.isConfirmed) {
                        form.submit();
                    }
                });
            });
        });
    </script>
@endsection
