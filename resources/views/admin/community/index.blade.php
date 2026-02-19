@extends('admin.layouts.master')
@section('title')
    Community Management
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

    .community-image {
        width: 60px;
        height: 60px;
        object-fit: cover;
        border-radius: 8px;
        border: 2px solid #e9ecef;
    }

    .badge-public {
        background-color: #28a745;
    }

    .badge-private {
        background-color: #6c757d;
    }

    .action-btns .btn-group {
        display: flex;
        gap: 2px;
    }

    .action-btns .btn {
        padding: 0.25rem 0.5rem;
        font-size: 0.75rem;
    }
</style>

@section('content')
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="m-4 d-sm-flex align-items-center justify-content-between">
                    <h4 class="mb-sm-0">Community Management</h4>
                    <div class="d-flex gap-2">
                        <!-- Export Button -->
                        <button type="button" class="btn btn-success" id="exportBtn">
                            <i class="fas fa-download me-2"></i> Export Data
                        </button>
                        <a href="{{ route('admin.community.create') }}" class="btn btn-primary">
                            <i class="fas fa-plus me-2"></i> Create Community
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        <h4 class="card-title">All Communities</h4>

                        <div class="table-responsive">
                            <table class="table table-bordered table-striped datatable">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>Image</th>
                                        <th>Name</th>
                                        <th>Type</th>
                                        <th>Members</th>
                                        <th>Created By</th>
                                        <th>Moderators</th> <!-- New Column -->
                                        <th>Created Date</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                @if ($communities->count() > 0)
                                    <tbody>
                                        @foreach ($communities as $community)
                                            <tr>
                                                <td>{{ $loop->iteration }}</td>
                                                <td>
                                                    @if ($community->profile)
                                                        <img src="{{ asset($community->profile) }}" alt="Community Image"
                                                            class="community-image">
                                                    @else
                                                        <div
                                                            class="community-image bg-light d-flex align-items-center justify-content-center">
                                                            <i class="fas fa-users text-muted"></i>
                                                        </div>
                                                    @endif
                                                </td>
                                                <td>
                                                    <strong>{{ $community->name }}</strong>
                                                </td>
                                                <td>
                                                    <span class="badge badge-{{ $community->type }}">
                                                        {{ ucfirst($community->type) }}
                                                    </span>
                                                </td>
                                                <td>
                                                    <span class="badge bg-info">
                                                        {{ $community->members->count() }} members
                                                    </span>
                                                </td>
                                                <td>
                                                    @if ($community->creator)
                                                        {{ $community->creator->first_name }}
                                                        {{ $community->creator->last_name }}
                                                    @else
                                                        <span class="text-muted">N/A</span>
                                                    @endif
                                                </td>
                                                <td>
                                                    <!-- Moderator Details -->
                                                    @if ($community->moderators->count() > 0)
                                                        @foreach ($community->moderators->take(2) as $moderator)
                                                            <span class="badge bg-warning text-dark">
                                                                {{ $moderator->user->first_name ?? 'N/A' }}
                                                            </span>
                                                        @endforeach
                                                        @if ($community->moderators->count() > 2)
                                                            <span
                                                                class="badge bg-secondary">+{{ $community->moderators->count() - 2 }}
                                                                more</span>
                                                        @endif
                                                    @else
                                                        <span class="text-muted">No moderators</span>
                                                    @endif
                                                </td>
                                                <td>{{ $community->created_at->format('M d, Y') }}</td>
                                                <td class="action-btns">
                                                    <div class="" role="group">
                                                        <a href="{{ route('admin.community.edit', $community->id) }}"
                                                            class="btn btn-sm btn-primary edit-btn"
                                                            style="height: 24px!important;" title="Edit Community">
                                                            <i class="fas fa-edit"></i>
                                                        </a>

                                                        <!-- Transfer Ownership Button -->
                                                        <button type="button"
                                                            class="btn btn-sm btn-info transfer-owner-btn"
                                                            data-community-id="{{ $community->id }}"
                                                            data-community-name="{{ $community->name }}"
                                                            title="Transfer Ownership">
                                                            <i class="fas fa-user-shield"></i>
                                                        </button>

                                                        <form
                                                            action="{{ route('admin.community.destroy', $community->id) }}"
                                                            method="POST" class="delete-form d-inline">
                                                            @csrf
                                                            @method('DELETE')
                                                            <button type="submit" class="btn btn-sm btn-danger"
                                                                title="Delete">
                                                                <i class="fas fa-trash"></i>
                                                            </button>
                                                        </form>
                                                    </div>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                @else
                                    <tbody>
                                        <tr>
                                            <td colspan="9" class="text-center">No communities found</td>
                                        </tr>
                                    </tbody>
                                @endif
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

        $(document).ready(function() {
            $('.datatable').DataTable({
                responsive: true,
                order: [
                    [0, 'desc']
                ]
            });

            // Export Functionality
            $('#exportBtn').on('click', function() {
                window.location.href = "{{ route('admin.community.export') }}";
            });

            // Transfer Ownership
            $('.transfer-owner-btn').on('click', function() {
                const communityId = $(this).data('community-id');
                const communityName = $(this).data('community-name');

                Swal.fire({
                    title: `Transfer Ownership - ${communityName}`,
                    text: 'This feature will allow you to change community admin/moderators',
                    icon: 'info',
                    showCancelButton: true,
                    confirmButtonText: 'Continue',
                    cancelButtonText: 'Cancel'
                }).then((result) => {
                    if (result.isConfirmed) {
                        window.location.href = `/petz-info/public/admin/community/${communityId}/transfer-ownership`;
                    }
                });
            });

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
