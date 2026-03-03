@extends('admin.layouts.master')
@section('title')
    Ads Banner Management
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
</style>

@section('content')
    <div class="container-fluid py-4">
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <div>
                            <h3 class="page-title mb-0" style="color:black!important;">Ads Banner Management</h3>
                        </div>
                        <div>
                            <button type="button" class="btn btn-primary" data-bs-toggle="modal"
                                data-bs-target="#serviceBannerModal">
                                <i class="fas fa-plus me-2"></i> Create Ads Banner
                            </button>
                        </div>
                    </div>

                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover datatable">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>Image</th>
                                        <th>Start Date</th>
                                        <th>End Date</th>
                                        <th>Sort Order</th>
                                        <th>Created At</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($serviceBanners as $banner)
                                        <tr>
                                            <td>{{ $loop->iteration }}</td>
                                            <td>
                                                @if ($banner->image)
                                                    <img src="{{ asset($banner->image) }}" alt="Service Banner"
                                                        class="banner-image" data-bs-toggle="modal"
                                                        data-bs-target="#imagePreviewModal"
                                                        data-image-src="{{ asset($banner->image) }}"
                                                        data-image-type="Service Banner">
                                                @else
                                                    <div
                                                        class="banner-image bg-light d-flex align-items-center justify-content-center">
                                                        <i class="fas fa-image text-muted"></i>
                                                    </div>
                                                @endif
                                            </td>

                                            <td>{{ \Carbon\Carbon::parse($banner->start_date)->format('M d, Y') }}</td>
                                            <td>{{ \Carbon\Carbon::parse($banner->end_date)->format('M d, Y') }}</td>
                                            <td>
                                                <span class="badge bg-info">{{ $banner->sort }}</span>
                                            </td>
                                            <td>{{ $banner->created_at->format('M d, Y') }}</td>
                                            <td>
                                                <div class="d-flex gap-2 align-items-center">
                                                    <!-- Edit Button -->
                                                    <button type="button" class="btn btn-sm btn-warning edit-btn px-2"
                                                        data-bs-toggle="modal" data-bs-target="#serviceBannerModal"
                                                        data-id="{{ $banner->id }}" data-sort="{{ $banner->sort }}"
                                                        data-image="{{ $banner->image }}"
                                                        data-start-date="{{ $banner->start_date }}"
                                                        data-end-date="{{ $banner->end_date }}"
                                                        title="Edit Service Banner">
                                                        <i class="fas fa-edit"></i>
                                                    </button>

                                                    <!-- Delete Button -->
                                                    <form action="{{ route('admin.service-banner.destroy', $banner->id) }}"
                                                        method="POST" class="delete-form m-0">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="btn btn-sm btn-danger delete-btn px-2"
                                                            title="Delete Service Banner">
                                                            <i class="fas fa-trash"></i>
                                                        </button>
                                                    </form>
                                                </div>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="5" class="text-center py-4">
                                                <div class="text-muted">
                                                    <i class="fas fa-images fa-2x mb-3"></i>
                                                    <p>No Ads banners found. Create your first Ads banner!</p>
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

    <!-- Service Banner Modal -->
    <div class="modal fade" id="serviceBannerModal" tabindex="-1" aria-labelledby="serviceBannerModalLabel"
        aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="serviceBannerModalLabel" style="color:black!important;">Create Ads Banner
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="serviceBannerForm" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div id="formMethod"></div>

                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="image" class="form-label">Ads Banner Image <span
                                    class="text-danger">*</span></label>
                            <input type="file" class="form-control @error('image') is-invalid @enderror" id="image"
                                name="image" accept="image/*">
                            @error('image')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="text-muted">Max size: 2MB</small>

                            <!-- Image Preview -->
                            <div id="imagePreview" class="mt-2" style="display: none;">
                                <p class="small text-muted mb-1">Preview:</p>
                                <img id="imagePreviewImg" class="image-preview" src="" alt="Banner preview">
                            </div>

                            <!-- Current Image Display (for edit) -->
                            <div id="currentImage" class="mt-2" style="display: none;">
                                <p class="small text-muted mb-1">Current Image:</p>
                                <img id="currentImg" class="image-preview" src="" alt="Current banner image">
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="start_date" class="form-label">Start Date <span
                                            class="text-danger">*</span></label>
                                    <input type="date" class="form-control @error('start_date') is-invalid @enderror"
                                        id="start_date" name="start_date" value="{{ old('start_date') }}">
                                    @error('start_date')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="end_date" class="form-label">End Date <span
                                            class="text-danger">*</span></label>
                                    <input type="date" class="form-control @error('end_date') is-invalid @enderror"
                                        id="end_date" name="end_date" value="{{ old('end_date') }}">
                                    @error('end_date')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="sort" class="form-label">Sort Order</label>
                            <input type="number" class="form-control @error('sort') is-invalid @enderror" id="sort"
                                name="sort" value="{{ old('sort', 0) }}" min="0">
                            @error('sort')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="text-muted">Lower numbers appear first</small>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-primary" id="submitBtn">Create Ads Banner</button>
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
                    timerProgressBar: true,
                });
            @endif
            // Initialize DataTable
            $('.datatable').DataTable({
                responsive: true,
                columnDefs: [{
                    orderable: false,
                    targets: [1, 5]
                }, {
                    searchable: false,
                    targets: [1, 5]
                }],
                order: [
                    [3, 'asc'] // Sort by sort order
                ]
            });

            // Image preview functionality
            $('#image').on('change', function(e) {
                const file = e.target.files[0];
                if (file) {
                    const reader = new FileReader();
                    reader.onload = function(e) {
                        $('#imagePreviewImg').attr('src', e.target.result);
                        $('#imagePreview').show();
                    }
                    reader.readAsDataURL(file);
                } else {
                    $('#imagePreview').hide();
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

            // Modal reset when closed
            $('#serviceBannerModal').on('hidden.bs.modal', function() {
                resetModal();
            });

            // Edit button click
            $(document).on('click', '.edit-btn', function() {
                const id = $(this).data('id');
                const sort = $(this).data('sort');
                const image = $(this).data('image');
                const startDate = $(this).data('start-date');
                const endDate = $(this).data('end-date');

                $('#serviceBannerModalLabel').text('Edit Service Banner');
                $('#sort').val(sort);
                $('#start_date').val(startDate);
                $('#end_date').val(endDate);

                // Show current image if exists
                if (image) {
                    $('#currentImg').attr('src', "{{ asset('') }}" + image);
                    $('#currentImage').show();
                } else {
                    $('#currentImage').hide();
                }

                $('#imagePreview').hide();
                $('#formMethod').html('<input type="hidden" name="_method" value="PUT">');
                $('#serviceBannerForm').attr('action', "{{ route('admin.service-banner.update', ':id') }}"
                    .replace(':id', id));
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
            $('#serviceBannerForm').on('submit', function(e) {
                const isEdit = $('#serviceBannerModalLabel').text() === 'Edit Service Banner';
                const image = $('#image').val();
                const startDate = $('#start_date').val();
                const endDate = $('#end_date').val();

                let isValid = true;

                // For create, image is required
                if (!isEdit && !image) {
                    $('#image').addClass('is-invalid');
                    $('#image').siblings('.invalid-feedback').text('Banner image is required.');
                    isValid = false;
                }

                // Date validation
                if (!startDate) {
                    $('#start_date').addClass('is-invalid');
                    isValid = false;
                }

                if (!endDate) {
                    $('#end_date').addClass('is-invalid');
                    isValid = false;
                }

                if (startDate && endDate && startDate >= endDate) {
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
            $('#sort, #image, #start_date, #end_date').on('input change', function() {
                if ($(this).val().trim()) {
                    $(this).removeClass('is-invalid');
                }
            });

            function resetModal() {
                $('#serviceBannerModalLabel').text('Create New Service Banner');
                $('#serviceBannerForm').attr('action', "{{ route('admin.service-banner.store') }}");
                $('#formMethod').html('');
                $('#sort').val('');
                $('#image').val('');
                $('#start_date').val('');
                $('#end_date').val('');
                $('#imagePreview, #currentImage').hide();
                $('#submitBtn').text('Create Banner');
                $('#sort, #image, #start_date, #end_date').removeClass('is-invalid');
            }
        });
    </script>
@endsection
