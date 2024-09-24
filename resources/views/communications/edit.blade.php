@extends('layouts.app')

@section('content')
    <div class="container">
        <h1>Update Communication</h1>
        <form action="{{ route('transactions.update', $communication->id) }}" method="POST">
            @csrf
            @method('PUT')
            <div class="mb-3">
                <label for="code" class="form-label">Code</label>
                <input type="text" class="form-control" id="code" name="code" value="{{ $communication->code }}" required>
            </div>
            <div class="mb-3">
                <label for="name" class="form-label">Objet  </label>
                <input type="text" class="form-control" id="name" name="name" value="{{ $communication->name }}" required>
            </div>
            <div class="mb-3">
                <label for="content" class="form-label"> Description  </label>
                <input type="text" class="form-control" id="content" name="content" value="{{ $communication->content }}" required>
            </div>
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label for="user_id" class="form-label">User</label>
                    <select class="form-select" id="user_id" name="user_id" required>
                        @foreach ($users as $user)
                            <option value="{{ $user->id }}" {{ $communication->user_id == $user->id ? 'selected' : '' }}>{{ $user->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-6 mb-3">
                    <label for="user_organisation_id" class="form-label">User organisation</label>
                    <select class="form-select" id="user_organisation_id" name="user_organisation_id" required>
                        @foreach ($organisations as $organisation)
                            <option value="{{ $organisation->id }}" {{ $communication->user_organisation_id == $organisation->id ? 'selected' : '' }}>{{ $organisation->name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="row">
                <div class="col-md-4 mb-3">
                    <label for="return_date" class="form-label">Return Date</label>
                    <input type="datetime-local" class="form-control" id="return_date" name="return_date" value="{{ $communication->return_date }}" required>
                </div>
                <div class="col-md-4 mb-3">
                    <label for="return_effective" class="form-label">Return Effective</label>
                    <input type="date" class="form-control" id="return_effective" name="return_effective" value="{{ $communication->return_effective }}">
                </div>
                <div class="col-md-4 mb-3">
                    <label for="status_id" class="form-label">Status</label>
                    <select class="form-select" id="status_id" name="status_id" required>
                        @foreach ($statuses as $status)
                            <option value="{{ $status->id }}" {{ $communication->status_id == $status->id ? 'selected' : '' }}>{{ $status->name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <button type="submit" class="btn btn-primary">Update</button>
        </form>
    </div>
@endsection
