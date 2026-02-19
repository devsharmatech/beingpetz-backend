@extends('admin.layouts.master')
@section('title', 'Deleted Users')

@section('content')
    <div class="container-fluid mt-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2>Deleted Users</h2>
            <a href="{{ route('admin.dashboard') }}" class="btn btn-primary">
                <i class="bi bi-arrow-left"></i> Back to Dashboard
            </a>
        </div>

        <div class="card">
            <div class="card-header" style="background-color:rgb(131, 55, 178);">
                <h5 class="card-title mb-0 text-white">
                    <i class="bi bi-trash"></i> Deleted Users List
                    <span class="badge bg-danger ms-2" id="totalCount">{{ $deletedUsers->count() }}</span>
                </h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover table-striped" id="deletedUsersTable">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Name</th>
                                <th>Email</th>
                                <th>Role</th>
                                <th>Account Created</th>
                                {{-- <th>Actions</th> --}}
                            </tr>
                        </thead>
                        <tbody>
                            <?php $count = 1; ?>
                            @foreach ($deletedUsers as $user)
                                <tr>
                                    <td><strong>{{ $count++ }}</strong></td>
                                    <td>
                                        <div class="d-flex align-items-center">

                                            {{ $user->first_name }} {{ $user->last_name }}
                                        </div>
                                    </td>
                                    <td>{{ $user->email }}</td>
                                    <td>
                                        <span class="badge bg-info">{{ ucfirst($user->role) }}</span>
                                    </td>

                                    <td>
                                        <small class="text-muted">
                                            {{ $user->created_at ? $user->created_at->format('M d, Y') : 'N/A' }}
                                        </small>
                                    </td>
                                    {{-- <td>
                                        <a href="{{ route('admin.users.details', $user->id) }}" class="btn btn-sm btn-info">
                                            <i class="bi bi-eye"></i> View Details
                                        </a>
                                    </td> --}}
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>


    </div>
@endsection

@section('scripts')
    <!-- Include DataTables CSS -->
    <link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">
    <link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/buttons/2.4.1/css/buttons.bootstrap5.min.css">

    <!-- Include DataTables JS -->
    <script type="text/javascript" charset="utf8" src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js">
    </script>
    <script type="text/javascript" charset="utf8" src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js">
    </script>
    <script type="text/javascript" charset="utf8"
        src="https://cdn.datatables.net/buttons/2.4.1/js/dataTables.buttons.min.js"></script>
    <script type="text/javascript" charset="utf8"
        src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.bootstrap5.min.js"></script>
    <script type="text/javascript" charset="utf8" src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js">
    </script>
    <script type="text/javascript" charset="utf8"
        src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/pdfmake.min.js"></script>
    <script type="text/javascript" charset="utf8" src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/vfs_fonts.js">
    </script>
    <script type="text/javascript" charset="utf8" src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.html5.min.js">
    </script>
    <script type="text/javascript" charset="utf8" src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.print.min.js">
    </script>

    <script>
        $(document).ready(function() {
            $('#deletedUsersTable').DataTable({
                "pageLength": 10,
                "lengthMenu": [10, 25, 50, 100],
                "order": [
                    [0, 'desc']
                ],
                "language": {
                    "search": "Search:",
                    "lengthMenu": "Show _MENU_ entries",
                    "info": "Showing _START_ to _END_ of _TOTAL_ entries",
                    "infoEmpty": "Showing 0 to 0 of 0 entries",
                    "infoFiltered": "(filtered from _MAX_ total entries)",
                    "paginate": {
                        "first": "First",
                        "last": "Last",
                        "next": "Next",
                        "previous": "Previous"
                    }
                },
                "dom": '<"row"<"col-sm-12 col-md-6"B><"col-sm-12 col-md-6"f>>' +
                    '<"row"<"col-sm-12"tr>>' +
                    '<"row"<"col-sm-12 col-md-5"i><"col-sm-12 col-md-7"p>>',
                "buttons": [{
                        extend: 'excel',
                        text: '<i class="bi bi-file-earmark-excel"></i> Excel',
                        className: 'btn btn-success btn-sm'
                    },
                    {
                        extend: 'pdf',
                        text: '<i class="bi bi-file-earmark-pdf"></i> PDF',
                        className: 'btn btn-danger btn-sm'
                    },
                    {
                        extend: 'print',
                        text: '<i class="bi bi-printer"></i> Print',
                        className: 'btn btn-secondary btn-sm'
                    }
                ]
            });
        });
    </script>

    <style>
        .dataTables_wrapper .dataTables_length,
        .dataTables_wrapper .dataTables_filter {
            margin-bottom: 1rem;
        }

        .dataTables_wrapper .dataTables_paginate .paginate_button {
            padding: 0.375rem 0.75rem;
        }

        .table th {
            border-top: none;
            font-weight: 600;
        }

        .avatar-sm {
            font-size: 0.8rem;
        }
    </style>
@endsection
