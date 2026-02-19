@extends('admin.layouts.master')

@section('title', 'Permissions')

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
</style>

@section('content')
    <div class="container-fluid mt-4">
        <div class="row">
            <div class="col-lg-12">
                <div class="card">
                    <div class="card-header">
                        <div class="d-flex justify-content-between align-items-center">
                            <h5 class="card-title mb-0">Permission List</h5>
                            <div>
                                <a href="{{ route('admin.permissions.create') }}" class="btn btn-primary btn-sm">
                                    <i class="fas fa-plus"></i> Add New Permission
                                </a>
                            </div>
                        </div>
                    </div>
                    <div class="card-body">
                        <!-- Module Filter -->
                        <div class="row mb-3">
                            <div class="col-md-3">
                                <select class="form-control" id="moduleFilter">
                                    <option value="">All Modules</option>
                                    @foreach ($modules as $module)
                                        <option value="{{ $module }}">{{ $module }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-6">
                                <input type="text" class="form-control" id="searchInput"
                                    placeholder="Search permissions...">
                            </div>
                        </div>

                        <div class="table-responsive">
                            <table class="table table-hover table-striped" id="permissionTable">
                                <thead>
                                    <tr>
                                        <th width="5%">ID</th>
                                        <th width="15%">Permission</th>
                                        <th width="15%">Display Name</th>

                                        <th width="10%">Status</th>
                                        <th width="10%">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php $count = 1; ?>
                                    @forelse ($permissions as $permission)
                                        <tr>
                                            <td>{{ $count++ }}</td>
                                            <td>
                                                <i class="{{ $permission->icon }} me-2 text-primary"></i>
                                                {{ $permission->name }}
                                            </td>
                                            <td>{{ $permission->display_name }}</td>

                                            <td>
                                                @if ($permission->is_active)
                                                    <span class="badge bg-success">Active</span>
                                                @else
                                                    <span class="badge bg-danger">Inactive</span>
                                                @endif
                                            </td>
                                            <td>
                                                <div class="btn-group" role="group">
                                                    <a href="{{ route('admin.permissions.edit', $permission->id) }}"
                                                        class="btn btn-sm btn-primary" title="Edit" style="height:25px;">
                                                        <i class="fas fa-edit"></i>
                                                    </a>
                                                    <form action="{{ route('admin.permissions.destroy', $permission->id) }}"
                                                        method="POST" class="d-inline delete-form">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="btn btn-sm btn-danger" title="Delete"
                                                            onclick="return confirm('Are you sure?')" style="height:25px;">
                                                            <i class="fas fa-trash"></i>
                                                        </button>
                                                    </form>
                                                </div>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="8" class="text-center">No permissions found</td>
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
            var table = $('#permissionTable').DataTable({
                responsive: true,
                columnDefs: [{
                    orderable: false,
                    targets: [4] // Actions column (6th column, zero-indexed)
                }],
                order: [
                    [0, 'asc'] // Order by ID column
                ],
                initComplete: function() {
                    // Add search input functionality
                    $('#searchInput').on('keyup', function() {
                        table.search(this.value).draw();
                    });

                    // Add module filter functionality
                    $('#moduleFilter').on('change', function() {
                        var module = $(this).val();
                        if (module) {
                            table.column(3).search('^' + module + '$', true, false).draw();
                        } else {
                            table.column(3).search('').draw();
                        }
                    });
                }
            });

            // Alternative way without DataTable if you want client-side filtering
            /* 
            $('#searchInput').on('keyup', function() {
                const value = $(this).val().toLowerCase();
                $('#permissionTable tbody tr').filter(function() {
                    $(this).toggle($(this).text().toLowerCase().indexOf(value) > -1);
                });
            });

            $('#moduleFilter').change(function() {
                const module = $(this).val();
                if (module) {
                    $('#permissionTable tbody tr').hide();
                    $(`#permissionTable tbody tr[data-module="${module}"]`).show();
                } else {
                    $('#permissionTable tbody tr').show();
                }
            });
            */
        });
    </script>
@endsection
