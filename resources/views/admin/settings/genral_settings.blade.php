<!-- resources/views/admin/settings/index.blade.php -->
@extends('admin.layouts.master')

@section('title', 'App Settings')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

@section('content')
    <div class="container-fluid py-4">
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">App Settings</h5>
                    </div>
                    <div class="card-body p-0">
                        @if (session('success'))
                            <div class="alert alert-success alert-dismissible fade show m-3" role="alert">
                                {{ session('success') }}
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                        @endif

                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th width="50">ID</th>
                                        <th>SETTING NAME</th>
                                        <th>TYPE</th>
                                        <th>VALUE</th>
                                        <th>STATUS</th>
                                        <th width="120">MANAGE</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($groups as $key => $groupName)
                                        @php
                                            $groupSettings = $settings[$key] ?? [];
                                            $firstSetting = $groupSettings->first();
                                        @endphp
                                        @if ($firstSetting)
                                            <tr>
                                                <td>{{ $loop->iteration }}</td>
                                                <td>
                                                    <strong>{{ $groupName }}</strong>
                                                </td>
                                                <td>
                                                    <span
                                                        class="badge bg-info text-capitalize">{{ $firstSetting->group }}</span>
                                                </td>
                                                <td>
                                                    @if ($key === 'email')
                                                        <span
                                                            class="text-muted">{{ $firstSetting->value ?: 'Not Configured' }}</span>
                                                    @elseif($key === 'website')
                                                        <span class="text-muted">Configured</span>
                                                    @elseif($key === 'notification')
                                                        <span class="text-muted">Enabled</span>
                                                    @elseif($key === 'page')
                                                        <span class="text-muted">Published</span>
                                                    @else
                                                        <span class="text-muted">Configured</span>
                                                    @endif
                                                </td>
                                                <td>
                                                    <span class="badge bg-success">Active</span>
                                                </td>
                                                <td>
                                                    <a href="{{ route('admin.settings.manage', $key) }}"
                                                        class="btn btn-sm btn-primary">
                                                        <i class="fas fa-cog me-1"></i> Manage
                                                    </a>
                                                </td>
                                            </tr>
                                        @endif
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
