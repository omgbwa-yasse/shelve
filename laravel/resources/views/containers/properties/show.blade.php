@extends('layouts.app')

@section('content')

<div class="container">
    <h1>Container Property Details</h1>
    <table class="table">
        <tbody>
            <tr>
                <th>Name</th>
                <td>{{ $containerProperty->name }}</td>
            </tr>
            <tr>
                <th>Width</th>
                <td>{{ $containerProperty->width }}</td>
            </tr>
            <tr>
                <th>Length</th>
                <td>{{ $containerProperty->length }}</td>
            </tr>
            <tr>
                <th>Depth</th>
                <td>{{ $containerProperty->depth }}</td>
            </tr>
            <tr>
                <th>Volumétrie</th>
                <td>{{ ($containerProperty->depth/100 * $containerProperty->length/100 * $containerProperty->width/100)*12 }} ml</td>
            </tr>
        </tbody>
    </table>
    <a href="{{ route('container-property.index') }}" class="btn btn-secondary btn-sm">Back</a>
    <a href="{{ route('container-property.edit', $containerProperty->id) }}" class="btn btn-warning btn-sm">Edit</a>
    <form action="{{ route('container-property.destroy', $containerProperty->id) }}" method="POST" style="display: inline-block;">
        @csrf
        @method('DELETE')
        <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure you want to delete this container property?')">Delete</button>
    </form>
</div>


@endsection
