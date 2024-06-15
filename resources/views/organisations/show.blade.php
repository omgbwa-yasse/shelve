@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">{{ $organisation->name }}</div>

                <div class="card-body">
                    <p><strong>Code:</strong> {{ $organisation->code }}</p>
                    <p><strong>Description:</strong> {{ $organisation->description }}</p>
                    <p><strong>Parent:</strong> {{ $organisation->parent ? $organisation->parent->name : 'None' }}</p>

                    <a href="{{ route('organisations.edit', $organisation->id) }}" class="btn btn-primary mt-3">Edit</a>
                    <form action="{{ route('organisations.destroy', $organisation->id) }}" method="POST" class="d-inline mt-3">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger">Delete</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
