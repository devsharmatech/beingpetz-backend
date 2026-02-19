@extends('admin.layouts.master')

@section('title', 'Roles Management')

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
</style>

@section('content')
    <div class="container-fluid mt-4">

        <div class="row">
            <div class="col-lg-12">
                <div class="card">
                    <div class="card-header d-flex justify-content-between">
                        <h5 class="card-title mb-0">All Roles</h5>
                        <div class="d-flex align-items-center gap-2">
                            <a href="{{ route('admin.roles.create') }}" class="btn btn-primary">
                                <i class="fas fa-plus"></i> Add New Role
                            </a>
                            <a href="{{ route('admin.permissions.index') }}" class="btn btn-outline-secondary">
                                <i class="fas fa-key"></i> Manage Permissions
                            </a>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover table-centered table-nowrap mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>#</th>
                                        <th>Role Name</th>
                                        <th>Permissions</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($roles as $role)
                                        <tr>
                                            <td>{{ $loop->iteration }}</td>
                                            <td>
                                                <strong>{{ $role->name }}</strong>
                                                @if ($role->description)
                                                    <p class="text-muted mb-0 small">{{ $role->description }}</p>
                                                @endif
                                            </td>
                                            <td>
                                                @if ($role->permissions->count() > 0)
                                                    <span
                                                        class="badge bg-success rounded-pill">{{ $role->permissions->count() }}</span>
                                                @else
                                                    <span class="badge bg-secondary">No Permissions</span>
                                                @endif
                                            </td>


                                            <td>
                                                <div class="d-flex gap-2">
                                                    <a href="{{ route('admin.roles.show', $role->id) }}"
                                                        class="btn btn-sm btn-info" title="View" style="height:25px;">
                                                        <i class="fas fa-eye"></i>
                                                    </a>
                                                    <a href="{{ route('admin.roles.edit', $role->id) }}"
                                                        class="btn btn-sm btn-primary" title="Edit" style="height:25px;">
                                                        <i class="fas fa-edit"></i>
                                                    </a>
                                                    @if (!$role->is_default && $role->users->count() == 0)
                                                        <form action="{{ route('admin.roles.destroy', $role->id) }}"
                                                            method="POST" class="d-inline"
                                                            onsubmit="return confirm('Are you sure you want to delete this role?')">
                                                            @csrf
                                                            @method('DELETE')
                                                            <button type="submit" class="btn btn-sm btn-danger"
                                                                title="Delete" style="height:25px;">
                                                                <i class="fas fa-trash"></i>
                                                            </button>
                                                        </form>
                                                    @endif
                                                </div>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="7" class="text-center">No roles found. <a
                                                    href="{{ route('admin.roles.create') }}">Create the first role</a></td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>

                        <!-- Pagination -->
                        <div class="row mt-3">
                            <div class="col-sm-12">
                                {{ $roles->links() }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Permissions Modal -->
    <div class="modal fade" id="permissionsModal" tabindex="-1" aria-labelledby="permissionsModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="permissionsModalLabel">Permissions for <span id="roleName"></span></h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div id="permissionsList"></div>
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
            // View permissions in modal
            $('.view-permissions').click(function() {
                const roleName = $(this).data('role');
                const permissions = $(this).data('permissions');

                $('#roleName').text(roleName);
                $('#permissionsList').html('<p>' + permissions + '</p>');
            });

            // Search functionality
            $('#searchInput').on('keyup', function() {
                const value = $(this).val().toLowerCase();
                $('table tbody tr').filter(function() {
                    $(this).toggle($(this).text().toLowerCase().indexOf(value) > -1)
                });
            });
        });
    </script>
@endsection
