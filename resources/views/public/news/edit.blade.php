@extends('layouts.app')

@section('conte                            @if($news->image_path)
                                <div class="mt-2">
                                    <img src="{{ asset('storage/' . $news->image_path) }}" class="img-thumbnail" style="max-height: 200px;" alt="Image actuelle">
                                </div>
                            @endif<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h2>Modifier l'actualité</h2>
                </div>

                <div class="card-body">
                    <form method="POST" action="{{ route('public.news.update', $news) }}" enctype="multipart/form-data">
                        @csrf
                        @method('PUT')

                        <div class="form-group mb-3">
                            <label for="title">Titre</label>
                            <input type="text" class="form-control @error('title') is-invalid @enderror"
                                   id="title" name="title" value="{{ old('title', $news->title) }}" required>
                            @error('title')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                        </div>

                        <div class="form-group mb-3">
                            <label for="content">Contenu</label>
                            <textarea class="form-control @error('content') is-invalid @enderror"
                                      id="content" name="content" rows="10" required>{{ old('content', $news->content) }}</textarea>
                            @error('content')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                        </div>

                        <div class="form-group mb-3">
                            <label for="image">Image</label>
                            @if($news->image_path)
                                <div class="mb-2">
                                    <img src="{{ asset('storage/' . $news->image_path) }}" class="img-thumbnail" style="max-height: 200px;" alt="Image actuelle">
                                </div>
                            @endif
                            <input type="file" class="form-control @error('image') is-invalid @enderror"
                                   id="image" name="image" accept="image/*">
                            <small class="form-text text-muted">Laissez vide pour conserver l'image actuelle</small>
                            @error('image')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                        </div>

                        <div class="form-group mb-3">
                            <label for="published_at">Date de publication</label>
                            <input type="datetime-local" class="form-control @error('published_at') is-invalid @enderror"
                                   id="published_at" name="published_at"
                                   value="{{ old('published_at', $news->published_at->format('Y-m-d\TH:i')) }}" required>
                            @error('published_at')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                        </div>

                        <div class="form-group mb-3">
                            <div class="form-check">
                                <input type="checkbox" class="form-check-input" id="is_published" name="is_published"
                                       value="1" {{ old('is_published', $news->is_published) ? 'checked' : '' }}>
                                <label class="form-check-label" for="is_published">Publier</label>
                            </div>
                        </div>

                        <div class="form-group mb-3">
                            <button type="submit" class="btn btn-primary">Mettre à jour</button>
                            <a href="{{ route('public.news.index') }}" class="btn btn-secondary">Annuler</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script src="https://cdn.tiny.cloud/1/no-api-key/tinymce/5/tinymce.min.js"></script>
<script>
    tinymce.init({
        selector: '#content',
        height: 400,
        plugins: [
            'advlist autolink lists link image charmap print preview anchor',
            'searchreplace visualblocks code fullscreen',
            'insertdatetime media table paste code help wordcount'
        ],
        toolbar: 'undo redo | formatselect | bold italic backcolor | \
                alignleft aligncenter alignright alignjustify | \
                bullist numlist outdent indent | removeformat | help'
    });
</script>
@endpush
@endsection
