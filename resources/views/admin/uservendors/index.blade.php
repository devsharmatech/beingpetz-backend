@extends('admin.layouts.master')

@section('title', 'User & Vendor Management')

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

    /* DataTable customization */
    #datatable_wrapper .dataTables_length,
    #datatable_wrapper .dataTables_filter,
    #datatable_wrapper .dataTables_info,
    #datatable_wrapper .dataTables_paginate {
        padding: 10px;
    }

    #datatable {
        width: 100% !important;
        margin: 0 auto;
    }

    .btn-group {
        display: flex;
        gap: 5px;
        flex-wrap: wrap;
    }

    .btn-group .btn {
        padding: 0.25rem 0.5rem;
        font-size: 0.875rem;
    }

    .badge-purple {
        background-color: #6f42c1;
        color: white;
        padding: 5px 10px;
        border-radius: 4px;
        font-weight: 500;
        font-size: 0.85em;
    }
</style>

@section('content')
    <div class="container-fluid mt-4">
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header d-flex justify-content-between">
                        <h3 class="card-title">User & Vendor Management</h3>
                        <div class="card-tools">
                            <!-- <a href="{{ route('admin.roles.create') }}" class="btn btn-secondary mr-2">
                                <i class="fas fa-plus"></i> Add New Role
                            </a> -->
                            <a href="{{ route('admin.uservendors.create') }}" class="btn btn-primary">
                                <i class="fas fa-plus"></i> Add New User/Vendor
                            </a>
                        </div>
                    </div>
                    <div class="card-body">
                        <table class="table table-bordered table-hover" id="datatable">
                            <thead>
                                <tr>
                                    <th width="5%">ID</th>
                                    <th width="15%">Name</th>
                                    <th width="15%">Email</th>
                                    <th width="10%">Phone</th>
                                    <th width="10%">Role</th>
                                    <th width="10%">City</th>
                                    <th width="10%">Status</th>
                                    <th width="25%">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php $count = 1; ?>
                                @foreach ($users as $user)
                                    <tr style="font-size: 13px;">
                                        <td>{{ $count++ }}</td>
                                        <td>{{ $user->first_name }} {{ $user->last_name }}</td>
                                        <td>{{ $user->email }}</td>
                                        <td>{{ $user->phone }}</td>
                                        <td>
                                            <span class="badge badge-{{ $user->role == 'vendor' ? 'warning' : 'info' }}"
                                                style="color: rgb(65, 65, 64);">
                                                {{ ucfirst($user->role) }}
                                            </span>
                                        </td>
                                        <td>{{ $user->city ?? 'N/A' }}</td>
                                        <td>
                                            <span class="badge badge-{{ $user->isComplete ? 'success' : 'secondary' }}"
                                                style="color: green;">
                                                {{ $user->isComplete ? 'Complete' : 'Incomplete' }}
                                            </span>
                                        </td>
                                        <td>
                                            <div class="btn-group">
                                                <a href="{{ route('admin.uservendors.show', $user->id) }}"
                                                    class="btn btn-info btn-sm" title="View" style="height: 25px;">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                                <a href="{{ route('admin.uservendors.edit', $user->id) }}"
                                                    class="btn btn-primary btn-sm" title="Edit" style="height: 25px;">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                                <form action="{{ route('admin.uservendors.destroy', $user->id) }}"
                                                    method="POST" class="d-inline">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-danger btn-sm"
                                                        onclick="return confirm('Are you sure you want to delete this?')"
                                                        title="Delete" style="height: 25px;">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </form>

                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <div class="row mt-4">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Roles & Permissions Management</h3>
                    </div>
                    <div class="card-body">
                        <table class="table table-bordered table-hover" id="rolesTable">
                            <thead>
                                <tr>
                                    <th width="5%">ID</th>
                                    <th width="15%">Role Name</th>
                                    <th width="50%">Permissions</th>
                                    <th width="15%">Description</th>
                                    <th width="15%">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($roles as $role)
                                <tr>
                                    <td>{{ $role->id }}</td>
                                    <td>{{ ucfirst($role->name) }}</td>
                                    <td>
                                        @if($role->permissions->count() > 0)
                                            @foreach($role->permissions as $permission)
                                                <span class="badge badge-purple mb-1">{{ $permission->display_name ?? $permission->name }}</span>
                                            @endforeach
                                        @else
                                            <span class="text-muted">No specific permissions assigned</span>
                                        @endif
                                        @if($role->default_permissions)
                                             <div class="mt-1">
                                                <small class="text-muted">Default: </small>
                                                @if(is_array($role->default_permissions))
                                                    @foreach($role->default_permissions as $perm)
                                                        <span class="badge badge-light border mb-1">{{ $perm }}</span>
                                                    @endforeach
                                                @endif
                                             </div>
                                        @endif
                                    </td>
                                    <td>{{ $role->description ?? 'N/A' }}</td>
                                    <td>
                                        <div class="btn-group">
                                            <a href="{{ route('admin.roles.edit', $role->id) }}" class="btn btn-primary btn-sm" title="Edit">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            
                                            <form action="{{ route('admin.roles.destroy', $role->id) }}" method="POST" class="d-inline">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure you want to delete this role?')" title="Delete">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
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

        @if (session('warning'))
            Swal.fire({
                toast: true,
                icon: 'warning',
                title: "{{ session('warning') }}",
                position: 'top-end',
                showConfirmButton: false,
                timer: 4000,
                timerProgressBar: true,
            });
        @endif

        $(document).ready(function() {
            // Initialize DataTable
            $('#datatable').DataTable({
                responsive: true,
                pageLength: 10,
                lengthMenu: [
                    [5, 10, 25, 50, -1],
                    [5, 10, 25, 50, "All"]
                ],
                order: [
                    [0, 'desc']
                ], // Sort by ID descending
                language: {
                    search: "Search:",
                    lengthMenu: "Show _MENU_ entries",
                    info: "Showing _START_ to _END_ of _TOTAL_ entries",
                    infoEmpty: "Showing 0 to 0 of 0 entries",
                    infoFiltered: "(filtered from _MAX_ total entries)",
                    paginate: {
                        first: "First",
                        last: "Last",
                        next: "Next",
                        previous: "Previous"
                    }
                }
            });
        });
    </script>
@endsection
