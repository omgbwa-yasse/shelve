@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <h1 class="mb-3"><i class="bi bi-envelope"></i> Messagerie</h1>

    @include('mails.email._nav', ['folder' => 'inbox'])
    @include('mails.email._list')
</div>
@endsection
