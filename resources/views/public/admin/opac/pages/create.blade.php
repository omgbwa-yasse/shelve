@extends('layouts.app')

@section('title', __('Create OPAC Page'))

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="page-title-box d-flex align-items-center justify-content-between">
                <div>
                    <h4 class="page-title">{{ __('Create OPAC Page') }}</h4>
                    <ol class="breadcrumb m-0">
                        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ __('Dashboard') }}</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('admin.opac.configurations.index') }}">{{ __('OPAC') }}</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('admin.opac.pages.index') }}">{{ __('Pages') }}</a></li>
                        <li class="breadcrumb-item active">{{ __('Create') }}</li>
                    </ol>
                </div>
                <div class="page-title-right">
                    <a href="{{ route('admin.opac.pages.index') }}" class="btn btn-secondary">
                        <i class="mdi mdi-arrow-left me-1"></i> {{ __('Back to Pages') }}
                    </a>
                </div>
            </div>
        </div>
    </div>

    <form action="{{ route('admin.opac.pages.store') }}" method="POST" enctype="multipart/form-data">
        @csrf

        <div class="row">
            <!-- Main Content -->
            <div class="col-lg-8">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">{{ __('Page Content') }}</h5>
                    </div>
                    <div class="card-body">
                        <!-- Title -->
                        <div class="mb-3">
                            <label for="title" class="form-label">{{ __('Title') }} <span class="text-danger">*</span></label>
                            <input type="text"
                                   class="form-control @error('title') is-invalid @enderror"
                                   id="title"
                                   name="title"
                                   value="{{ old('title') }}"
                                   required
                                   placeholder="{{ __('Enter page title') }}">
                            @error('title')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Name (URL slug) -->
                        <div class="mb-3">
                            <label for="name" class="form-label">{{ __('URL Name') }}</label>
                            <input type="text"
                                   class="form-control @error('name') is-invalid @enderror"
                                   id="name"
                                   name="name"
                                   value="{{ old('name') }}"
                                   placeholder="{{ __('URL slug (auto-generated from title if empty)') }}">
                            <div class="form-text">{{ __('Used in URL: /opac/pages/{name}. Leave empty to auto-generate from title.') }}</div>
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Content -->
                        <div class="mb-3">
                            <label for="content" class="form-label">{{ __('Content') }}</label>
                            <textarea class="form-control @error('content') is-invalid @enderror"
                                      id="content"
                                      name="content"
                                      rows="15"
                                      placeholder="{{ __('Enter page content (HTML supported)') }}">{{ old('content') }}</textarea>
                            @error('content')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Meta Description -->
                        <div class="mb-3">
                            <label for="meta_description" class="form-label">{{ __('Meta Description') }}</label>
                            <textarea class="form-control @error('meta_description') is-invalid @enderror"
                                      id="meta_description"
                                      name="meta_description"
                                      rows="3"
                                      placeholder="{{ __('SEO description for this page') }}">{{ old('meta_description') }}</textarea>
                            @error('meta_description')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>
            </div>

            <!-- Sidebar -->
            <div class="col-lg-4">
                <!-- Publish Settings -->
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">{{ __('Publish Settings') }}</h5>
                    </div>
                    <div class="card-body">
                        <!-- Status -->
                        <div class="mb-3">
                            <label for="status" class="form-label">{{ __('Status') }}</label>
                            <select class="form-select @error('status') is-invalid @enderror" id="status" name="status">
                                <option value="draft" {{ old('status') === 'draft' ? 'selected' : '' }}>{{ __('Draft') }}</option>
                                <option value="published" {{ old('status') === 'published' ? 'selected' : '' }}>{{ __('Published') }}</option>
                                <option value="private" {{ old('status') === 'private' ? 'selected' : '' }}>{{ __('Private') }}</option>
                            </select>
                            @error('status')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Published Checkbox -->
                        <div class="mb-3">
                            <div class="form-check">
                                <input type="checkbox"
                                       class="form-check-input @error('is_published') is-invalid @enderror"
                                       id="is_published"
                                       name="is_published"
                                       value="1"
                                       {{ old('is_published') ? 'checked' : '' }}>
                                <label class="form-check-label" for="is_published">
                                    {{ __('Published') }}
                                </label>
                            </div>
                            <div class="form-text">{{ __('Make this page visible to public users') }}</div>
                            @error('is_published')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Order -->
                        <div class="mb-3">
                            <label for="order" class="form-label">{{ __('Display Order') }}</label>
                            <input type="number"
                                   class="form-control @error('order') is-invalid @enderror"
                                   id="order"
                                   name="order"
                                   value="{{ old('order', 0) }}"
                                   min="0"
                                   placeholder="0">
                            <div class="form-text">{{ __('Lower numbers appear first in navigation') }}</div>
                            @error('order')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>

                <!-- Page Hierarchy -->
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">{{ __('Page Hierarchy') }}</h5>
                    </div>
                    <div class="card-body">
                        <!-- Parent Page -->
                        <div class="mb-3">
                            <label for="parent_id" class="form-label">{{ __('Parent Page') }}</label>
                            <select class="form-select @error('parent_id') is-invalid @enderror" id="parent_id" name="parent_id">
                                <option value="">{{ __('No Parent (Top Level)') }}</option>
                                @foreach($parentPages as $parentPage)
                                    <option value="{{ $parentPage->id }}" {{ old('parent_id') == $parentPage->id ? 'selected' : '' }}>
                                        {{ $parentPage->title }}
                                    </option>
                                @endforeach
                            </select>
                            <div class="form-text">{{ __('Select a parent page to create a sub-page') }}</div>
                            @error('parent_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>

                <!-- Featured Image -->
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">{{ __('Featured Image') }}</h5>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label for="image" class="form-label">{{ __('Upload Image') }}</label>
                            <input type="file"
                                   class="form-control @error('image') is-invalid @enderror"
                                   id="image"
                                   name="image"
                                   accept="image/*">
                            <div class="form-text">{{ __('Supported formats: JPG, PNG, GIF. Max size: 2MB') }}</div>
                            @error('image')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Image Preview -->
                        <div id="imagePreview" style="display: none;">
                            <img id="previewImg" src="" alt="{{ __('Image Preview') }}" class="img-fluid rounded">
                            <button type="button" class="btn btn-sm btn-outline-danger mt-2" id="removeImage">
                                <i class="mdi mdi-delete"></i> {{ __('Remove') }}
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Actions -->
                <div class="card">
                    <div class="card-body">
                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-primary">
                                <i class="mdi mdi-content-save me-1"></i> {{ __('Create Page') }}
                            </button>
                            <button type="submit" name="save_and_continue" value="1" class="btn btn-outline-primary">
                                <i class="mdi mdi-content-save-edit me-1"></i> {{ __('Save & Continue Editing') }}
                            </button>
                            <a href="{{ route('admin.opac.pages.index') }}" class="btn btn-outline-secondary">
                                <i class="mdi mdi-arrow-left me-1"></i> {{ __('Cancel') }}
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Auto-generate slug from title
    const titleInput = document.getElementById('title');
    const nameInput = document.getElementById('name');
    let manuallyChanged = false;

    nameInput.addEventListener('input', function() {
        manuallyChanged = this.value.length > 0;
    });

    titleInput.addEventListener('input', function() {
        if (!manuallyChanged) {
            nameInput.value = generateSlug(this.value);
        }
    });

    function generateSlug(text) {
        return text
            .toLowerCase()
            .replace(/[^\w\s-]/g, '') // Remove special characters
            .replace(/\s+/g, '-')     // Replace spaces with hyphens
            .replace(/-+/g, '-')      // Replace multiple hyphens with single
            .trim('-');               // Remove leading/trailing hyphens
    }

    // Image preview functionality
    const imageInput = document.getElementById('image');
    const imagePreview = document.getElementById('imagePreview');
    const previewImg = document.getElementById('previewImg');
    const removeImageBtn = document.getElementById('removeImage');

    imageInput.addEventListener('change', function(e) {
        const file = e.target.files[0];
        if (file) {
            const reader = new FileReader();
            reader.onload = function(e) {
                previewImg.src = e.target.result;
                imagePreview.style.display = 'block';
            };
            reader.readAsDataURL(file);
        }
    });

    removeImageBtn.addEventListener('click', function() {
        imageInput.value = '';
        imagePreview.style.display = 'none';
        previewImg.src = '';
    });

    // Form validation
    const form = document.querySelector('form');
    form.addEventListener('submit', function(e) {
        const title = titleInput.value.trim();
        if (!title) {
            e.preventDefault();
            titleInput.focus();
            alert('{{ __("Please enter a page title") }}');
            return false;
        }
    });
});
</script>
@endpush
@endsection
