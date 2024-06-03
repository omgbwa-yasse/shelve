@extends('layouts.app')

@section('content')
    <h1>Create New Mail Subject</h1>
    <form action="{{ route('mail_subjects.store') }}" method="POST">
        @csrf
        <label for="name">Name:</label>
        <input type="text" id="name" name="name" required>
        <button type="submit">Create</button>
    </form>
@endsection
