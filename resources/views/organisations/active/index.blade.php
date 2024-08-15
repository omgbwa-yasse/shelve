@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">{{ __('Organisations Active') }}</div>

                <div class="card-body">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Organisation</th>
                                <th>User</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($organisationActives as $organisationActive)
                            <tr>
                                <td>{{ $organisationActive->organisation->name }}</td>
                                <td>{{ $organisationActive->user->name }}</td>
                                <td>
                                    <a href="{{ route('organisation-active.show', $organisationActive) }}" class="btn btn-sm btn-primary">Show</a>
                                    <a href="{{ route('organisation-active.edit', $organisationActive) }}" class="btn btn-sm btn-secondary">Edit</a>
                                    <form method="POST" action="{{ route('organisation-active.destroy', $organisationActive) }}" style="display: inline-block;">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure you want to delete this record?')">Delete</button>
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
