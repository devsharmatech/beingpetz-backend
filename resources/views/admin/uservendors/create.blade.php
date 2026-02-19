@extends('admin.layouts.master')

@section('title', 'Create User/Vendor')

@section('content')
    <div class="container-fluid mt-4">
        <div class="row">
            <div class="col-md-12 ">
                <div class="card">
                    <div class="card-header d-flex justify-content-between">
                        <h3 class="card-title">Create New User/Vendor</h3>
                        <div class="card-tools">
                            <a href="{{ route('admin.uservendors.index') }}" class="btn btn-secondary">
                                <i class="fas fa-arrow-left"></i> Back
                            </a>
                        </div>
                    </div>
                    <form action="{{ route('admin.uservendors.store') }}" method="POST">
                        @csrf
                        <div class="card-body">
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="first_name">First Name *</label>
                                        <input type="text" name="first_name" id="first_name"
                                            class="form-control @error('first_name') is-invalid @enderror"
                                            value="{{ old('first_name') }}" required>
                                        @error('first_name')
                                            <span class="invalid-feedback">{{ $message }}</span>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="last_name">Last Name *</label>
                                        <input type="text" name="last_name" id="last_name"
                                            class="form-control @error('last_name') is-invalid @enderror"
                                            value="{{ old('last_name') }}" required>
                                        @error('last_name')
                                            <span class="invalid-feedback">{{ $message }}</span>
                                        @enderror
                                    </div>
                                </div>
                            </div>

                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="password">Password *</label>
                                        <input type="password" name="password" id="password"
                                            class="form-control @error('password') is-invalid @enderror"
                                            value="{{ old('password') }}" required>
                                        @error('password')
                                            <span class="invalid-feedback">{{ $message }}</span>
                                        @enderror
                                        <small class="form-text text-muted">Minimum 8 characters</small>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="password_confirmation">Confirm Password *</label>
                                        <input type="password" name="password_confirmation" id="password_confirmation"
                                            class="form-control @error('password') is-invalid @enderror"
                                            value="{{ old('password_confirmation') }}" required>
                                        @error('password')
                                            <span class="invalid-feedback">{{ $message }}</span>
                                        @enderror
                                    </div>
                                </div>
                            </div>

                            <div class="form-group mb-3">
                                <label for="email">Email *</label>
                                <input type="email" name="email" id="email"
                                    class="form-control @error('email') is-invalid @enderror" value="{{ old('email') }}"
                                    required>
                                @error('email')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                                <small class="text-muted">Password will be generated and sent to this email</small>
                            </div>

                            <div class="form-group mb-3">
                                <label for="phone">Phone *</label>
                                <input type="text" name="phone" id="phone"
                                    class="form-control @error('phone') is-invalid @enderror" value="{{ old('phone') }}"
                                    required>
                                @error('phone')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>

                            {{-- ... existing code ... --}}

                            <div class="form-group mb-3">
                                <label for="role">Role *</label>
                                <select name="role_id" id="role"
                                    class="form-control @error('role') is-invalid @enderror" required>
                                    <option value="">Select Role</option>
                                    @foreach ($roles as $role)
                                        <option value="{{ $role->id }}">{{ $role->display_name }}</option>
                                    @endforeach
                                </select>
                                @error('role')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>



                            <div class="row mb-3">
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="city">City</label>
                                        <input type="text" name="city" id="city" class="form-control"
                                            value="{{ old('city') }}">
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="state">State</label>
                                        <input type="text" name="state" id="state" class="form-control"
                                            value="{{ old('state') }}">
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="locality">Locality</label>
                                        <input type="text" name="locality" id="locality" class="form-control"
                                            value="{{ old('locality') }}">
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="card-footer">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i> Create User/Vendor
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
