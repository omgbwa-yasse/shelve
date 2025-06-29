@extends('layouts.app')

@section('content')
    <div class="container">
        <h1>Edit Reservation</h1>
        <form action="{{ route('communications.reservations.update', $reservation) }}" method="POST">
            @csrf
            @method('PUT')
            <div class="mb-3">
                <label for="code" class="form-label">Code</label>
                <input type="text" name="code" id="code" class="form-control" value="{{ $reservation->code }}" required>
            </div>
            <div class="mb-3">
                <label for="name" class="form-label">Objet</label>
                <input type="text" class="form-control" id="name" name="name" value="{{ $reservation->name }}" required>
            </div>
            <div class="mb-3">
                <label for="content" class="form-label">Description</label>
                <textarea class="form-control" id="content" name="content" value="{{ $reservation->content }}"></textarea>
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
                <label for="status" class="form-label">Statut</label>
                <select name="status" id="status" class="form-select" required>
                    @foreach ($statuses as $status)
                        <option value="{{ $status['value'] }}" {{ (old('status', $reservation->status->value ?? null) === $status['value']) ? 'selected' : '' }}>{{ $status['label'] }}</option>
                    @endforeach
                </select>
            </div>
            <button type="submit" class="btn btn-primary">Update</button>
        </form>
    </div>
@endsection
