@extends('layouts.app')

@section('content')
    <div class="container">
        <h1>Edit Reservation</h1>
        <form action="{{ route('reservations.update', $reservation->id) }}" method="POST">
            @csrf
            @method('PUT')
            <div class="mb-3">
                <label for="code" class="form-label">Code</label>
                <input type="text" name="code" id="code" class="form-control" value="{{ $reservation->code }}" required>
            </div>
            <div class="mb-3">
                <label for="operator_id" class="form-label">Operator</label>
                <select name="operator_id" id="operator_id" class="form-select" required>
                    @foreach ($operators as $operator)
                        <option value="{{ $operator->id }}" {{ $operator->id == $reservation->operator_id ? 'selected' : '' }}>{{ $operator->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="mb-3">
                <label for="operator_organisation_id" class="form-label">Operator Organisation</label>
                <select name="operator_organisation_id" id="operator_organisation_id" class="form-select" required>
                    @foreach ($organisations as $organisation)
                        <option value="{{ $organisation->id }}" {{ $organisation->id == $reservation->operator_organisation_id ? 'selected' : '' }}>{{ $organisation->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="mb-3">
                <label for="user_id" class="form-label">User</label>
                <select name="user_id" id="user_id" class="form-select" required>
                    @foreach ($users as $user)
                        <option value="{{ $user->id }}" {{ $user->id == $reservation->user_id ? 'selected' : '' }}>{{ $user->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="mb-3">
                <label for="user_organisation_id" class="form-label">User Organisation</label>
                <select name="user_organisation_id" id="user_organisation_id" class="form-select" required>
                    @foreach ($organisations as $organisation)
                        <option value="{{ $organisation->id }}" {{ $organisation->id == $reservation->user_organisation_id ? 'selected' : '' }}>{{ $organisation->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="mb-3">
                <label for="status_id" class="form-label">Status</label>
                <select name="status_id" id="status_id" class="form-select" required>
                    @foreach ($statuses as $status)
                        <option value="{{ $status->id }}" {{ $status->id == $reservation->status_id ? 'selected' : '' }}>{{ $status->name }}</option>
                    @endforeach
                </select>
            </div>
            <button type="submit" class="btn btn-primary">Update</button>
        </form>
    </div>
@endsection
