@extends('layouts.app')

@section('content')
    <div class="container">
        <h1>Edit Reservation</h1>

        @if ($errors->any())
            <div class="alert alert-danger">
                <strong>{{ __('Please correct the following errors:') }}</strong>
                <ul class="mb-0 mt-2">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form action="{{ route('communications.reservations.update', $reservation) }}" method="POST">
            @csrf
            @method('PUT')
            <div class="mb-3">
                <label for="code" class="form-label">Code</label>
                <input type="text" name="code" id="code" maxlength="10" class="form-control @error('code') is-invalid @enderror" value="{{ old('code', $reservation->code) }}" required>
                @error('code')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="mb-3">
                <label for="name" class="form-label">Objet</label>
                <input type="text" class="form-control @error('name') is-invalid @enderror" id="name" name="name" maxlength="200" value="{{ old('name', $reservation->name) }}" required>
                @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="mb-3">
                <label for="content" class="form-label">Description</label>
                <textarea class="form-control @error('content') is-invalid @enderror" id="content" name="content">{{ old('content', $reservation->content) }}</textarea>
                @error('content')<div class="invalid-feedback">{{ $message }}</div>@enderror
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
