@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">Create User Organisation</div>

                <div class="card-body">
                    <form action="{{ route('user-organisations.store') }}" method="POST">
                        @csrf

                        <div class="form-group">
                            <label for="user_id">User</label>
                            <select name="user_id" id="user_id" class="form-select">
                                @foreach (App\Models\User::all() as $user)
                                    <option value="{{ $user->id }}">{{ $user->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="form-group">
                            <label for="organisation_id">Organisation</label>
                            <select name="organisation_id" id="organisation_id" class="form-select">
                                @foreach (App\Models\Organisation::all() as $organisation)
                                    <option value="{{ $organisation->id }}">{{ $organisation->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="form-group">
                            <label for="active">Active</label>
                            <input type="checkbox" name="active" id="active">
                        </div>

                        <button type="submit" class="btn btn-primary">Create</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
