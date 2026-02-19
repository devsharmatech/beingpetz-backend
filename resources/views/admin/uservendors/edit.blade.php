@extends('admin.layouts.master')

@section('title', 'Edit User/Vendor')

@section('content')
    <div class="container-fluid mt-4">
        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header d-flex justify-content-between">
                        <h3 class="card-title">Edit User/Vendor: {{ $user->first_name }} {{ $user->last_name }}</h3>
                        <div class="card-tools">
                            <a href="{{ route('admin.uservendors.index') }}" class="btn btn-secondary">
                                <i class="fas fa-arrow-left"></i> Back
                            </a>
                        </div>
                    </div>
                    <form action="{{ route('admin.uservendors.update', $user->id) }}" method="POST">
                        @csrf
                        @method('PUT')
                        <div class="card-body">
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="first_name">First Name *</label>
                                        <input type="text" name="first_name" id="first_name"
                                            class="form-control @error('first_name') is-invalid @enderror"
                                            value="{{ old('first_name', $user->first_name) }}" required>
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
                                            value="{{ old('last_name', $user->last_name) }}" required>
                                        @error('last_name')
                                            <span class="invalid-feedback">{{ $message }}</span>
                                        @enderror
                                    </div>
                                </div>
                            </div>

                            <div class="form-group mb-3">
                                <label for="email">Email *</label>
                                <input type="email" name="email" id="email"
                                    class="form-control @error('email') is-invalid @enderror"
                                    value="{{ old('email', $user->email) }}" required>
                                @error('email')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>

                            <div class="form-group mb-3">
                                <label for="phone">Phone *</label>
                                <input type="text" name="phone" id="phone"
                                    class="form-control @error('phone') is-invalid @enderror"
                                    value="{{ old('phone', $user->phone) }}" required>
                                @error('phone')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>

                            <div class="form-group mb-3">
                                <label for="role_id">Role *</label>
                                <select name="role_id" id="role_id"
                                    class="form-control @error('role_id') is-invalid @enderror" required>
                                    <option value="">Select Role</option>
                                    @foreach ($roles as $role)
                                        <option value="{{ $role->id }}"
                                            {{ $user->role_id == $role->id ? 'selected' : '' }}>
                                            {{ $role->display_name }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('role_id')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>

                            <div class="row mb-3">
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="city">City</label>
                                        <input type="text" name="city" id="city"
                                            class="form-control @error('city') is-invalid @enderror"
                                            value="{{ old('city', $user->city) }}">
                                        @error('city')
                                            <span class="invalid-feedback">{{ $message }}</span>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="state">State</label>
                                        <input type="text" name="state" id="state"
                                            class="form-control @error('state') is-invalid @enderror"
                                            value="{{ old('state', $user->state) }}">
                                        @error('state')
                                            <span class="invalid-feedback">{{ $message }}</span>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="locality">Locality</label>
                                        <input type="text" name="locality" id="locality"
                                            class="form-control @error('locality') is-invalid @enderror"
                                            value="{{ old('locality', $user->locality) }}">
                                        @error('locality')
                                            <span class="invalid-feedback">{{ $message }}</span>
                                        @enderror
                                    </div>
                                </div>


                            </div>

                            <!-- Password Reset Section (Optional - agar aap show karna chahte hain) -->
                            <div class="alert alert-info mt-3">
                                <h5><i class="fas fa-info-circle"></i> Password Information</h5>
                                <p class="mb-1">Password cannot be changed from here.</p>

                            </div>
                        </div>
                        <div class="card-footer">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i> Update
                            </button>
                            <a href="{{ route('admin.uservendors.index') }}" class="btn btn-secondary">
                                Cancel
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
