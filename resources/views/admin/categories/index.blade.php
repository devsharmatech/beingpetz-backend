@extends('admin.layouts.master')
@section('title')
    Category Management
@endsection


<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<link href="{{ URL::asset('build/libs/admin-resources/jquery.vectormap/jquery-jvectormap-1.2.2.css') }}" rel="stylesheet"
    type="text/css" />
<link href="{{ URL::asset('build/libs/datatables.net-bs4/css/dataTables.bootstrap4.min.css') }}" rel="stylesheet">
<link rel="icon" type="image/x-icon" href="/favicon.ico">
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
                            <h3 class="page-title mb-0" style="color:black!important;">Category Management</h3>
                        </div>
                        <div>
                            <button type="button" class="btn btn-primary" data-bs-toggle="modal"
                                data-bs-target="#categoryModal">
                                <i class="fas fa-plus me-2"></i> Create New Category
                            </button>
                        </div>
                    </div>

                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover datatable">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>Name</th>
                                        <th>Type</th>
                                        <th>Created At</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($categories as $category)
                                        <tr>
                                            <td>{{ $loop->iteration }}</td>
                                            <td>{{ $category->name }}</td>
                                            <td>
                                                <span
                                                    class="badge bg-{{ $category->type == 'blog' ? 'primary' : 'success' }}">
                                                    {{ ucfirst($category->type) }}
                                                </span>
                                            </td>
                                            <td>{{ $category->created_at->format('M d, Y') }}</td>
                                            <td>
                                                <div class="d-flex gap-2">
                                                    <button type="button" class="btn btn-sm btn-warning edit-btn"
                                                        data-bs-toggle="modal" data-bs-target="#categoryModal"
                                                        data-id="{{ $category->id }}" data-name="{{ $category->name }}"
                                                        data-type="{{ $category->type }}" style="height: 24px!important;">
                                                        <i class="fas fa-edit"></i>
                                                    </button>
                                                    <form action="{{ route('admin.categories.destroy', $category->id) }}"
                                                        method="POST" class="delete-form">
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
                                            <td colspan="5" class="text-center py-4">
                                                <div class="text-muted">
                                                    <i class="fas fa-inbox fa-2x mb-3"></i>
                                                    <p>No categories found. Create your first category!</p>
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

    <!-- Category Modal -->
    <div class="modal fade" id="categoryModal" tabindex="-1" aria-labelledby="categoryModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="categoryModalLabel" style="color:black!important;">Create Category</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="categoryForm" method="POST">
                    @csrf
                    <div id="formMethod"></div>

                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="name" class="form-label">Category Name <span
                                    class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('name') is-invalid @enderror" id="name"
                                name="name" value="{{ old('name') }}" required>
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="type" class="form-label">Category Type <span
                                    class="text-danger">*</span></label>
                            <select class="form-control @error('type') is-invalid @enderror" id="type" name="type"
                                required>
                                <option value="">Select Type</option>
                                <option value="blog" {{ old('type') == 'blog' ? 'selected' : '' }}>Blog</option>
                                <option value="event" {{ old('type') == 'event' ? 'selected' : '' }}>Event</option>
                            </select>
                            @error('type')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-primary" id="submitBtn">Create Category</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
<script async src="https://www.googletagmanager.com/gtag/js?id=G-9Q6H0QETRF"></script>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="{{ URL::asset('build/libs/datatables.net/js/jquery.dataTables.min.js') }}"></script>
    <script src="{{ URL::asset('build/libs/datatables.net-bs4/js/dataTables.bootstrap4.min.js') }}"></script>
    <script>
      window.dataLayer = window.dataLayer || [];
      function gtag(){dataLayer.push(arguments);}
      gtag('js', new Date());
      gtag('config', 'G-9Q6H0QETRF');
    </script>

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
                    targets: [4]
                }, {
                    searchable: false,
                    targets: [4]
                }],
                order: [
                    [0, 'asc']
                ]
            });

            // Modal reset when closed
            $('#categoryModal').on('hidden.bs.modal', function() {
                resetModal();
            });

           
            // Edit button click
            $('.edit-btn').on('click', function() {
                const id = $(this).data('id');
                const name = $(this).data('name');
                const type = $(this).data('type');
            
                // Modal title
                $('#categoryModalLabel').text('Edit Category');
            
                // Fill form fields
                $('#name').val(name);
                $('#type').val(type);
            
                // Add hidden method field for PUT
                $('#formMethod').html('<input type="hidden" name="_method" value="PUT">');
            
                // Set form action to update route
                $('#categoryForm').attr('action', `/petz-info/public/admin/categories/${id}`);
            
                // Change button text
                $('#submitBtn').text('Update Category');
            
                // Show modal
                $('#categoryModal').modal('show');
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

            // Form validation
            $('#categoryForm').on('submit', function(e) {
                const name = $('#name').val().trim();
                const type = $('#type').val();

                let isValid = true;

                if (!name) {
                    $('#name').addClass('is-invalid');
                    $('#name').siblings('.invalid-feedback').text('Category name is required.');
                    isValid = false;
                }

                if (!type) {
                    $('#type').addClass('is-invalid');
                    $('#type').siblings('.invalid-feedback').text('Category type is required.');
                    isValid = false;
                }

                if (!isValid) {
                    e.preventDefault();
                    return false;
                }

                return true;
            });

            // Remove validation on input
            $('#name, #type').on('input change', function() {
                if ($(this).val().trim()) {
                    $(this).removeClass('is-invalid');
                }
            });

            function resetModal() {
                $('#categoryModalLabel').text('Create Category');
                $('#categoryForm').attr('action', "{{ route('admin.categories.store') }}");
                $('#formMethod').html('');
                $('#name').val('');
                $('#type').val('');
                $('#submitBtn').text('Create Category');
                $('#name, #type').removeClass('is-invalid');
            }
        });
    </script>
@endsection
