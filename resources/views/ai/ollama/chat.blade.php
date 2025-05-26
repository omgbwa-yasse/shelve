{{-- resources/views/ai/ollama/chat.blade.php --}}

@extends('layouts.app')

@section('title', 'Chat Ollama')

@section('content')
<div id="ollama-chat-app">
    <ollama-chat></ollama-chat>
</div>

@push('scripts')
<script src="{{ mix('js/ollama-chat.js') }}"></script>
@endpush
@endsection