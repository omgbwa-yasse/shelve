@extends('layouts.app')

@section('content')
<div class="container">
    <h1>Create Transaction</h1>
    <form action="{{ route('transactions.store') }}" method="POST">
        @csrf
        <!-- Add your form fields here -->
        <button type="submit" class="btn btn-primary">Create</button>
    </form>
</div>
@endsection
