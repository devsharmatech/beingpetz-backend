@extends('admin.layouts.master')
@section('title')
    Pet Management
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

    .pet-avatar {
        width: 50px;
        height: 50px;
        border-radius: 50%;
        object-fit: cover;
    }

    .export-buttons {
        margin-bottom: 1rem;
    }
</style>

@section('content')
    <div class="container-fluid py-4">
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <div>
                            <h3 class="page-title mb-0" style="color:black!important;">Pet Management</h3>
                        </div>
                        <div class="d-flex gap-2">
                            <!-- Export CSV Button -->
                            <form action="{{ route('admin.pets.export') }}" method="GET" class="d-inline">
                                <button type="submit" class="btn btn-success">
                                    <i class="fas fa-file-export me-2"></i> Export CSV
                                </button>
                            </form>
                            {{-- <button type="button" class="btn btn-primary" data-bs-toggle="modal"
                                data-bs-target="#petModal">
                                <i class="fas fa-plus me-2"></i> Add New Pet
                            </button> --}}
                        </div>
                    </div>

                    <div class="card-body">
                        <!-- Export Buttons -->
                        <div class="export-buttons">
                            <div class="btn-group" role="group" style="gap: 11px;">
                                <form action="{{ route('admin.pets.export') }}" method="GET" class="d-inline">
                                    <input type="hidden" name="type" value="all">
                                    <button type="submit" class="btn btn-success btn-sm">
                                        <i class="fas fa-file-csv me-1"></i> Export All Data
                                    </button>
                                </form>
                                <form action="{{ route('admin.pets.export') }}" method="GET" class="d-inline">
                                    <input type="hidden" name="type" value="current_page">
                                    <button type="submit" class="btn btn-outline btn-primary btn-sm">
                                        <i class="fas fa-table me-1"></i> Export Current Page
                                    </button>
                                </form>
                            </div>
                        </div>

                        <div class="table-responsive">
                            <table class="table table-hover datatable">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>Avatar</th>
                                        <th>Name</th>
                                        <th>Type</th>
                                        <th>Breed</th>
                                        <th>Gender</th>
                                        <th>DOB</th>
                                        <th>Age</th>
                                        <th>Owner</th>
                                        <th>Owner Phone</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($pets as $pet)
                                        <tr>
                                            <td>{{ $loop->iteration }}</td>
                                            <td>
                                                @if ($pet->avatar)
                                                    <img src="{{ asset($pet->avatar) }}" alt="{{ $pet->name }}"
                                                        class="pet-avatar">
                                                @else
                                                    <div
                                                        class="pet-avatar bg-light d-flex align-items-center justify-content-center">
                                                        <i class="fas fa-paw text-muted"></i>
                                                    </div>
                                                @endif
                                            </td>
                                            <td>{{ $pet->name }}</td>
                                            <td>{{ $pet->type }}</td>
                                            <td>{{ $pet->breed ?? 'N/A' }}</td>
                                            <td>{{ ucfirst($pet->gender) }} </td>
                                            <td>{{ \Carbon\Carbon::parse($pet->dob)->format('d M y') }}</td>

                                            <td>
                                                @if ($pet->dob)
                                                    {{ \Carbon\Carbon::parse($pet->dob)->age }} years
                                                @else
                                                    N/A
                                                @endif
                                            </td>

                                            <td>{{ $pet->user->first_name }}</td>
                                            <td>{{ $pet->user->phone }}</td>
                                            <td>
                                                <div class="d-flex gap-2">
                                                    <button type="button" class="btn btn-sm btn-warning edit-btn"
                                                        data-bs-toggle="modal" data-bs-target="#petModal"
                                                        data-id="{{ $pet->id }}" data-name="{{ $pet->name }}"
                                                        data-gender="{{ $pet->gender }}" data-type="{{ $pet->type }}"
                                                        data-breed="{{ $pet->breed }}" data-dob="{{ $pet->dob ?? '' }}"
                                                        data-bio="{{ $pet->bio }}" style="height: 24px!important;">
                                                        <i class="fas fa-edit"></i>
                                                    </button>
                                                    <form action="{{ route('admin.pets.delete', $pet->id) }}"
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
                                            <td colspan="11" class="text-center py-4">
                                                <div class="text-muted">
                                                    <i class="fas fa-paw fa-2x mb-3"></i>
                                                    <p>No pets found.</p>
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

    <!-- Pet Modal -->
    <div class="modal fade" id="petModal" tabindex="-1" aria-labelledby="petModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="petModalLabel" style="color:black!important;">Add New Pet</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="petForm" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div id="formMethod"></div>

                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="name" class="form-label">Pet Name <span
                                            class="text-danger">*</span></label>
                                    <input type="text" class="form-control @error('name') is-invalid @enderror"
                                        id="name" name="name" value="{{ old('name') }}" required>
                                    @error('name')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="mb-3">
                                    <label for="gender" class="form-label">Gender <span
                                            class="text-danger">*</span></label>
                                    <select class="form-control @error('gender') is-invalid @enderror" id="gender"
                                        name="gender" required>
                                        <option value="">Select Gender</option>
                                        <option value="male" {{ old('gender') == 'male' ? 'selected' : '' }}>Male
                                        </option>
                                        <option value="female" {{ old('gender') == 'female' ? 'selected' : '' }}>Female
                                        </option>
                                        <option value="unknown" {{ old('gender') == 'unknown' ? 'selected' : '' }}>Unknown
                                        </option>
                                    </select>
                                    @error('gender')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="mb-3">
                                    <label for="type" class="form-label">Pet Type <span
                                            class="text-danger">*</span></label>
                                    <input type="text" class="form-control @error('type') is-invalid @enderror"
                                        id="type" name="type" value="{{ old('type') }}" required
                                        placeholder="e.g., Dog, Cat, Bird">
                                    @error('type')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="breed" class="form-label">Breed</label>
                                    <input type="text" class="form-control @error('breed') is-invalid @enderror"
                                        id="breed" name="breed" value="{{ old('breed') }}"
                                        placeholder="e.g., Labrador, Persian">
                                    @error('breed')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="mb-3">
                                    <label for="dob" class="form-label">Date of Birth</label>
                                    <input type="date" class="form-control @error('dob') is-invalid @enderror"
                                        id="dob" name="dob" value="{{ old('dob') }}">
                                    @error('dob')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="mb-3">
                                    <label for="avatar" class="form-label">Pet Photo</label>
                                    <input type="file" class="form-control @error('avatar') is-invalid @enderror"
                                        id="avatar" name="avatar" accept="image/*">
                                    @error('avatar')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-12">
                                <div class="mb-3">
                                    <label for="bio" class="form-label">Bio</label>
                                    <textarea class="form-control @error('bio') is-invalid @enderror" id="bio" name="bio" rows="3"
                                        placeholder="Tell us about this pet...">{{ old('bio') }}</textarea>
                                    @error('bio')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-primary" id="submitBtn">Add Pet</button>
                    </div>
                </form>
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
            var table = $('.datatable').DataTable({
                responsive: true,
                columnDefs: [{
                    orderable: false,
                    targets: [1, 10] // Avatar and Actions columns
                }, {
                    searchable: false,
                    targets: [1, 10] // Avatar and Actions columns
                }],
                order: [
                    [0, 'asc']
                ],
                dom: 'Bfrtip',
                buttons: [{
                    extend: 'csv',
                    text: '<i class="fas fa-file-csv me-1"></i> Export CSV',
                    className: 'btn btn-success btn-sm',
                    exportOptions: {
                        columns: [0, 2, 3, 4, 5, 6, 7, 8,
                            9
                        ] // Export all columns except avatar and actions
                    }
                }]
            });

            // Modal reset when closed
            $('#petModal').on('hidden.bs.modal', function() {
                resetModal();
            });

            // Edit button click
            $('.edit-btn').on('click', function() {
                const id = $(this).data('id');
                const name = $(this).data('name');
                const gender = $(this).data('gender');
                const type = $(this).data('type');
                const breed = $(this).data('breed');
                const dob = $(this).data('dob');
                const bio = $(this).data('bio');

                $('#petModalLabel').text('Edit Pet');
                $('#name').val(name);
                $('#gender').val(gender);
                $('#type').val(type);
                $('#breed').val(breed);
                $('#dob').val(dob);
                $('#bio').val(bio);
                $('#formMethod').html('<input type="hidden" name="_method" value="PUT">');
                $('#petForm').attr('action', `/petz-info/public/admin/pets/${id}`);
                $('#submitBtn').text('Update Pet');
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
            $('#petForm').on('submit', function(e) {
                const name = $('#name').val().trim();
                const gender = $('#gender').val();
                const type = $('#type').val().trim();

                let isValid = true;

                if (!name) {
                    $('#name').addClass('is-invalid');
                    $('#name').siblings('.invalid-feedback').text('Pet name is required.');
                    isValid = false;
                }

                if (!gender) {
                    $('#gender').addClass('is-invalid');
                    $('#gender').siblings('.invalid-feedback').text('Gender selection is required.');
                    isValid = false;
                }

                if (!type) {
                    $('#type').addClass('is-invalid');
                    $('#type').siblings('.invalid-feedback').text('Pet type is required.');
                    isValid = false;
                }

                if (!isValid) {
                    e.preventDefault();
                    return false;
                }

                return true;
            });

            // Remove validation on input
            $('#name, #gender, #type, #breed, #dob, #bio, #avatar').on('input change', function() {
                if ($(this).val().trim()) {
                    $(this).removeClass('is-invalid');
                }
            });

            function resetModal() {
                $('#petModalLabel').text('Add New Pet');
                $('#petForm').attr('action', "{{ route('admin.pets.save') }}");
                $('#formMethod').html('');
                $('#name, #gender, #type, #breed, #dob, #bio').val('');
                $('#avatar').val('');
                $('#submitBtn').text('Add Pet');
                $('#name, #gender, #type, #breed, #dob, #bio, #avatar').removeClass('is-invalid');
            }
        });
    </script>
@endsection
