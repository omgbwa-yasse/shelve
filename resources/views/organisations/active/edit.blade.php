@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">{{ __('Edit Organisation Active') }}</div>
                <div class="card-body">
                    <form method="POST" action="{{ route('organisation-active.update', $organisationActive) }}">
                        @csrf
                        @method('PUT')
                        <div class="form-group">
                            <label for="organisation_id">Organisation</label>
                            <select name="organisation_id" id="organisation_id" class="form-control" required>
                                @foreach ($organisations as $organisation)
                                    <option value="{{ $organisation->id }}" {{ $organisationActive->organisation_id == $organisation->id ? 'selected' : '' }}>
                                        {{ $organisation->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="user_id">User</label>
                            <select name="user_id" id="user_id" class="form-control" required>
                                @foreach ($users as $user)
                                    <option value="{{ $user->id }}" {{ $organisationActive->user_id == $user->id ? 'selected' : '' }}>
                                        {{ $user->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <button type="submit" class="btn btn-primary">{{ __('Update') }}</button>
                    </form>

                </div>
            </div>
        </div>
    </div>
</div>
@endsection
