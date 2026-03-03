@extends('admin.layouts.master')
@section('title')
    Parents Management
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

    .parent-avatar {
        width: 50px;
        height: 50px;
        border-radius: 50%;
        object-fit: cover;
    }

    /* Filter Styles */
    .filter-section {
        background: #f8f9fa;
        border-radius: 10px;
        padding: 20px;
        margin-bottom: 20px;
    }

    .filter-badge {
        font-size: 0.8rem;
        padding: 5px 10px;
    }

    .active-filter {
        background: #8337b6 !important;
        color: white !important;
    }
</style>

@section('content')
    <div class="container-fluid py-4">
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <div>
                            <h3 class="page-title mb-0" style="color:black!important;">Parents Management</h3>
                            @if (request('filter'))
                                <small class="text-muted">
                                    Showing
                                    @if (request('filter') == 'daily')
                                        Daily Active
                                    @elseif(request('filter') == 'weekly')
                                        Weekly Active
                                    @elseif(request('filter') == 'monthly')
                                        Monthly Active
                                    @else
                                        All
                                    @endif Users
                                </small>
                            @endif
                        </div>
                        <div>
                            {{-- Export CSV Button --}}
                            <a href="{{ route('admin.parents.export.csv', request()->query()) }}"
                                class="btn btn-success me-2" id="exportBtn">
                                <i class="fas fa-download me-2"></i> Export CSV
                            </a>

                            {{-- <button type="button" class="btn btn-primary" data-bs-toggle="modal"
            data-bs-target="#parentModal">
            <i class="fas fa-plus me-2"></i> Add New Parent
        </button> --}}
                        </div>
                    </div>

                    <!-- Filter Section -->
                    <div class="card-body border-bottom">
                        <div class="row align-items-center">
                            <div class="col-md-6">
                                <h6 class="mb-0">Filter by Activity:</h6>
                            </div>
                            <div class="col-md-6 text-end">
                                <div class="btn-group" role="group">
                                    <a href="{{ route('admin.parents.index') }}"
                                        class="btn btn-outline-primary {{ !request('filter') ? 'active-filter' : '' }}">
                                        All Users
                                        <span class="badge filter-badge ms-1">{{ $totalUsersCount }}</span>
                                    </a>
                                    <a href="{{ route('admin.parents.index', ['filter' => 'daily']) }}"
                                        class="btn btn-outline-primary {{ request('filter') == 'daily' ? 'active-filter' : '' }}">
                                        Daily Active
                                        <span class="badge filter-badge ms-1">{{ $dailyActiveCount }}</span>
                                    </a>
                                    <a href="{{ route('admin.parents.index', ['filter' => 'weekly']) }}"
                                        class="btn btn-outline-primary {{ request('filter') == 'weekly' ? 'active-filter' : '' }}">
                                        Weekly Active
                                        <span class="badge filter-badge ms-1">{{ $weeklyActiveCount }}</span>
                                    </a>
                                    <a href="{{ route('admin.parents.index', ['filter' => 'monthly']) }}"
                                        class="btn btn-outline-primary {{ request('filter') == 'monthly' ? 'active-filter' : '' }}">
                                        Monthly Active
                                        <span class="badge filter-badge ms-1">{{ $monthlyActiveCount }}</span>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover datatable">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>Profile</th>
                                        <th>Full Name</th>
                                        <th>Email</th>
                                        <th>Phone</th>
                                        <th>Address</th>
                                        <th>Pets</th>
                                        <th>Last Active</th>
                                        <th>Created Date</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($parents as $parent)
                                        <tr>
                                            <td>{{ $loop->iteration }}</td>
                                            <td>
                                                @if ($parent->profile)
                                                    <img src="{{ asset($parent->profile) }}"
                                                        alt="{{ $parent->first_name }}" class="parent-avatar">
                                                @else
                                                    <div
                                                        class="parent-avatar bg-light d-flex align-items-center justify-content-center">
                                                        <i class="fas fa-user text-muted"></i>
                                                    </div>
                                                @endif
                                            </td>
                                            <td>{{ $parent->first_name }} {{ $parent->last_name ?? 'N/A' }}</td>

                                            <td>{{ $parent->email }}</td>
                                            <td>{{ $parent->phone ?? 'N/A' }}</td>
                                            <td>
                                                <span class="address-short" data-bs-toggle="tooltip" data-bs-placement="top"
                                                    title="{{ $parent->locality ?? 'N/A' }}, {{ $parent->city ?? 'N/A' }}, {{ $parent->state ?? 'N/A' }}">
                                                    {{ Str::limit($parent->locality, 15) ?? 'N/A' }} -
                                                    {{ Str::limit($parent->city, 10) ?? 'N/A' }},
                                                    {{ Str::limit($parent->state, 10) ?? 'N/A' }}
                                                </span>
                                            </td>

                                            <td>
                                                @if ($parent->pets_count > 0)
                                                    <span class="badge bg-success">{{ $parent->pets_count }} Pets</span>
                                                @else
                                                    <span class="badge bg-secondary">No Pets</span>
                                                @endif
                                            </td>
                                            <td>
                                                @php
                                                    $lastActive = $parent->last_active_at ?? $parent->last_login;
                                                @endphp
                                                @if ($lastActive)
                                                    <span
                                                        class="badge 
                                                        @if ($lastActive->gt(now()->subDay())) bg-success
                                                        @elseif($lastActive->gt(now()->subWeek())) bg-warning
                                                        @elseif($lastActive->gt(now()->subMonth())) bg-info
                                                        @else bg-secondary @endif"
                                                        data-bs-toggle="tooltip" 
                                                        data-bs-placement="top" 
                                                        title="{{ $parent->last_activity_details ?? 'Logged in' }}">
                                                        {{ $lastActive->diffForHumans() }}
                                                    </span>
                                                @else
                                                    <span class="badge bg-secondary">Never Active</span>
                                                @endif
                                            </td>
                                            <td>{{ $parent->created_at->format('M d, Y') }}</td>
                                            <td>
                                                <div class="d-flex gap-2">
                                                    <button type="button" class="btn btn-sm btn-warning edit-btn"
                                                        data-bs-toggle="modal" data-bs-target="#parentModal"
                                                        data-id="{{ $parent->id }}"
                                                        data-first_name="{{ $parent->first_name }}"
                                                        data-last_name="{{ $parent->last_name }}"
                                                        data-email="{{ $parent->email }}"
                                                        data-phone="{{ $parent->phone }}"
                                                        data-locality="{{ $parent->locality }}"
                                                        data-city="{{ $parent->city }}" data-state="{{ $parent->state }}"
                                                        style="height: 24px!important;">
                                                        <i class="fas fa-edit"></i>
                                                    </button>
                                                    <form action="{{ route('admin.parents.destroy', $parent->id) }}"
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
                                                    <i class="fas fa-users fa-2x mb-3"></i>
                                                    <p>No parents found for the selected filter.</p>
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

    <!-- Parent Modal -->
    <div class="modal fade" id="parentModal" tabindex="-1" aria-labelledby="parentModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="parentModalLabel" style="color:black!important;">Edit Parent</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="parentForm" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div id="formMethod"></div>

                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="first_name" class="form-label">First Name <span
                                            class="text-danger">*</span></label>
                                    <input type="text" class="form-control @error('first_name') is-invalid @enderror"
                                        id="first_name" name="first_name" value="{{ old('first_name') }}" required>
                                    @error('first_name')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="mb-3">
                                    <label for="email" class="form-label">Email <span
                                            class="text-danger">*</span></label>
                                    <input type="email" class="form-control @error('email') is-invalid @enderror"
                                        id="email" name="email" value="{{ old('email') }}" readonly>
                                    @error('email')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="mb-3">
                                    <label for="locality" class="form-label">Locality</label>
                                    <input type="text" class="form-control @error('locality') is-invalid @enderror"
                                        id="locality" name="locality" value="{{ old('locality') }}">
                                    @error('locality')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="mb-3">
                                    <label for="city" class="form-label">City</label>
                                    <input type="text" class="form-control @error('city') is-invalid @enderror"
                                        id="city" name="city" value="{{ old('city') }}">
                                    @error('city')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                            </div>

                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="last_name" class="form-label">Last Name</label>
                                    <input type="text" class="form-control @error('last_name') is-invalid @enderror"
                                        id="last_name" name="last_name" value="{{ old('last_name') }}">
                                    @error('last_name')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="mb-3">
                                    <label for="phone" class="form-label">Phone</label>
                                    <input type="text" class="form-control @error('phone') is-invalid @enderror"
                                        id="phone" name="phone" value="{{ old('phone') }}">
                                    @error('phone')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="mb-3">
                                    <label for="state" class="form-label">State</label>
                                    <input type="text" class="form-control @error('state') is-invalid @enderror"
                                        id="state" name="state" value="{{ old('state') }}">
                                    @error('state')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="mb-3">
                                    <label for="profile" class="form-label">Profile Photo</label>
                                    <input type="file" class="form-control @error('profile') is-invalid @enderror"
                                        id="profile" name="profile" accept="image/*">
                                    @error('profile')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    <small class="text-muted">Leave blank to keep current photo</small>
                                </div>

                            </div>

                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-primary" id="submitBtn">Update Parent</button>
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
            $('.datatable').DataTable({
                responsive: true,
                columnDefs: [{
                    orderable: false,
                    targets: [1, 6, 7, 9]
                }, {
                    searchable: false,
                    targets: [1, 6, 7]
                }],
                order: [
                    [0, 'asc']
                ]
            });

            // Export button loading state
            $('#exportBtn').on('click', function(e) {
                const $btn = $(this);
                const originalText = $btn.html();

                $btn.prop('disabled', true).html(`
                    <i class="fas fa-spinner fa-spin me-2"></i> Exporting...
                `);

                // Re-enable button after 3 seconds if still on page
                setTimeout(function() {
                    $btn.prop('disabled', false).html(originalText);
                }, 3000);
            });

            // Modal reset when closed
            $('#parentModal').on('hidden.bs.modal', function() {
                resetModal();
            });

            // Edit button click
            $('.edit-btn').on('click', function() {
                const id = $(this).data('id');
                const firstName = $(this).data('first_name');
                const lastName = $(this).data('last_name');
                const email = $(this).data('email');
                const phone = $(this).data('phone');
                const locality = $(this).data('locality');
                const city = $(this).data('city');
                const state = $(this).data('state');

                $('#parentModalLabel').text('Edit Parent');
                $('#first_name').val(firstName);
                $('#last_name').val(lastName);
                $('#email').val(email);
                $('#phone').val(phone);
                $('#locality').val(locality);
                $('#city').val(city);
                $('#state').val(state);

                $('#formMethod').html('<input type="hidden" name="_method" value="PUT">');
                $('#parentForm').attr('action', `/petz-info/public/admin/parents/${id}`);
                $('#submitBtn').text('Update Parent');
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
            $('#parentForm').on('submit', function(e) {
                const firstName = $('#first_name').val().trim();
                const email = $('#email').val().trim();

                let isValid = true;

                if (!firstName) {
                    $('#first_name').addClass('is-invalid');
                    $('#first_name').siblings('.invalid-feedback').text('First name is required.');
                    isValid = false;
                }

                if (!email) {
                    $('#email').addClass('is-invalid');
                    $('#email').siblings('.invalid-feedback').text('Email is required.');
                    isValid = false;
                }

                if (!isValid) {
                    e.preventDefault();
                    return false;
                }

                return true;
            });

            // Remove validation on input
            $('#first_name, #last_name, #email, #phone, #locality, #city, #state, #profile').on(
                'input change',
                function() {
                    if ($(this).val().trim()) {
                        $(this).removeClass('is-invalid');
                    }
                });

            function resetModal() {
                $('#parentModalLabel').text('Edit Parent');
                $('#first_name, #last_name, #email, #phone, #locality, #city, #state').val('');
                $('#profile').val('');
                $('#first_name, #last_name, #email, #phone, #locality, #city, #state, #profile')
                    .removeClass('is-invalid');
            }
        });
    </script>
@endsection
