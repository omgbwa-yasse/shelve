
@extends('layouts.app')

@section('content')
    <h1>Mail Subject Details</h1>
    <p>ID: {{ $mailSubject->id }}</p>
    <p>Name: {{ $mailSubject->name }}</p>
    <a href="{{ route('mail_subjects.index') }}">Back</a>
@endsection
