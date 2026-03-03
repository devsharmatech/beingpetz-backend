@extends('admin.layouts.master')
@section('title')
    Banner Management
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

    .banner-image {
        width: 80px;
        height: 50px;
        object-fit: cover;
        border-radius: 4px;
        border: 1px solid #dee2e6;
        cursor: pointer;
        transition: transform 0.2s ease;
    }

    .banner-image:hover {
        transform: scale(1.05);
        border-color: #007bff;
    }

    .image-preview {
        max-width: 150px;
        max-height: 100px;
        margin-top: 10px;
        border-radius: 4px;
        border: 1px solid #dee2e6;
    }

    .btn-sm {
        width: 32px;
        height: 32px;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    /* Image Preview Modal Styles */
    .image-preview-modal .modal-dialog {
        max-width: 90%;
        max-height: 90vh;
    }

    .image-preview-modal .modal-content {
        background: transparent;
        border: none;
    }

    .image-preview-modal .modal-body {
        padding: 0;
        display: flex;
        justify-content: center;
        align-items: center;
        background: rgba(0, 0, 0, 0.8);
    }

    .image-preview-modal .preview-image {
        max-width: 100%;
        max-height: 80vh;
        object-fit: contain;
    }

    .image-preview-modal .btn-close {
        position: absolute;
        top: 20px;
        right: 20px;
        background: white;
        opacity: 1;
        z-index: 1055;
    }

    .image-info {
        position: absolute;
        bottom: 20px;
        left: 20px;
        background: rgba(0, 0, 0, 0.7);
        color: white;
        padding: 8px 12px;
        border-radius: 4px;
        font-size: 14px;
    }

    .status-badge {
        font-size: 0.75em;
        padding: 0.35em 0.65em;
    }

    .banner-status-active {
        background-color: #198754;
        color: white;
    }

    .banner-status-inactive {
        background-color: #6c757d;
        color: white;
    }

    .banner-status-expired {
        background-color: #dc3545;
        color: white;
    }

    .banner-status-upcoming {
        background-color: #ffc107;
        color: black;
    }
</style>

@section('content')
    <div class="container-fluid py-4">
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <div>
                            <h3 class="page-title mb-0" style="color:black!important;">Banner Management</h3>
                            <p class="text-muted mb-0">Banners automatically deactivate after end date</p>
                        </div>
                        <div>
                            <button type="button" class="btn btn-primary" data-bs-toggle="modal"
                                data-bs-target="#bannerModal">
                                <i class="fas fa-plus me-2"></i> Create New Banner
                            </button>
                        </div>
                    </div>

                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover datatable">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>Mobile Image</th>
                                        <th>Desktop Image</th>
                                        <th>Link</th>
                                        <th>Sort Order</th>
                                        <th>Start Date</th>
                                        <th>End Date</th>
                                        <th>Status</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($banners as $banner)
                                        @php
                                            $status = $banner->is_currently_active
                                                ? 'active'
                                                : (!$banner->is_active
                                                    ? 'inactive'
                                                    : ($banner->end_date && $banner->end_date->lt(now())
                                                        ? 'expired'
                                                        : ($banner->start_date && $banner->start_date->gt(now())
                                                            ? 'upcoming'
                                                            : 'inactive')));
                                        @endphp
                                        <tr>
                                            <td>{{ $loop->iteration }}</td>
                                            <td>
                                                @if ($banner->mobile_image)
                                                    <img src="{{ asset($banner->mobile_image) }}" alt="Mobile Banner"
                                                        class="banner-image" data-bs-toggle="modal"
                                                        data-bs-target="#imagePreviewModal"
                                                        data-image-src="{{ asset($banner->mobile_image) }}"
                                                        data-image-type="Mobile Banner">
                                                @else
                                                    <div
                                                        class="banner-image bg-light d-flex align-items-center justify-content-center">
                                                        <i class="fas fa-mobile-alt text-muted"></i>
                                                    </div>
                                                @endif
                                            </td>
                                            <td>
                                                @if ($banner->desktop_image)
                                                    <img src="{{ asset($banner->desktop_image) }}" alt="Desktop Banner"
                                                        class="banner-image" data-bs-toggle="modal"
                                                        data-bs-target="#imagePreviewModal"
                                                        data-image-src="{{ asset($banner->desktop_image) }}"
                                                        data-image-type="Desktop Banner">
                                                @else
                                                    <div
                                                        class="banner-image bg-light d-flex align-items-center justify-content-center">
                                                        <i class="fas fa-desktop text-muted"></i>
                                                    </div>
                                                @endif
                                            </td>
                                            <td>
                                                @if ($banner->link)
                                                    <a href="{{ $banner->link }}" target="_blank" class="text-truncate"
                                                        style="max-width: 150px; display: inline-block;">
                                                        {{ $banner->link }}
                                                    </a>
                                                @else
                                                    <span class="text-muted">No Link</span>
                                                @endif
                                            </td>
                                            <td>
                                                <span class="badge bg-info">{{ $banner->sort }}</span>
                                            </td>
                                            <td>
                                                {{ $banner->start_date ? $banner->start_date->format('M d, Y') : 'Immediate' }}
                                            </td>
                                            <td>
                                                {{ $banner->end_date ? $banner->end_date->format('M d, Y') : 'No End Date' }}
                                            </td>
                                            <td>
                                                @if ($status === 'active')
                                                    <span class="badge banner-status-active status-badge">Active</span>
                                                @elseif($status === 'inactive')
                                                    <span class="badge banner-status-inactive status-badge">Inactive</span>
                                                @elseif($status === 'expired')
                                                    <span class="badge banner-status-expired status-badge">Expired</span>
                                                @elseif($status === 'upcoming')
                                                    <span class="badge banner-status-upcoming status-badge">Upcoming</span>
                                                @endif
                                            </td>
                                            <td>
                                                <div class="d-flex gap-2 align-items-center">
                                                    <!-- Edit Button -->
                                                    <button type="button" class="btn btn-sm btn-warning edit-btn px-2"
                                                        data-bs-toggle="modal" data-bs-target="#bannerModal"
                                                        data-id="{{ $banner->id }}" data-link="{{ $banner->link }}"
                                                        data-sort="{{ $banner->sort }}"
                                                        data-start_date="{{ $banner->start_date ? $banner->start_date->format('Y-m-d') : '' }}"
                                                        data-end_date="{{ $banner->end_date ? $banner->end_date->format('Y-m-d') : '' }}"
                                                        data-is_active="{{ $banner->is_active }}"
                                                        data-mobile_image="{{ $banner->mobile_image }}"
                                                        data-desktop_image="{{ $banner->desktop_image }}"
                                                        title="Edit Banner">
                                                        <i class="fas fa-edit"></i>
                                                    </button>

                                                    <!-- Delete Button -->
                                                    <form action="{{ route('admin.banner.destroy', $banner->id) }}"
                                                        method="POST" class="delete-form m-0">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="btn btn-sm btn-danger delete-btn px-2"
                                                            title="Delete Banner">
                                                            <i class="fas fa-trash"></i>
                                                        </button>
                                                    </form>
                                                </div>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="9" class="text-center py-4">
                                                <div class="text-muted">
                                                    <i class="fas fa-images fa-2x mb-3"></i>
                                                    <p>No banners found. Create your first banner!</p>
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

    <!-- Banner Modal -->
    <div class="modal fade" id="bannerModal" tabindex="-1" aria-labelledby="bannerModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="bannerModalLabel" style="color:black!important;">Create New Banner</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="bannerForm" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div id="formMethod"></div>

                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="link" class="form-label">Banner Link</label>
                                    <input type="url" class="form-control @error('link') is-invalid @enderror"
                                        id="link" name="link" value="{{ old('link') }}"
                                        placeholder="https://example.com">
                                    @error('link')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="mb-3">
                                    <label for="start_date" class="form-label">Start Date</label>
                                    <input type="date" class="form-control @error('start_date') is-invalid @enderror"
                                        id="start_date" name="start_date" value="{{ old('start_date') }}"
                                        min="{{ date('Y-m-d') }}">
                                    @error('start_date')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    <small class="text-muted">Leave empty for immediate start</small>
                                </div>

                                <div class="mb-3">
                                    <label for="mobile_image" class="form-label">Mobile Image <span
                                            class="text-danger">*</span></label>
                                    <input type="file" class="form-control @error('mobile_image') is-invalid @enderror"
                                        id="mobile_image" name="mobile_image" accept="image/*">
                                    @error('mobile_image')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    <small class="text-muted">Max size: 2MB</small>

                                    <!-- Mobile Image Preview -->
                                    <div id="mobilePreview" class="mt-2" style="display: none;">
                                        <p class="small text-muted mb-1">Mobile Preview:</p>
                                        <img id="mobilePreviewImg" class="image-preview" src=""
                                            alt="Mobile preview">
                                    </div>

                                    <!-- Current Mobile Image Display (for edit) -->
                                    <div id="currentMobileImage" class="mt-2" style="display: none;">
                                        <p class="small text-muted mb-1">Current Mobile Image:</p>
                                        <img id="currentMobileImg" class="image-preview" src=""
                                            alt="Current mobile image">
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="sort" class="form-label">Sort Order</label>
                                    <input type="number" class="form-control @error('sort') is-invalid @enderror"
                                        id="sort" name="sort" value="{{ old('sort', 0) }}" min="0">
                                    @error('sort')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="mb-3">
                                    <label for="end_date" class="form-label">End Date</label>
                                    <input type="date" class="form-control @error('end_date') is-invalid @enderror"
                                        id="end_date" name="end_date" value="{{ old('end_date') }}"
                                        min="{{ date('Y-m-d') }}">
                                    @error('end_date')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    <small class="text-muted">Leave empty for no expiration</small>
                                </div>

                                <div class="mb-3">
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" id="is_active" name="is_active"
                                            value="1" checked>
                                        <label class="form-check-label" for="is_active">Active Banner</label>
                                    </div>
                                    <small class="text-muted">Banner will only show if active and within date range</small>
                                </div>

                                <div class="mb-3">
                                    <label for="desktop_image" class="form-label">Desktop Image <span
                                            class="text-danger">*</span></label>
                                    <input type="file"
                                        class="form-control @error('desktop_image') is-invalid @enderror"
                                        id="desktop_image" name="desktop_image" accept="image/*">
                                    @error('desktop_image')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    <small class="text-muted">Max size: 2MB</small>

                                    <!-- Desktop Image Preview -->
                                    <div id="desktopPreview" class="mt-2" style="display: none;">
                                        <p class="small text-muted mb-1">Desktop Preview:</p>
                                        <img id="desktopPreviewImg" class="image-preview" src=""
                                            alt="Desktop preview">
                                    </div>

                                    <!-- Current Desktop Image Display (for edit) -->
                                    <div id="currentDesktopImage" class="mt-2" style="display: none;">
                                        <p class="small text-muted mb-1">Current Desktop Image:</p>
                                        <img id="currentDesktopImg" class="image-preview" src=""
                                            alt="Current desktop image">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-primary" id="submitBtn">Create Banner</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Image Preview Modal -->
    <div class="modal fade image-preview-modal" id="imagePreviewModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                <div class="modal-body">
                    <img src="" class="preview-image" alt="Preview">
                    <div class="image-info"></div>
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
                    targets: [1, 2, 8]
                }, {
                    searchable: false,
                    targets: [1, 2, 8]
                }],
                order: [
                    [4, 'asc'] // Sort by sort order
                ]
            });

            // Set end date min based on start date
            $('#start_date').on('change', function() {
                const startDate = $(this).val();
                if (startDate) {
                    $('#end_date').attr('min', startDate);
                }
            });

            // Image preview functionality for form
            $('#mobile_image').on('change', function(e) {
                const file = e.target.files[0];
                if (file) {
                    // Check file size (2MB limit)
                    if (file.size > 2 * 1024 * 1024) {
                        alert('Mobile image must be less than 2MB');
                        $(this).val('');
                        return;
                    }

                    const reader = new FileReader();
                    reader.onload = function(e) {
                        $('#mobilePreviewImg').attr('src', e.target.result);
                        $('#mobilePreview').show();
                    }
                    reader.readAsDataURL(file);
                } else {
                    $('#mobilePreview').hide();
                }
            });

            $('#desktop_image').on('change', function(e) {
                const file = e.target.files[0];
                if (file) {
                    // Check file size (2MB limit)
                    if (file.size > 2 * 1024 * 1024) {
                        alert('Desktop image must be less than 2MB');
                        $(this).val('');
                        return;
                    }

                    const reader = new FileReader();
                    reader.onload = function(e) {
                        $('#desktopPreviewImg').attr('src', e.target.result);
                        $('#desktopPreview').show();
                    }
                    reader.readAsDataURL(file);
                } else {
                    $('#desktopPreview').hide();
                }
            });

            // Image preview modal functionality
            $('#imagePreviewModal').on('show.bs.modal', function(event) {
                const button = $(event.relatedTarget);
                const imageSrc = button.data('image-src');
                const imageType = button.data('image-type');

                const modal = $(this);
                modal.find('.preview-image').attr('src', imageSrc);
                modal.find('.image-info').text(imageType);
            });

            // Close modal on background click
            $('#imagePreviewModal').on('click', function(e) {
                if (e.target === this) {
                    $(this).modal('hide');
                }
            });

            // Keyboard navigation for image preview
            $(document).on('keydown', function(e) {
                if (e.key === 'Escape') {
                    $('#imagePreviewModal').modal('hide');
                }
            });

            // Modal reset when closed
            $('#bannerModal').on('hidden.bs.modal', function() {
                resetModal();
            });

            // Edit button click
            $(document).on('click', '.edit-btn', function() {
                const id = $(this).data('id');
                const link = $(this).data('link');
                const sort = $(this).data('sort');
                const start_date = $(this).data('start_date');
                const end_date = $(this).data('end_date');
                const is_active = $(this).data('is_active');
                const mobile_image = $(this).data('mobile_image');
                const desktop_image = $(this).data('desktop_image');

                $('#bannerModalLabel').text('Edit Banner');
                $('#link').val(link);
                $('#sort').val(sort);
                $('#start_date').val(start_date);
                $('#end_date').val(end_date);
                $('#is_active').prop('checked', is_active);

                // Show current images if they exist
                if (mobile_image) {
                    $('#currentMobileImg').attr('src', "{{ asset('') }}" + mobile_image);
                    $('#currentMobileImage').show();
                } else {
                    $('#currentMobileImage').hide();
                }

                if (desktop_image) {
                    $('#currentDesktopImg').attr('src', "{{ asset('') }}" + desktop_image);
                    $('#currentDesktopImage').show();
                } else {
                    $('#currentDesktopImage').hide();
                }

                $('#mobilePreview').hide();
                $('#desktopPreview').hide();
                $('#formMethod').html('<input type="hidden" name="_method" value="PUT">');
                $('#bannerForm').attr('action', "{{ route('admin.banner.update', ':id') }}".replace(':id',
                    id));
                $('#submitBtn').text('Update Banner');
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
                    confirmButtonText: 'Yes, delete it!'
                }).then((result) => {
                    if (result.isConfirmed) {
                        form.submit();
                    }
                });
            });

            // Form validation
            $('#bannerForm').on('submit', function(e) {
                const isEdit = $('#bannerModalLabel').text() === 'Edit Banner';
                const mobileImage = $('#mobile_image').val();
                const desktopImage = $('#desktop_image').val();

                let isValid = true;

                // For create, both images are required
                if (!isEdit) {
                    if (!mobileImage) {
                        $('#mobile_image').addClass('is-invalid');
                        $('#mobile_image').siblings('.invalid-feedback').text('Mobile image is required.');
                        isValid = false;
                    }

                    if (!desktopImage) {
                        $('#desktop_image').addClass('is-invalid');
                        $('#desktop_image').siblings('.invalid-feedback').text(
                            'Desktop image is required.');
                        isValid = false;
                    }
                }

                // Validate date range
                const startDate = $('#start_date').val();
                const endDate = $('#end_date').val();

                if (startDate && endDate && startDate > endDate) {
                    $('#end_date').addClass('is-invalid');
                    $('#end_date').siblings('.invalid-feedback').text('End date must be after start date.');
                    isValid = false;
                }

                if (!isValid) {
                    e.preventDefault();
                    return false;
                }

                return true;
            });

            // Remove validation on input
            $('#link, #sort, #mobile_image, #desktop_image, #start_date, #end_date').on('input change', function() {
                if ($(this).val().trim()) {
                    $(this).removeClass('is-invalid');
                }
            });

            function resetModal() {
                $('#bannerModalLabel').text('Create New Banner');
                $('#bannerForm').attr('action', "{{ route('admin.banner.store') }}");
                $('#formMethod').html('');
                $('#link, #sort, #start_date, #end_date').val('');
                $('#mobile_image, #desktop_image').val('');
                $('#is_active').prop('checked', true);
                $('#mobilePreview, #desktopPreview, #currentMobileImage, #currentDesktopImage').hide();
                $('#submitBtn').text('Create Banner');
                $('input').removeClass('is-invalid');

                // Reset date min values
                $('#start_date').attr('min', "{{ date('Y-m-d') }}");
                $('#end_date').attr('min', "{{ date('Y-m-d') }}");
            }
        });
    </script>
@endsection
