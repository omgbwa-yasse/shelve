@extends('layouts.app')

@section('content')
    <div class="container">
        <h1>Create New Reservation</h1>
        <form action="{{ route('reservations.store') }}" method="POST">
            @csrf
            <div class="row">
                <div class="col-md-6 mb-3">
                <label for="code" class="form-label">Code</label>
                <input type="text" name="code" id="code" class="form-control" required>
            </div>

            <div class="col-md-6 mb-3">
                <label for="status_id" class="form-label">Status</label>
                <select name="status_id" id="status_id" class="form-select" required>
                    @foreach ($statuses as $status)
                        <option value="{{ $status->id }}">{{ $status->name }}</option>
                    @endforeach
                </select>
            </div>
        </div>


            <div class="row">
            <div class="col-md-6 mb-3">
                <label for="operator_id" class="form-label">Operator</label>
                <select name="operator_id" id="operator_id" class="form-select" required>
                    @foreach ($operators as $operator)
                        <option value="{{ $operator->id }}">{{ $operator->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-6 mb-3">
                <label for="operator_organisation_id" class="form-label">Operator Organisation</label>
                <select name="operator_organisation_id" id="operator_organisation_id" class="form-select" required>
                    @foreach ($organisations as $organisation)
                        <option value="{{ $organisation->id }}">{{ $organisation->name }}</option>
                    @endforeach
                </select>
            </div>
        </div>
        <div class="row">
            <div class="col-md-6 mb-3">
                <label for="user_id" class="form-label">User</label>
                <select name="user_id" id="user_id" class="form-select" required>
                    @foreach ($users as $user)
                        <option value="{{ $user->id }}">{{ $user->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-6 mb-3">
                <label for="user_organisation_id" class="form-label">User Organisation</label>
                <select name="user_organisation_id" id="user_organisation_id" class="form-select" required>
                    @foreach ($organisations as $organisation)
                        <option value="{{ $organisation->id }}">{{ $organisation->name }}</option>
                    @endforeach
                </select>
            </div>
        </div>

            <button type="submit" class="btn btn-primary">Create</button>
        </form>
    </div>
@endsection
