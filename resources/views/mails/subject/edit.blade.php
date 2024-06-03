
@extends('layouts.app')
@section('content')
    <h1>Edit Mail Subject</h1>
    <form action="{{ route('mail_subjects.update', $mailSubject->id) }}" method="POST">
        @csrf
        @method('PUT')
        <label for="name">Name:</label>
        <input type="text" id="name" name="name" value="{{ $mailSubject->name }}" required>
        <button type="submit">Update</button>
    </form>
@endsection
