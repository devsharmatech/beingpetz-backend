@extends('admin.layouts.master')
@section('title')
    Legal & Compliance
@endsection

@section('css')
    <link href="https://cdn.jsdelivr.net/npm/summernote@0.8.18/dist/summernote-lite.min.css" rel="stylesheet">
@endsection

@section('content')
    <div class="container-fluid py-4">
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h1 class="page-title mb-0">Terms & Conditions</h1>
                    </div>
                    <div class="card-body">
                        @if (session('success'))
                            <div class="alert alert-success alert-dismissible fade show" role="alert">
                                {{ session('success') }}
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                        @endif

                        <form action="{{ route('admin.legal.update') }}" method="POST">
                            @csrf
                            
                            @foreach ($settings as $setting)
                                <div class="mb-4">
                                    <label for="{{ $setting->key }}" class="form-label h5">{{ $setting->label }}</label>
                                    @if ($setting->description)
                                        <p class="text-muted small mb-2">{{ $setting->description }}</p>
                                    @endif
                                    
                                    <textarea class="form-control summernote" id="{{ $setting->key }}" name="{{ $setting->key }}" rows="10">{{ old($setting->key, $setting->value) }}</textarea>
                                </div>
                                <hr class="my-4">
                            @endforeach

                            <div class="d-flex justify-content-end">
                                <button type="submit" class="btn btn-primary btn-sm">
                                    <i class="fas fa-save me-2"></i> Save Changes
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/summernote@0.8.18/dist/summernote-lite.min.js"></script>

    <script>
        $(document).ready(function() {
            $('.summernote').summernote({
                height: 400,
                toolbar: [
                    ['style', ['style']],
                    ['font', ['bold', 'underline', 'clear']],
                    ['color', ['color']],
                    ['para', ['ul', 'ol', 'paragraph']],
                    ['table', ['table']],
                    ['insert', ['link', 'picture', 'video', 'hr']],
                    ['view', ['fullscreen', 'codeview', 'help']]
                ]
            });
        });
    </script>
@endsection
