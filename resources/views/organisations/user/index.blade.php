@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">User Organisations</div>

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
                                <th>Organisation</th>
                                <th>Utilisateurs</th>
                                <th>Active</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($userOrganisations as $userOrganisation)
                                <tr>
                                    <td>{{ $userOrganisation->organisation->code }}</td>
                                    <td>{{ $userOrganisation->organisation->name }}</td>
                                    <td>
                                        {{ $userOrganisation->user->name }}
                                    </td>
                                    <td>{{ $userOrganisation->active ? 'Yes' : 'No' }}</td>
                                    <td>
                                        <a href="{{ route('user-organisation.edit', $userOrganisation->organisation->id) }}" class="btn btn-primary btn-sm" role="button">Modifier</a>
                                        <form action="{{ route('user-organisation.destroy', $userOrganisation->organisation->id) }}" method="POST">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-danger btn-sm">Delete</button>
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
