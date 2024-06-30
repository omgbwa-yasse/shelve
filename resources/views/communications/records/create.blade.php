@extends('layouts.app')

@section('content')
    <div class="container">
        <h1>Create Communication</h1>
        <form action="{{ route('transactions.records.store', $communication) }}" method="POST">
            @csrf
            <div class="mb-3">
                <label for="code" class="form-label">Code</label>
                <input type="text" class="form-control" id="code" name="code" required>
            </div>
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label for="operator_id" class="form-label">Operator</label>
                    <select class="form-select" id="operator_id" name="operator_id" required>
                        @foreach ($users as $user)
                            <option value="{{ $user->id }}">{{ $user->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-6 mb-3">
                    <label for="operator_organisation_id" class="form-label">Operator Organisation</label>
                    <select class="form-select" id="operator_organisation_id" name="operator_organisation_id" required>
                        @foreach ($organisations as $organisation)
                            <option value="{{ $organisation->id }}">{{ $organisation->name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label for="user_id" class="form-label">User</label>
                    <select class="form-select" id="user_id" name="user_id" required>
                        @foreach ($users as $user)
                            <option value="{{ $user->id }}">{{ $user->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-6 mb-3">
                    <label for="user_organisation_id" class="form-label">User Organisation</label>
                    <select class="form-select" id="user_organisation_id" name="user_organisation_id" required>
                        @foreach ($organisations as $organisation)
                            <option value="{{ $organisation->id }}">{{ $organisation->name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="row">
                <div class="col-md-4 mb-3">
                    <label for="return_date" class="form-label">Return Date</label>
                    <input type="datetime-local" class="form-control" id="return_date" name="return_date" required>
                </div>
                <div class="col-md-4 mb-3">
                    <label for="return_effective" class="form-label">Return Effective</label>
                    <input type="date" class="form-control" id="return_effective" name="return_effective">
                </div>
                <div class="col-md-4 mb-3">
                    <label for="status_id" class="form-label">Status</label>
                    <select class="form-select" id="status_id" name="status_id" required>
                        @foreach ($statuses as $status)
                            <option value="{{ $status->id }}">{{ $status->name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <button type="submit" class="btn btn-primary">Create</button>
        </form>
    </div>
@endsection
