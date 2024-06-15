@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">Organisations</div>

                <div class="card-body">
                    @if (session('success'))
                        <div class="alert alert-success">
                            {{ session('success') }}
                        </div>
                    @endif

                    <table class="table">
                        <thead>
                            <tr>
                                <th>Code</th>
                                <th>Name</th>
                                <th>Description</th>
                                <th>Parent</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($organisations as $organisation)
                                <tr>
                                    <td>{{ $organisation->code }}</td>
                                    <td>{{ $organisation->name }}</td>
                                    <td>{{ $organisation->description }}</td>
                                    <td>{{ $organisation->parent ? $organisation->parent->name : 'None' }}</td>
                                    <td>
                                        <a href="{{ route('organisations.edit', $organisation->id) }}" class="btn btn-primary btn-sm">Edit</a>
                                        <form action="{{ route('organisations.destroy', $organisation->id) }}" method="POST" class="d-inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-danger btn-sm">Delete</button>
                                        </form>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>

                    <a href="{{ route('organisations.create') }}" class="btn btn-primary mt-3">Create Organisation</a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
