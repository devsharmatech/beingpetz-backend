<!-- resources/views/admin/settings/manage.blade.php -->
@extends('admin.layouts.master')

@section('title', $groupTitle)

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
@section('content')
    <div class="container-fluid py-4">
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">{{ $groupTitle }}</h5>
                        <a href="{{ route('admin.settings.index') }}" class="btn btn-light">
                            <i class="fas fa-arrow-left me-2"></i> Back to Settings
                        </a>
                    </div>
                    <div class="card-body">
                        @if (session('success'))
                            <div class="alert alert-success alert-dismissible fade show" role="alert">
                                {{ session('success') }}
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                        @endif

                        <form action="{{ route('admin.settings.update', $group) }}" method="POST">
                            @csrf
                            @method('PUT')

                            @foreach ($settings as $setting)
                                <div class="row mb-4">
                                    <div class="col-md-8">
                                        <label for="{{ $setting->key }}" class="form-label">
                                            {{ $setting->label }}
                                            @if ($setting->description)
                                                <small class="text-muted d-block">{{ $setting->description }}</small>
                                            @endif
                                        </label>

                                        @if ($setting->type === 'text' || $setting->type === 'email' || $setting->type === 'number')
                                            <input type="{{ $setting->type }}" class="form-control" id="{{ $setting->key }}"
                                                name="{{ $setting->key }}"
                                                value="{{ old($setting->key, $setting->value) }}"
                                                @if ($setting->type === 'number') step="any" @endif>
                                        @elseif($setting->type === 'password')
                                            <input type="password" class="form-control" id="{{ $setting->key }}"
                                                name="{{ $setting->key }}"
                                                placeholder="Leave blank to keep current password" value="">
                                        @elseif($setting->type === 'textarea')
                                            <textarea class="form-control" id="{{ $setting->key }}" name="{{ $setting->key }}" rows="4">{{ old($setting->key, $setting->value) }}</textarea>
                                        @elseif($setting->type === 'boolean')
                                            <div class="form-check form-switch">
                                                <input class="form-check-input" type="checkbox" id="{{ $setting->key }}"
                                                    name="{{ $setting->key }}" value="1"
                                                    {{ old($setting->key, $setting->value) ? 'checked' : '' }}>
                                                <label class="form-check-label" for="{{ $setting->key }}">
                                                    {{ $setting->value ? 'Enabled' : 'Disabled' }}
                                                </label>
                                            </div>
                                        @elseif($setting->type === 'select')
                                            <select class="form-control" id="{{ $setting->key }}"
                                                name="{{ $setting->key }}">
                                                @foreach ($setting->options as $optionValue => $optionLabel)
                                                    <option value="{{ $optionValue }}"
                                                        {{ old($setting->key, $setting->value) == $optionValue ? 'selected' : '' }}>
                                                        {{ $optionLabel }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        @endif

                                        @error($setting->key)
                                            <div class="text-danger small mt-1">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                            @endforeach

                            <div class="row mt-4">
                                <div class="col-md-8">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-save me-2"></i> Update Settings
                                    </button>
                                    <a href="{{ route('admin.settings.index') }}" class="btn btn-light">
                                        Cancel
                                    </a>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
