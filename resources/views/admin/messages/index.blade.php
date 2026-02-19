@extends('admin.layouts.master')
@section('title')
    Messages Management
@endsection


    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="{{ URL::asset('build/libs/datatables.net-bs4/css/dataTables.bootstrap4.min.css') }}" rel="stylesheet">
    <link href="{{ URL::asset('build/libs/datatables.net-buttons-bs4/css/buttons.bootstrap4.min.css') }}" rel="stylesheet">
    <link href="{{ URL::asset('build/libs/datatables.net-responsive-bs4/css/responsive.bootstrap4.min.css') }}"
        rel="stylesheet">

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

    .status-toggle {
        position: relative;
        display: inline-block;
        width: 60px;
        height: 30px;
    }

    .status-toggle input {
        opacity: 0;
        width: 0;
        height: 0;
    }

    .status-slider {
        position: absolute;
        cursor: pointer;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background-color: #ffc107;
        transition: .4s;
        border-radius: 34px;
    }

    .status-slider:before {
        position: absolute;
        content: "";
        height: 22px;
        width: 22px;
        left: 4px;
        bottom: 4px;
        background-color: white;
        transition: .4s;
        border-radius: 50%;
    }

    input:checked+.status-slider {
        background-color: #28a745;
    }

    input:checked+.status-slider:before {
        transform: translateX(30px);
    }

    .status-label {
        margin-left: 10px;
        font-weight: 500;
    }

    .status-on {
        color: #28a745;
    }

    .status-off {
        color: #ffc107;
    }

    .btn-sm {
        width: 32px;
        height: 32px;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 6px !important;
        /* Added border radius */
        margin: 2px;
        /* Added gap between buttons */
    }

    .btn-group .btn-sm {
        margin: 0 2px;
        /* Gap between buttons in group */
    }

    .btn-group {
        gap: 4px;
        /* Added gap between button groups */
    }

    /* Bulk delete button styling */
    #bulkDeleteBtn {
        border-radius: 8px;
        padding: 8px 16px;
        font-weight: 500;
        margin-left: 10px;
    }

    .dataTables_wrapper .dataTables_length,
    .dataTables_wrapper .dataTables_filter {
        margin-bottom: 15px;
    }

    .dataTables_wrapper .dataTables_filter input {
        margin-left: 10px;
        border: 1px solid #ced4da;
        border-radius: 4px;
        padding: 4px 8px;
    }

    .dataTables_wrapper .dataTables_length select {
        border: 1px solid #ced4da;
        border-radius: 4px;
        padding: 4px;
    }

    .dataTables_wrapper .dataTables_paginate .paginate_button {
        padding: 4px 10px;
        margin-left: 2px;
        border: 1px solid #dee2e6;
        border-radius: 4px;
    }

    .dataTables_wrapper .dataTables_paginate .paginate_button.current {
        background: #007bff;
        color: white !important;
        border-color: #007bff;
    }

    .dataTables_wrapper .dataTables_paginate .paginate_button:hover {
        background: #e9ecef;
        border-color: #dee2e6;
    }

    .card-header {
        background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
        border-bottom: 1px solid #dee2e6;
    }

    .table th {
        border-top: none;
        font-weight: 600;
        color: #495057;
        background-color: #f8f9fa;
    }

    .badge {
        font-size: 0.75em;
        padding: 0.35em 0.65em;
        border-radius: 6px;
        /* Added border radius */
    }

    .select-all-checkbox {
        margin-right: 10px;
    }

    .dataTables_empty {
        text-align: center;
        padding: 30px !important;
    }

    /* Action buttons container */
    .action-buttons {
        display: flex;
        gap: 4px;
        flex-wrap: nowrap;
    }

    /* Form buttons styling */
    .btn-group form {
        margin: 0;
    }

    /* Modal styling */
    .modal-header {
        background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
        border-bottom: 1px solid #dee2e6;
    }
</style>
@section('content')
    <!-- Mark as Read Modal -->
    <div class="modal fade" id="markAsReadModal" tabindex="-1" aria-labelledby="markAsReadModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-sm">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="markAsReadModalLabel">Mark as Read</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p>Are you sure you want to mark this message as read?</p>
                    <form id="markAsReadForm" method="POST">
                        @csrf
                        <div class="mb-3">
                            <label for="messageId" class="form-label">Message ID</label>
                            <input type="text" class="form-control" id="messageId" name="message_id" readonly>
                        </div>
                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-success">Mark as Read</button>
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <div class="container-fluid py-4">
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h4 class="mb-0">Messages Management</h4>
                        <div>
                            <button type="button" class="btn btn-danger" id="bulkDeleteBtn" style="display: none;">
                                <i class="fas fa-trash"></i> Delete Selected
                            </button>
                        </div>
                    </div>
                    <div class="card-body">


                        @if ($messages->count() > 0)
                            <div class="table-responsive">
                                <!-- Bulk Delete Form - Changed to POST -->
                                <form id="bulkDeleteForm" action="{{ route('admin.messages.bulkDelete') }}" method="POST">
                                    @csrf
                                    <!-- Remove @method('DELETE') since we're using POST -->
                                    <input type="hidden" name="ids" id="selectedIds">

                                    <table id="messages-datatable" class="table table-striped table-hover w-100">
                                        <thead>
                                            <tr>
                                                <th width="50">
                                                    <input type="checkbox" id="selectAll" class="select-all-checkbox">
                                                </th>
                                                <th>ID</th>
                                                <th>Sender</th>
                                                <th>Receiver</th>
                                                <th>Message Type</th>
                                                <th>Message</th>
                                                <th>Status</th>
                                                <th>Date</th>
                                                <th>Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @php
                                                $counter = 1;
                                            @endphp
                                            @foreach ($messages as $message)
                                                <tr>
                                                    <td>
                                                        <input type="checkbox" class="message-checkbox"
                                                            value="{{ $message->id }}">
                                                    </td>
                                                    <td>{{ $counter++ }}</td>
                                                    <td>
                                                        <strong>{{ $message->sender->name ?? 'Unknown User' }}</strong>
                                                    </td>
                                                    <td>
                                                        <strong>{{ $message->receiver->name ?? 'Unknown User' }}</strong>
                                                    </td>
                                                    <td>
                                                        <span
                                                            class="badge bg-{{ $message->message_type == 'text' ? 'primary' : 'warning' }}">
                                                            {{ ucfirst($message->message_type) }}
                                                        </span>
                                                    </td>
                                                    <td>
                                                        @if ($message->message_type == 'text')
                                                            {{ Str::limit($message->message_text, 50) }}
                                                        @else
                                                            <i class="fas fa-file"></i>
                                                            {{ basename($message->media_path) }}
                                                        @endif
                                                    </td>
                                                    <td>
                                                        @if ($message->is_seen)
                                                            <span class="badge bg-success">Read</span>
                                                        @else
                                                            <span class="badge bg-warning">Unread</span>
                                                        @endif
                                                    </td>
                                                    <td data-order="{{ $message->created_at->format('Y-m-d') }}">
                                                        {{ $message->created_at->format('M d, Y') }}
                                                    </td>
                                                    <td>
                                                        <div class="action-buttons">
                                                            @if (!$message->is_seen)
                                                                <button type="button"
                                                                    class="btn btn-success btn-sm mark-as-read-btn"
                                                                    data-message-id="{{ $message->id }}"
                                                                    title="Mark as Read">
                                                                    <i class="fas fa-check"></i>
                                                                </button>
                                                            @endif

                                                            {{-- <a href="{{ route('admin.messages.conversation', [$message->sender_id, $message->receiver_id]) }}"
                                                                class="btn btn-info btn-sm" title="View Conversation">
                                                                <i class="fas fa-history"></i>
                                                            </a> --}}

                                                            <form
                                                                action="{{ route('admin.messages.destroy', $message->id) }}"
                                                                method="POST" class="d-inline">
                                                                @csrf
                                                                @method('DELETE')
                                                                <button type="submit" class="btn btn-danger btn-sm"
                                                                    onclick="return confirm('Are you sure you want to delete this message?')"
                                                                    title="Delete Message">
                                                                    <i class="fas fa-trash"></i>
                                                                </button>
                                                            </form>
                                                        </div>
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </form>
                            </div>
                        @else
                            <div class="text-center py-5">
                                <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
                                <h4>No messages found</h4>
                                <p class="text-muted">There are no messages in the system yet.</p>
                            </div>
                        @endif
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
    <script src="{{ URL::asset('build/libs/datatables.net-responsive/js/dataTables.responsive.min.js') }}"></script>
    <script src="{{ URL::asset('build/libs/datatables.net-responsive-bs4/js/responsive.bootstrap4.min.js') }}"></script>

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

        @if (session('error'))
            Swal.fire({
                toast: true,
                icon: 'error',
                title: "{{ session('error') }}",
                position: 'top-end',
                showConfirmButton: false,
                timer: 3000,
                timerProgressBar: true,
            });
        @endif
        $(document).ready(function() {
            // Initialize DataTable
            var table = $('#messages-datatable').DataTable({
                "order": [
                    [1, "desc"]
                ], // Default sort by ID column (index 1)
                "columnDefs": [{
                        "orderable": false,
                        "targets": [0, 8] // Disable sorting for checkbox and actions columns
                    },
                    {
                        "searchable": false,
                        "targets": [0, 8] // Disable searching for checkbox and actions columns
                    },
                    {
                        "width": "5%",
                        "targets": [0, 8] // Set width for checkbox and actions columns
                    }
                ],
                "language": {
                    "emptyTable": "No messages found",
                    "info": "Showing _START_ to _END_ of _TOTAL_ messages",
                    "infoEmpty": "Showing 0 to 0 of 0 messages",
                    "infoFiltered": "(filtered from _MAX_ total messages)",
                    "lengthMenu": "Show _MENU_ messages",
                    "loadingRecords": "Loading...",
                    "processing": "Processing...",
                    "search": "Search:",
                    "zeroRecords": "No matching messages found",
                    "paginate": {
                        "first": "First",
                        "last": "Last",
                        "next": "Next",
                        "previous": "Previous"
                    }
                },
                "responsive": true,
                "dom": '<"row"<"col-sm-12 col-md-6"l><"col-sm-12 col-md-6"f>>rt<"row"<"col-sm-12 col-md-6"i><"col-sm-12 col-md-6"p>>',
                "drawCallback": function(settings) {
                    // Re-initialize bulk selection after DataTable redraw
                    initializeBulkSelection();
                    // Re-initialize mark as read buttons after DataTable redraw
                    initializeMarkAsReadButtons();
                }
            });

            // Mark as Read functionality
            function initializeMarkAsReadButtons() {
                $('.mark-as-read-btn').on('click', function() {
                    const messageId = $(this).data('message-id');
                    const modal = $('#markAsReadModal');

                    // Set the message ID in the form
                    modal.find('#messageId').val(messageId);

                    // Set the form action
                    modal.find('#markAsReadForm').attr('action',
                        `/petz-info/public/admin/messages/${messageId}/mark-as-read`);

                    // Show the modal
                    modal.modal('show');
                });
            }

            // Initialize mark as read buttons on page load
            initializeMarkAsReadButtons();

            // Bulk selection functionality
            function initializeBulkSelection() {
                const selectAll = document.getElementById('selectAll');
                const checkboxes = document.querySelectorAll('.message-checkbox');
                const bulkDeleteBtn = document.getElementById('bulkDeleteBtn');
                const selectedIds = document.getElementById('selectedIds');

                // Select All functionality
                if (selectAll) {
                    selectAll.addEventListener('change', function() {
                        checkboxes.forEach(checkbox => {
                            checkbox.checked = selectAll.checked;
                        });
                        toggleBulkDeleteButton();
                    });
                }

                // Individual checkbox functionality
                checkboxes.forEach(checkbox => {
                    checkbox.addEventListener('change', function() {
                        if (!this.checked) {
                            if (selectAll) selectAll.checked = false;
                        } else {
                            const allChecked = Array.from(checkboxes).every(cb => cb.checked);
                            if (selectAll) selectAll.checked = allChecked;
                        }
                        toggleBulkDeleteButton();
                    });
                });

                // Toggle bulk delete button
                function toggleBulkDeleteButton() {
                    const checkedBoxes = document.querySelectorAll('.message-checkbox:checked');
                    if (checkedBoxes.length > 0) {
                        bulkDeleteBtn.style.display = 'inline-block';

                        // Update hidden input with selected IDs
                        const ids = Array.from(checkedBoxes).map(cb => cb.value);
                        selectedIds.value = JSON.stringify(ids);
                    } else {
                        bulkDeleteBtn.style.display = 'none';
                        selectedIds.value = '';
                    }
                }

                // Bulk delete confirmation - Using POST method
                bulkDeleteBtn.addEventListener('click', function() {
                    const ids = JSON.parse(selectedIds.value || '[]');
                    if (ids.length === 0) {
                        alert('Please select at least one message to delete.');
                        return;
                    }

                    if (confirm(`Are you sure you want to delete ${ids.length} selected message(s)?`)) {
                        document.getElementById('bulkDeleteForm').submit();
                    }
                });

                // Initialize the button state
                toggleBulkDeleteButton();
            }

            // Initialize bulk selection on page load
            initializeBulkSelection();

            // Auto-hide success alert after 3 seconds
            setTimeout(function() {
                $('.alert-success').fadeOut('slow');
            }, 3000);
        });
    </script>
@endsection
