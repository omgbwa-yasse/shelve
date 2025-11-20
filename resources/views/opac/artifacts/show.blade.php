@extends('opac.layouts.app')

@section('title', $artifact->name . ' - OPAC')

@section('content')
<div class="container my-5">
    <nav aria-label="breadcrumb" class="mb-4">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('opac.index') }}">{{ __('Home') }}</a></li>
            <li class="breadcrumb-item"><a href="{{ route('opac.artifacts.index') }}">{{ __('Artifacts') }}</a></li>
            <li class="breadcrumb-item active" aria-current="page">{{ $artifact->name }}</li>
        </ol>
    </nav>

    <div class="row">
        <div class="col-md-8">
            <div class="opac-card mb-4">
                <div class="card-body">
                    <h1 class="h2 mb-3">{{ $artifact->name }}</h1>

                    @if($artifact->attachments->where('type', 'image')->count() > 0)
                        <div id="artifactCarousel" class="carousel slide mb-4" data-bs-ride="carousel">
                            <div class="carousel-inner rounded">
                                @foreach($artifact->attachments->where('type', 'image') as $key => $image)
                                    <div class="carousel-item {{ $loop->first ? 'active' : '' }}">
                                        <img src="{{ Storage::url($image->path) }}" class="d-block w-100" alt="{{ $artifact->name }}" style="max-height: 500px; object-fit: contain; background-color: #f8f9fa;">
                                    </div>
                                @endforeach
                            </div>
                            @if($artifact->attachments->where('type', 'image')->count() > 1)
                                <button class="carousel-control-prev" type="button" data-bs-target="#artifactCarousel" data-bs-slide="prev">
                                    <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                                    <span class="visually-hidden">Previous</span>
                                </button>
                                <button class="carousel-control-next" type="button" data-bs-target="#artifactCarousel" data-bs-slide="next">
                                    <span class="carousel-control-next-icon" aria-hidden="true"></span>
                                    <span class="visually-hidden">Next</span>
                                </button>
                            @endif
                        </div>
                    @endif

                    <div class="mb-4">
                        <h5 class="border-bottom pb-2">{{ __('Description') }}</h5>
                        <p>{{ $artifact->description }}</p>
                    </div>

                    <div class="row mb-4">
                        <div class="col-md-6">
                            <h5 class="border-bottom pb-2">{{ __('Details') }}</h5>
                            <table class="table table-borderless">
                                <tbody>
                                    <tr>
                                        <th scope="row" class="w-25">{{ __('Category') }}</th>
                                        <td>{{ $artifact->category }}</td>
                                    </tr>
                                    <tr>
                                        <th scope="row">{{ __('Material') }}</th>
                                        <td>{{ $artifact->material }}</td>
                                    </tr>
                                    <tr>
                                        <th scope="row">{{ __('Technique') }}</th>
                                        <td>{{ $artifact->technique }}</td>
                                    </tr>
                                    <tr>
                                        <th scope="row">{{ __('Dimensions') }}</th>
                                        <td>{{ $artifact->dimensions }}</td>
                                    </tr>
                                    <tr>
                                        <th scope="row">{{ __('Period') }}</th>
                                        <td>{{ $artifact->period }}</td>
                                    </tr>
                                    <tr>
                                        <th scope="row">{{ __('Origin') }}</th>
                                        <td>{{ $artifact->origin }}</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                        <div class="col-md-6">
                            <h5 class="border-bottom pb-2">{{ __('Acquisition') }}</h5>
                            <table class="table table-borderless">
                                <tbody>
                                    <tr>
                                        <th scope="row" class="w-25">{{ __('Date') }}</th>
                                        <td>{{ $artifact->acquisition_date ? $artifact->acquisition_date->format('d/m/Y') : '-' }}</td>
                                    </tr>
                                    <tr>
                                        <th scope="row">{{ __('Method') }}</th>
                                        <td>{{ $artifact->acquisition_method }}</td>
                                    </tr>
                                    <tr>
                                        <th scope="row">{{ __('Source') }}</th>
                                        <td>{{ $artifact->acquisition_source }}</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="opac-card mb-4">
                <div class="card-body">
                    <h5 class="card-title">{{ __('Information') }}</h5>
                    <ul class="list-group list-group-flush">
                        <li class="list-group-item d-flex justify-content-between align-items-center px-0">
                            {{ __('Code') }}
                            <span class="badge bg-primary rounded-pill">{{ $artifact->code }}</span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center px-0">
                            {{ __('Status') }}
                            <span class="badge bg-success">{{ __('Available') }}</span>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
