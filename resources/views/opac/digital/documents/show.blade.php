@extends('opac.layouts.app')

@section('title', $document->name . ' - OPAC')

@section('content')
<div class="container my-5">
    <nav aria-label="breadcrumb" class="mb-4">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('opac.index') }}">{{ __('Home') }}</a></li>
            <li class="breadcrumb-item"><a href="{{ route('opac.digital.folders.index') }}">{{ __('Digital Collections') }}</a></li>
            @if($document->folder)
                @foreach($document->folder->getAncestors()->reverse() as $ancestor)
                    <li class="breadcrumb-item"><a href="{{ route('opac.digital.folders.show', $ancestor->id) }}">{{ $ancestor->name }}</a></li>
                @endforeach
                <li class="breadcrumb-item"><a href="{{ route('opac.digital.folders.show', $document->folder->id) }}">{{ $document->folder->name }}</a></li>
            @endif
            <li class="breadcrumb-item active" aria-current="page">{{ $document->name }}</li>
        </ol>
    </nav>

    <div class="row">
        <div class="col-md-8">
            <div class="opac-card mb-4">
                <div class="card-body">
                    <h1 class="h3 mb-3">{{ $document->name }}</h1>

                    <div class="bg-light p-4 text-center mb-4 rounded">
                        @if(in_array(strtolower($document->extension), ['jpg', 'jpeg', 'png', 'gif']))
                            <img src="{{ route('opac.digital.documents.download', $document->id) }}" class="img-fluid" alt="{{ $document->name }}">
                        @elseif(strtolower($document->extension) === 'pdf')
                            <div class="ratio ratio-16x9">
                                <iframe src="{{ route('opac.digital.documents.download', $document->id) }}" allowfullscreen></iframe>
                            </div>
                        @else
                            <div class="py-5">
                                <i class="fas fa-file-alt fa-5x text-muted mb-3"></i>
                                <p>{{ __('Preview not available for this file type.') }}</p>
                                <a href="{{ route('opac.digital.documents.download', $document->id) }}" class="btn btn-primary">
                                    <i class="fas fa-download me-2"></i>{{ __('Download File') }}
                                </a>
                            </div>
                        @endif
                    </div>

                    <div class="mb-4">
                        <h5 class="border-bottom pb-2">{{ __('Description') }}</h5>
                        <p>{{ $document->description ?? __('No description available.') }}</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="opac-card mb-4">
                <div class="card-body">
                    <h5 class="card-title">{{ __('Document Info') }}</h5>
                    <ul class="list-group list-group-flush">
                        <li class="list-group-item d-flex justify-content-between align-items-center px-0">
                            {{ __('Type') }}
                            <span class="badge bg-secondary">{{ strtoupper($document->extension) }}</span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center px-0">
                            {{ __('Size') }}
                            <span>{{ $document->file_size_human }}</span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center px-0">
                            {{ __('Created') }}
                            <span>{{ $document->created_at->format('d/m/Y') }}</span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center px-0">
                            {{ __('Code') }}
                            <span class="badge bg-light text-dark border">{{ $document->code }}</span>
                        </li>
                    </ul>
                    <div class="d-grid gap-2 mt-3">
                        <a href="{{ route('opac.digital.documents.download', $document->id) }}" class="btn btn-primary">
                            <i class="fas fa-download me-2"></i>{{ __('Download') }}
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
