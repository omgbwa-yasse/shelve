@extends('layouts.app')

@section('content')
    <div class="container">
        <h1>User Organisation Role Details</h1>
        <table class="table">
            <tbody>
                <tr>
                    <th>User</th>
                    <td>{{ __('User Name') }}: {{ $userOrganisationRole->user->name }}</td>
                    <td>{{ __('Organisation') }}: {{ $userOrganisationRole->organisation->name }}</td>
                    <td>{{ __('Role') }}: {{ $userOrganisationRole->role->name }}</td>
                    <td>{{ __('Creator') }}: {{ $userOrganisationRole->creator->name }}</td>
                </tr>
            </tbody>
        </table>
        <a href="{{ route('user-organisation-role.index') }}" class="btn btn-secondary">{{ __('Back') }}</a>
    </div>
@endsection

