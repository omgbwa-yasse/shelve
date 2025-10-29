@extends('layouts.app')

@section('title', __('AI Search Assistant'))

<!-- Autoriser l'accès au microphone pour la reconnaissance vocale -->
<meta http-equiv="Permissions-Policy" content="microphone=(self)">

@section('content')
<div class="card-header bg-primary text-white">
    <h4 class="mb-0">
        <i class="bi bi-robot me-2"></i>
        {{ __('AI Search Assistant') }}
    </h4>
</div>
<div class="card-body">
    <!-- Sélecteur de type de recherche et actions -->
    <div class="row mb-4">
        <div class="col-md-8">
            @include('ai-search.components.search-type-selector', ['defaultType' => 'records'])
        </div>
        <div class="col-md-4">
            @include('ai-search.partials.header-actions')
        </div>
    </div>

    <!-- Interface de chat -->
    @include('ai-search.components.chat-interface')
</div>
@endsection

@section('styles')
<link rel="stylesheet" href="{{ asset('css/ai-search/chat.css') }}">
<link rel="stylesheet" href="{{ asset('css/ai-search/results.css') }}">
<link rel="stylesheet" href="{{ asset('css/ai-search/voice.css') }}">
<link rel="stylesheet" href="{{ asset('css/ai-search/animations.css') }}">
@endsection

@section('scripts')
<script src="{{ asset('js/ai-search/chat.js') }}"></script>
<script src="{{ asset('js/ai-search/voice.js') }}"></script>
@endsection
