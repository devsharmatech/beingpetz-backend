@extends('admin.layouts.master')
@section('title')
  Comments List
@endsection

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

    /* Tooltip styles */
    .tooltip-wrapper {
        position: relative;
        display: inline-block;
        cursor: pointer;
    }
    td {
        font-size: small!important;
    }

    .tooltip-text {
        visibility: hidden;
        width: 300px;
        background-color: #333;
        color: #fff;
        text-align: center;
        border-radius: 6px;
        padding: 8px;
        position: absolute;
        z-index: 1;
        bottom: 125%;
        left: 50%;
        transform: translateX(-50%);
        opacity: 0;
        transition: opacity 0.3s;
        font-size: 14px;
        line-height: 1.4;
        word-wrap: break-word;
    }

    .tooltip-wrapper:hover .tooltip-text {
        visibility: visible;
        opacity: 1;
    }

    .truncated-text {
        display: inline-block;
        max-width: 150px;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
    }
</style>

@section('content')
    <div class="container-fluid py-4">
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <div>
                            <h3 class="page-title mb-0" style="color:black!important;">Comment Lists</h3>
                        </div>
                        
                    </div>

                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover datatable">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>Blog Name</th>
                                        <th>Name</th>
                                        <th>Email</th>
                                        <th>Comment</th>
                                        <th>Created At</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($comments as $comment)
                                        <tr>
                                            <td>{{ $loop->iteration }}</td>
                                            <td>
                                                <div class="tooltip-wrapper">
                                                    <span class="truncated-text">
                                                        {{ Str::limit($comment->blog->title ?? 'Unknown Blog', 20) }}
                                                    </span>
                                                    <div class="tooltip-text">
                                                        {{ $comment->blog->title ?? 'Unknown Blog' }}
                                                    </div>
                                                </div>
                                            </td>
                                            <td>{{ $comment->name }}</td>
                                            <td>{{ $comment->email }}</td>
                                            <td>
                                                <div class="tooltip-wrapper">
                                                    <span>
                                                        {{ Str::words($comment->comment, 5, '...') }}
                                                    </span>
                                                    <div class="tooltip-text">
                                                        {{ $comment->comment }}
                                                    </div>
                                                </div>
                                            </td>
                                            <td>{{ $comment->created_at->format('M d, Y') }}</td>
                                            <td>
                                                <div class="d-flex gap-2">
                                                    <form action="{{ route('admin.comments.destroy', $comment->id) }}" 
                                                          method="POST" 
                                                          class="delete-form">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="btn btn-sm btn-danger delete-btn">
                                                            <i class="fas fa-trash"></i>
                                                        </button>
                                                    </form>
                                                </div>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="7" class="text-center py-4">
                                                <div class="text-muted">
                                                    <i class="fas fa-inbox fa-2x mb-3"></i>
                                                   <p>Data Not Found!!</p>
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
            $('.datatable').DataTable({
                responsive: true,
                columnDefs: [{
                    orderable: false,
                    targets: [6] // Updated to match the new column index
                }, {
                    searchable: false,
                    targets: [6] // Updated to match the new column index
                }],
                order: [
                    [0, 'asc']
                ]
            });

            // Delete button confirmation
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
        });
    </script>
@endsection