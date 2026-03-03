@extends('admin.layouts.master')
@section('title')
    Post Management
@endsection


    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="{{ URL::asset('build/libs/datatables.net-bs4/css/dataTables.bootstrap4.min.css') }}" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/viewerjs/1.11.6/viewer.min.css" rel="stylesheet">


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
        transition: transform 0.2s;
    }

    .post-image:hover {
        transform: scale(1.05);
        border-color: #007bff;
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
    }

    .gallery-item {
        position: relative;
        margin-bottom: 15px;
    }

    .gallery-item img,
    .gallery-item video {
        width: 100%;
        height: 150px;
        object-fit: cover;
        border-radius: 8px;
        cursor: pointer;
    }

    .gallery-item img:hover,
    .gallery-item video:hover {
        opacity: 0.9;
    }

    .delete-media-btn {
        position: absolute;
        top: 5px;
        right: 5px;
        width: 25px;
        height: 25px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        background: rgba(220, 53, 69, 0.9);
        color: white;
        border: none;
        font-size: 12px;
        opacity: 0;
        transition: opacity 0.3s;
        z-index: 10;
    }

    .gallery-item:hover .delete-media-btn {
        opacity: 1;
    }

    .empty-gallery {
        text-align: center;
        padding: 30px;
        color: #6c757d;
    }

    .empty-gallery i {
        font-size: 48px;
        margin-bottom: 15px;
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
    }

    .media-count-badge {
        position: absolute;
        top: -5px;
        right: -5px;
        background: #dc3545;
        color: white;
        border-radius: 50%;
        width: 18px;
        height: 18px;
        font-size: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
    }
</style>

@section('content')
    <div class="container-fluid py-4">
        <div class="row">
            <div class="col-12">
                <div class="card shadow-sm">
                    <div class="card-header d-flex justify-content-between align-items-center py-3">
                        <div>
                            <h3 class="page-title mb-1" style="color: #343a40 !important;">Post Management</h3>
                            <p class="page-subtitle mb-0 text-muted">Manage your blog posts and articles</p>
                        </div>
                        <div class="d-flex gap-2">
                            <!-- Post Logs Button - यह नया button add करें -->
                            <a href="{{ route('admin.post.history-log') }}" class="btn btn-info">
                                <i class="fas fa-history me-2"></i> Post Logs
                            </a>
                            <a href="{{ route('admin.post.create') }}" class="btn btn-primary">
                                <i class="fas fa-plus me-2"></i> Create Post
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
                                        <th>Created Date</th>
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
                                                    <div class="position-relative d-inline-block">
                                                        <img src="{{ asset($post->images[0]['image_path']) }}"
                                                            alt="Post Image" class="post-image" data-bs-toggle="modal"
                                                            data-bs-target="#galleryModal"
                                                            data-post-id="{{ $post->id }}"
                                                            style="width: 60px; height: 60px;">
                                                        @if (count($post->images) > 1 || ($post->videos && count($post->videos) > 0))
                                                            <span class="media-count-badge">
                                                                {{ count($post->images) + ($post->videos ? count($post->videos) : 0) }}
                                                            </span>
                                                        @endif
                                                    </div>
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
                                                <div class="d-flex align-items-center">
                                                    <label class="status-toggle mb-0">
                                                        <input type="checkbox" class="status-checkbox"
                                                            data-id="{{ $post->id }}"
                                                            {{ $post->is_public ? 'checked' : '' }}>
                                                        <span class="status-slider"></span>
                                                    </label>
                                                    <span
                                                        class="status-label {{ $post->is_public ? 'status-on' : 'status-off' }}">
                                                        {{ $post->is_public ? 'Published' : 'Draft' }}
                                                    </span>
                                                </div>
                                            </td>

                                            <!-- Created Date -->
                                            <td>{{ $post->created_at->format('M d, Y') }}</td>

                                            <!-- Actions -->
                                            <td>
                                                <div class="d-flex gap-2 align-items-center">
                                                    <!-- Edit Button -->
                                                    <a href="{{ route('admin.post.edit', $post->id) }}"
                                                        class="btn btn-sm btn-warning px-2" title="Edit Post">
                                                        <i class="fas fa-edit"></i>
                                                    </a>

                                                    <!-- Gallery Button -->
                                                    @if (($post->images && count($post->images) > 0) || ($post->videos && count($post->videos) > 0))
                                                        <button
                                                            class="btn btn-sm btn-info px-2 gallery-btn position-relative"
                                                            title="View Gallery" data-bs-toggle="modal"
                                                            data-bs-target="#galleryModal"
                                                            data-post-id="{{ $post->id }}">
                                                            <i class="fas fa-images"></i>
                                                        </button>
                                                    @else
                                                        <button class="btn btn-sm btn-secondary px-2" title="No Media"
                                                            disabled>
                                                            <i class="fas fa-images"></i>
                                                        </button>
                                                    @endif

                                                    <!-- Delete Button -->
                                                    <form action="{{ route('admin.post.delete', $post->id) }}"
                                                        method="POST" class="delete-form m-0">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="btn btn-sm btn-danger delete-btn px-2"
                                                            title="Delete Post">
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
                                                    <i class="fas fa-file-alt fa-2x mb-3"></i>
                                                    <p>No posts found. Create your first post!</p>
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

    <!-- Gallery Modal -->
    <div class="modal fade" id="galleryModal" tabindex="-1" aria-labelledby="galleryModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-xl">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="galleryModalLabel">
                        <i class="fas fa-images me-2"></i>Post Gallery
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div id="gallery-content">
                        <!-- Dynamic content will be loaded here via JavaScript -->
                        <div class="text-center py-5">
                            <div class="spinner-border text-primary" role="status">
                                <span class="visually-hidden">Loading...</span>
                            </div>
                            <p class="mt-2 text-muted">Loading gallery...</p>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="fas fa-times me-1"></i>Close
                    </button>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="{{ URL::asset('build/libs/datatables.net/js/jquery.dataTables.min.js') }}"></script>
    <script src="{{ URL::asset('build/libs/datatables.net-bs4/js/dataTables.bootstrap4.min.js') }}"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/viewerjs/1.11.6/viewer.min.js"></script>

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
            // Route definitions
            const galleryRoute = "{{ route('admin.post.gallery', ':id') }}";
            const toggleStatusRoute = "{{ route('admin.posts.toggleStatus', ':id') }}";
            const deleteImageRoute = "{{ route('admin.media.image.delete', ':id') }}";
            const deleteVideoRoute = "{{ route('admin.media.video.delete', ':id') }}";

            // Initialize DataTable
            $('.datatable').DataTable({
                responsive: true,
                pageLength: 5,
                lengthMenu: [
                    [3, 10, 25, 50, -1],
                    [3, 10, 25, 50, "All"]
                ],
                columnDefs: [{
                    orderable: false,
                    targets: [1, 7] // Image and Actions columns
                }, {
                    searchable: false,
                    targets: [1, 7] // Image and Actions columns
                }],
                order: [
                    [0, 'asc'] // Sort by ID
                ],
                language: {
                    search: "Search posts:",
                    lengthMenu: "Show _MENU_ entries",
                    info: "Showing _START_ to _END_ of _TOTAL_ posts",
                    infoEmpty: "Showing 0 to 0 of 0 posts",
                    infoFiltered: "(filtered from _MAX_ total posts)"
                }
            });

            // Delete button confirmation
            $(document).on('submit', '.delete-form', function(e) {
                e.preventDefault();
                const form = this;

                Swal.fire({
                    title: 'Are you sure?',
                    text: "You won't be able to revert this!",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#3085d6',
                    cancelButtonColor: '#d33',
                    confirmButtonText: 'Yes, delete it!',
                    cancelButtonText: 'Cancel'
                }).then((result) => {
                    if (result.isConfirmed) {
                        form.submit();
                    }
                });
            });

            // Status toggle functionality
            $('.status-checkbox').on('change', function() {
                const postId = $(this).data('id');
                const isChecked = $(this).is(':checked');
                const statusLabel = $(this).closest('td').find('.status-label');

                // Update label immediately for better UX
                if (isChecked) {
                    statusLabel.text('Published').removeClass('status-off').addClass('status-on');
                } else {
                    statusLabel.text('Draft').removeClass('status-on').addClass('status-off');
                }

                // Send request to server
                const url = toggleStatusRoute.replace(':id', postId);
                fetch(url, {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'Content-Type': 'application/json'
                        },
                        body: JSON.stringify({
                            is_public: isChecked ? 1 : 0
                        })
                    })
                    .then(res => res.json())
                    .then(data => {
                        if (!data.success) {
                            // Revert if failed
                            $(this).prop('checked', !isChecked);
                            if (isChecked) {
                                statusLabel.text('Draft').removeClass('status-on').addClass(
                                    'status-off');
                            } else {
                                statusLabel.text('Published').removeClass('status-off').addClass(
                                    'status-on');
                            }

                            Swal.fire({
                                toast: true,
                                icon: 'error',
                                title: 'Failed to update status',
                                position: 'top-end',
                                showConfirmButton: false,
                                timer: 3000
                            });
                        } else {
                            Swal.fire({
                                toast: true,
                                icon: 'success',
                                title: `Post ${isChecked ? 'published' : 'set to draft'}`,
                                position: 'top-end',
                                showConfirmButton: false,
                                timer: 2000
                            });
                        }
                    })
                    .catch(err => {
                        console.error(err);
                        // Revert on error
                        $(this).prop('checked', !isChecked);
                        if (isChecked) {
                            statusLabel.text('Draft').removeClass('status-on').addClass('status-off');
                        } else {
                            statusLabel.text('Published').removeClass('status-off').addClass(
                                'status-on');
                        }

                        Swal.fire({
                            toast: true,
                            icon: 'error',
                            title: 'Error updating status',
                            position: 'top-end',
                            showConfirmButton: false,
                            timer: 3000
                        });
                    });
            });

            // Gallery modal functionality
            $('#galleryModal').on('show.bs.modal', function(event) {
                const button = $(event.relatedTarget);
                const postId = button.data('post-id');
                const modal = $(this);

                // Update modal title
                modal.find('.modal-title').html(
                    `<i class="fas fa-images me-2"></i>Gallery - Post #${postId}`);

                // Show loading state
                $('#gallery-content').html(`
                    <div class="text-center py-5">
                        <div class="spinner-border text-primary" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                        <p class="mt-2 text-muted">Loading gallery...</p>
                    </div>
                `);

                // Load gallery content via AJAX
                const url = galleryRoute.replace(':id', postId);
                $.ajax({
                    url: url,
                    method: 'GET',
                    success: function(data) {
                        $('#gallery-content').html(data);

                        // Initialize image viewer for images only
                        const images = document.querySelectorAll('#media-gallery img');
                        if (images.length > 0) {
                            // You can initialize Viewer.js here if needed
                            // new Viewer(document.getElementById('media-gallery'));
                        }
                    },
                    error: function() {
                        $('#gallery-content').html(`
                            <div class="empty-gallery">
                                <i class="fas fa-exclamation-circle text-danger fa-2x mb-3"></i>
                                <h5>Failed to load gallery</h5>
                                <p class="text-muted">Please try again later.</p>
                            </div>
                        `);
                    }
                });
            });

            // Delete media functionality
            // Delete media functionality - Updated for both image and video
            $(document).on('click', '.delete-media-btn', function(e) {
                e.preventDefault();
                e.stopPropagation();

                const mediaId = $(this).data('media-id');
                const mediaType = $(this).data('media-type'); // 'image' or 'video'
                const button = $(this);
                const mediaItem = button.closest('.gallery-item');

                Swal.fire({
                    title: 'Delete Media?',
                    text: `This ${mediaType} will be permanently removed from the post!`,
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#d33',
                    cancelButtonColor: '#6c757d',
                    confirmButtonText: 'Yes, delete it!',
                    cancelButtonText: 'Cancel',
                    reverseButtons: true
                }).then((result) => {
                    if (result.isConfirmed) {
                        // Show loading on button
                        button.html('<i class="fas fa-spinner fa-spin"></i>');
                        button.prop('disabled', true);

                        // Determine the correct URL based on media type
                        const deleteUrl = mediaType === 'image' ?
                            deleteImageRoute.replace(':id', mediaId) :
                            deleteVideoRoute.replace(':id', mediaId);

                        // Send delete request
                        $.ajax({
                            url: deleteUrl,
                            method: 'DELETE',
                            headers: {
                                'X-CSRF-TOKEN': '{{ csrf_token() }}'
                            },
                            success: function(data) {
                                if (data.success) {
                                    // Remove the media item with animation
                                    mediaItem.fadeOut(300, function() {
                                        $(this).remove();

                                        // Check if gallery is empty now
                                        const remainingItems = $(
                                                '#media-gallery .gallery-item')
                                            .length;
                                        if (remainingItems === 0) {
                                            $('#media-gallery').html(`
                                    <div class="empty-gallery">
                                        <i class="fas fa-images fa-2x mb-3"></i>
                                        <h5>No Media Found</h5>
                                        <p class="text-muted">All media has been deleted.</p>
                                    </div>
                                `);
                                        }
                                    });

                                    Swal.fire({
                                        toast: true,
                                        icon: 'success',
                                        title: `${mediaType.charAt(0).toUpperCase() + mediaType.slice(1)} deleted successfully`,
                                        position: 'top-end',
                                        showConfirmButton: false,
                                        timer: 2000
                                    });
                                } else {
                                    button.html('<i class="fas fa-times"></i>');
                                    button.prop('disabled', false);
                                    Swal.fire({
                                        toast: true,
                                        icon: 'error',
                                        title: `Failed to delete ${mediaType}`,
                                        position: 'top-end',
                                        showConfirmButton: false,
                                        timer: 3000
                                    });
                                }
                            },
                            error: function(xhr, status, error) {
                                console.error('Delete error:', error);
                                button.html('<i class="fas fa-times"></i>');
                                button.prop('disabled', false);
                                Swal.fire({
                                    toast: true,
                                    icon: 'error',
                                    title: `Error deleting ${mediaType}`,
                                    position: 'top-end',
                                    showConfirmButton: false,
                                    timer: 3000
                                });
                            }
                        });
                    }
                });
            });

            // Close modal when clicking on background image (for better UX)
            $(document).on('click', '.gallery-item img, .gallery-item video', function() {
                // You can add lightbox functionality here if needed
                console.log('Media clicked:', this.src);
            });
        });
    </script>
    @endsection

