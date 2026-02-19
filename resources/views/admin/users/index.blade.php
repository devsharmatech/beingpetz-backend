@extends('admin.layouts.master')
@section('title', 'Users')

@section('content')
    <div class="container-fluid">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2>Users Management</h2>
            <a href="{{ route('admin.dashboard') }}" class="btn btn-primary">
                <i class="bi bi-arrow-left"></i> Back to Dashboard
            </a>
        </div>

        <div class="card">
            <div class="card-body">
                <p>Users list will be displayed here. Total Users: {{ $users->total() }}</p>
                <!-- Add your users table here -->
            </div>
        </div>
    </div>
@endsection
