@extends('admin.layouts.master')
@section('title')
    Events Management
@endsection
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<link href="{{ URL::asset('build/libs/admin-resources/jquery.vectormap/jquery-jvectormap-1.2.2.css') }}" rel="stylesheet"
    type="text/css" />
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
</style>

@section('content')
    <div class="container-fluid py-4">
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <div>
                            <h3 class="page-title mb-0" style="color:black!important;">Events Management</h3>
                            <p class="page-subtitle mb-0">Manage your Event posts</p>
                        </div>
                        <div>
                            <a href="{{ route('admin.events.create') }}" class="btn btn-primary">
                                <i class="fas fa-plus me-2"></i> Create New Event
                            </a>
                        </div>
                    </div>

                    <div class="card-body">


                        <div class="table-responsive">
                            <table class="table table-hover datatable">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>Image</th>
                                        <th>Category</th>
                                        <th>Title</th>
                                        <th>Event Date</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($events as $event)
                                        <tr>
                                            <td>{{ $loop->iteration }}</td>

                                            <!-- Event Image -->
                                            <td>
                                                @if ($event->image)
                                                    <img src="{{ asset($event->image) }}" alt="{{ $event->title }}"
                                                        style="width: 60px; height: 40px; object-fit: cover; border-radius: 4px;">
                                                @else
                                                    <div
                                                        style="width: 60px; height: 40px; background: #f8f9fa; display: flex; align-items: center; justify-content: center; border-radius: 4px;">
                                                        <i class="fas fa-image text-muted"></i>
                                                    </div>
                                                @endif
                                            </td>

                                            <!-- Event Category -->
                                            <td>
                                                <span class="badge bg-info">
                                                    {{ $event->category ? $event->category->name : 'No Category' }}
                                                </span>
                                            </td>

                                            <!-- Event Title & Description -->
                                            <td>
                                                <div class="fw-semibold">{{ Str::limit($event->title, 50) }}</div>
                                            </td>

                                            <!-- Event Date -->
                                            <td>{{ \Carbon\Carbon::parse($event->event_date)->format('M d, Y') }}</td>

                                            <!-- Actions -->
                                            <td>
                                                <div class="d-flex gap-2">
                                                    <a href="{{ route('admin.events.edit', $event->id) }}"
                                                        class="btn btn-sm btn-primary edit-btn"
                                                        style="height: 24px!important;">
                                                        <i class="fas fa-edit"></i>
                                                    </a>
                                                    <form action="{{ route('admin.events.delete', $event->id) }}"
                                                        method="POST" style="display: inline;">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="btn btn-sm btn-danger delete-btn"
                                                            onclick="return confirm('Are you sure you want to delete this event?')">
                                                            <i class="fas fa-trash"></i>
                                                        </button>
                                                    </form>
                                                </div>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="6" class="text-center py-4">
                                                <div class="text-muted">
                                                    <i class="fas fa-inbox fa-2x mb-3"></i>
                                                    <p>No events found. Create your first event!</p>
                                                </div>
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
@endsection

@section('scripts')
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
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
    </script>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Add confirmation for delete buttons
            const deleteButtons = document.querySelectorAll('.delete-btn');
            deleteButtons.forEach(button => {
                button.addEventListener('click', function(e) {
                    if (!confirm(
                            'Are you sure you want to delete this event? This action cannot be undone.'
                        )) {
                        e.preventDefault();
                    }
                });
            });
        });
    </script>
    <script>
        $(document).ready(function() {
            $('.datatable').DataTable({
                responsive: true,
                columnDefs: [{
                        orderable: false,
                        targets: [5]
                    },
                    {
                        searchable: false,
                        targets: [5]
                    }
                ],
                order: [
                    [0, 'asc']
                ] // Default sort by ID
            });

        });
    </script>
@endsection
