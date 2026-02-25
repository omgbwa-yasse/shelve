@extends('opac.layouts.app')

@section('title', $folder->name . ' - OPAC')

@section('content')
<div class="container my-5">
    <nav aria-label="breadcrumb" class="mb-4">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('opac.index') }}">{{ __('Home') }}</a></li>
            <li class="breadcrumb-item"><a href="{{ route('opac.digital.folders.index') }}">{{ __('Digital Collections') }}</a></li>
            @foreach($folder->getAncestors()->reverse() as $ancestor)
                <li class="breadcrumb-item"><a href="{{ route('opac.digital.folders.show', $ancestor->id) }}">{{ $ancestor->name }}</a></li>
            @endforeach
            <li class="breadcrumb-item active" aria-current="page">{{ $folder->name }}</li>
        </ol>
    </nav>

    <div class="row">
        <div class="col-12">
            <div class="opac-card mb-4">
                <div class="card-body">
                    <div class="d-flex align-items-center mb-3">
                        <i class="fas fa-folder-open fa-3x text-warning me-3"></i>
                        <div>
                            <h1 class="h3 mb-1">{{ $folder->name }}</h1>
                            <p class="text-muted mb-0">{{ $folder->description }}</p>
                        </div>
                    </div>
                </div>
            </div>

            @if($folder->children->count() > 0)
                <h4 class="mb-3">{{ __('Subfolders') }}</h4>
                <div class="row mb-4">
                    @foreach($folder->children as $child)
                        <div class="col-md-3 mb-3">
                            <div class="card h-100 opac-card">
                                <div class="card-body d-flex align-items-center">
                                    <i class="fas fa-folder fa-2x text-warning me-3"></i>
                                    <div>
                                        <h6 class="card-title mb-0">
                                            <a href="{{ route('opac.digital.folders.show', $child->id) }}" class="text-decoration-none text-dark stretched-link">
                                                {{ $child->name }}
                                            </a>
                                        </h6>
                                        <small class="text-muted">{{ $child->documents_count }} {{ __('items') }}</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif

            @if($folder->documents->count() > 0)
                <h4 class="mb-3">{{ __('Documents') }}</h4>
                <div class="opac-card">
                    <div class="list-group list-group-flush">
                        @foreach($folder->documents as $document)
                            <div class="list-group-item list-group-item-action p-3">
                                <div class="d-flex w-100 justify-content-between align-items-center">
                                    <div class="d-flex align-items-center">
                                        <div class="me-3 text-center" style="width: 40px;">
                                            @if(in_array(strtolower($document->extension), ['pdf']))
                                                <i class="fas fa-file-pdf fa-2x text-danger"></i>
                                            @elseif(in_array(strtolower($document->extension), ['jpg', 'jpeg', 'png', 'gif']))
                                                <i class="fas fa-file-image fa-2x text-primary"></i>
                                            @elseif(in_array(strtolower($document->extension), ['doc', 'docx']))
                                                <i class="fas fa-file-word fa-2x text-primary"></i>
                                            @else
                                                <i class="fas fa-file-alt fa-2x text-secondary"></i>
                                            @endif
                                        </div>
                                        <div>
                                            <h5 class="mb-1">
                                                <a href="{{ route('opac.digital.documents.show', $document->id) }}" class="text-decoration-none text-dark">
                                                    {{ $document->name }}
                                                </a>
                                            </h5>
                                            <small class="text-muted">
                                                {{ strtoupper($document->extension) }} • {{ $document->file_size_human }} • {{ $document->created_at->format('d/m/Y') }}
                                            </small>
                                        </div>
                                    </div>
                                    <a href="{{ route('opac.digital.documents.download', $document->id) }}" class="btn btn-sm btn-outline-primary">
                                        <i class="fas fa-download"></i>
                                    </a>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @elseif($folder->children->count() === 0)
                <div class="alert alert-info">
                    {{ __('This folder is empty.') }}
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
