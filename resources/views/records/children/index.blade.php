@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">Children of Record {{ $record->reference }}</div>

                <div class="card-body">
                    <a href="{{ route('records.children.create', $record->id) }}" class="btn btn-primary mb-3">Add Child Record</a>

                    <table class="table table-bordered">
                        <thead>
                            <tr>
                                <th>Reference</th>
                                <th>Name</th>
                                <!-- Add more columns as needed -->
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($record->children as $child)
                                <tr>
                                    <td>{{ $child->reference }}</td>
                                    <td>{{ $child->name }}</td>
                                    <!-- Add more columns as needed -->
                                    <td>
                                        <a href="{{ route('records.children.edit', [$record->id, $child->id]) }}" class="btn btn-primary">Edit</a>
                                        <form action="{{ route('records.children.destroy', [$record->id, $child->id]) }}" method="POST" style="display: inline;">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-danger">Delete</button>
                                        </form>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
