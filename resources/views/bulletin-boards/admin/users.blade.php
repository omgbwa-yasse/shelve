<!-- resources/views/bulletin-boards/admin/users.blade.php -->

@extends('layouts.app')

@section('content')
    <div class="container">
        <h1>Gestion des Utilisateurs</h1>
        <table class="table">
            <thead>
            <tr>
                <th>Nom</th>
                <th>Email</th>
                <th>Rôles</th>
                <th>Actions</th>
            </tr>
            </thead>
            <tbody>
            @foreach($users as $user)
                <tr>
                    <td>{{ $user->name }}</td>
                    <td>{{ $user->email }}</td>
                    <td>
                        @foreach($user->roles as $role)
                            {{ $role->name }}
                        @endforeach
                    </td>
                    <td>
                        <form action="{{ route('bulletin-boards.admin.updatePermissions', $user) }}" method="POST">
                            @csrf
                            <div class="form-group">
                                <select name="roles[]" class="form-control" multiple>
                                    @foreach(\App\Models\Role::all() as $role)
                                        <option value="{{ $role->id }}" {{ $user->roles->contains($role) ? 'selected' : '' }}>
                                            {{ $role->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <button type="submit" class="btn btn-success">Mettre à jour</button>
                        </form>
                    </td>
                </tr>
            @endforeach
            </tbody>
        </table>
        {{ $users->links() }}
    </div>
@endsection
