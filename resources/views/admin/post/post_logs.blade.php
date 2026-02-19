@extends('admin.layouts.master')
@section('title')
    Post Logs
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

    .post-image {
        width: 60px;
        height: 60px;
        object-fit: cover;
        border-radius: 8px;
        border: 2px solid #e9ecef;
        cursor: pointer;
    }

    .btn-sm {
        width: 32px;
        height: 32px;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .badge-deleted {
        background-color: #dc3545;
        color: white;
    }

    .badge-active {
        background-color: #28a745;
        color: white;
    }

    .card-header {
        background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
        border-bottom: 1px solid #dee2e6;
    }
</style>

@section('content')
    <div class="container-fluid py-4">
        <div class="row">
            <div class="col-12">
                <div class="card shadow-sm">
                    <div class="card-header d-flex justify-content-between align-items-center py-3">
                        <div>
                            <h3 class="page-title mb-1" style="color: #343a40 !important;">
                                <i class="fas fa-history me-2"></i>Post Logs
                            </h3>
                            <p class="page-subtitle mb-0 text-muted">View and restore deleted posts</p>
                        </div>
                        <div>
                            <a href="{{ route('admin.post.index') }}" class="btn btn-primary">
                                <i class="fas fa-arrow-left me-2"></i> Back to Posts
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
                                        <th>Pet Name</th>
                                        <th>Parent Name</th>
                                        <th>Post Type</th>
                                        <th>Status</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($posts as $post)
                                        <tr>
                                            <td>{{ $loop->iteration }}</td>

                                            <!-- Post Image -->
                                            <td>
                                                @if ($post->images && count($post->images) > 0)
                                                    <img src="{{ asset($post->images[0]['image_path']) }}" alt="Post Image"
                                                        class="post-image" style="width: 60px; height: 60px;">
                                                @else
                                                    <div class="post-image bg-light d-flex align-items-center justify-content-center"
                                                        style="width: 60px; height: 60px; border-radius: 8px; cursor: default;">
                                                        <i class="fas fa-image text-muted"></i>
                                                    </div>
                                                @endif
                                            </td>

                                            <!-- Pet Name -->
                                            <td>
                                                @if ($post->pet)
                                                    <strong>{{ $post->pet->name }}</strong>
                                                @else
                                                    <span class="text-muted">No Pet</span>
                                                @endif
                                            </td>

                                            <!-- Parent Name -->
                                            <td>
                                                @if ($post->parent)
                                                    <div>
                                                        <strong>{{ $post->parent->first_name }}
                                                            {{ $post->parent->last_name }}</strong>
                                                    </div>
                                                    <small class="text-muted">{{ $post->parent->email }}</small>
                                                @else
                                                    <span class="text-muted">No Parent</span>
                                                @endif
                                            </td>

                                            <!-- Post Type -->
                                            <td>
                                                <span
                                                    class="badge bg-{{ $post->post_type == 'normal' ? 'primary' : ($post->post_type == 'birthday' ? 'success' : ($post->post_type == 'repost' ? 'info' : 'secondary')) }}">
                                                    {{ ucfirst($post->post_type ?? 'Normal') }}
                                                </span>
                                            </td>

                                            <!-- Status -->
                                            <td>
                                                @if ($post->deleted_at)
                                                    <span class="badge badge-deleted">Deleted</span>
                                                @else
                                                    <span class="badge badge-active">Active</span>
                                                @endif
                                            </td>



                                            <!-- Actions -->
                                            <td>
                                                <div class="d-flex gap-2 align-items-center">
                                                    <!-- Restore Button -->
                                                    <form action="{{ route('admin.post.restore', $post->id) }}"
                                                        method="POST" class="restore-form m-0">
                                                        @csrf
                                                        @method('PUT')
                                                        <button type="submit"
                                                            class="btn btn-sm btn-success restore-btn px-2"
                                                            title="Restore Post">
                                                            <i class="fas fa-undo"></i>
                                                        </button>
                                                    </form>

                                                    <!-- Permanent Delete Button -->
                                                    <form action="{{ route('admin.post.force-delete', $post->id) }}"
                                                        method="POST" class="force-delete-form m-0">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit"
                                                            class="btn btn-sm btn-danger force-delete-btn px-2"
                                                            title="Permanently Delete">
                                                            <i class="fas fa-trash"></i>
                                                        </button>
                                                    </form>


                                                </div>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="8" class="text-center py-4">
                                                <div class="text-muted">
                                                    <i class="fas fa-history fa-2x mb-3"></i>
                                                    <p>No deleted posts found in logs.</p>
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

    <!-- Post Details Modal -->
    <div class="modal fade" id="postDetailsModal" tabindex="-1" aria-labelledby="postDetailsModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="postDetailsModalLabel">
                        <i class="fas fa-info-circle me-2"></i>Post Details
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body" id="postDetailsContent">
                    <!-- Dynamic content will be loaded here -->
                    <div class="text-center py-5">
                        <div class="spinner-border text-primary" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                        <p class="mt-2 text-muted">Loading post details...</p>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
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
                pageLength: 10,
                order: [
                    [6, 'desc']
                ], // Sort by deleted date descending
                language: {
                    search: "Search logs:",
                    lengthMenu: "Show _MENU_ entries",
                    info: "Showing _START_ to _END_ of _TOTAL_ posts",
                    infoEmpty: "Showing 0 to 0 of 0 posts",
                    infoFiltered: "(filtered from _MAX_ total posts)"
                }
            });

            // Restore button confirmation
            $(document).on('submit', '.restore-form', function(e) {
                e.preventDefault();
                const form = this;

                Swal.fire({
                    title: 'Restore Post?',
                    text: "This post will be restored and become active again!",
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonColor: '#28a745',
                    cancelButtonColor: '#6c757d',
                    confirmButtonText: 'Yes, restore it!',
                    cancelButtonText: 'Cancel',
                    reverseButtons: true
                }).then((result) => {
                    if (result.isConfirmed) {
                        form.submit();
                    }
                });
            });

            // Permanent delete button confirmation
            $(document).on('submit', '.force-delete-form', function(e) {
                e.preventDefault();
                const form = this;

                Swal.fire({
                    title: 'Permanently Delete?',
                    text: "This action cannot be undone! The post will be permanently removed from the database!",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#dc3545',
                    cancelButtonColor: '#6c757d',
                    confirmButtonText: 'Yes, delete permanently!',
                    cancelButtonText: 'Cancel',
                    reverseButtons: true,
                    dangerMode: true
                }).then((result) => {
                    if (result.isConfirmed) {
                        form.submit();
                    }
                });
            });

            // View details modal
            $('#postDetailsModal').on('show.bs.modal', function(event) {
                const button = $(event.relatedTarget);
                const postId = button.data('post-id');
                const modal = $(this);

                // Show loading state
                $('#postDetailsContent').html(`
                    <div class="text-center py-5">
                        <div class="spinner-border text-primary" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                        <p class="mt-2 text-muted">Loading post details...</p>
                    </div>
                `);

                // Load post details via AJAX
                $.ajax({
                    url: `/petz-info/public/admin/posts/${postId}/details`,
                    method: 'GET',
                    success: function(data) {
                        $('#postDetailsContent').html(data);
                    },
                    error: function() {
                        $('#postDetailsContent').html(`
                            <div class="text-center py-5">
                                <i class="fas fa-exclamation-circle text-danger fa-2x mb-3"></i>
                                <h5>Failed to load post details</h5>
                                <p class="text-muted">Please try again later.</p>
                            </div>
                        `);
                    }
                });
            });
        });
    </script>
@endsection
