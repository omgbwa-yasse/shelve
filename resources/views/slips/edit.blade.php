@extends('layouts.app')

@section('content')
    <div class="container">
        <h1>Modifier un versement</h1>

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

        <form action="{{ route('slips.update', $slip->id) }}" method="POST">
            @csrf
            @method('PUT')
            <div class="mb-3">
                <label for="code" class="form-label">Code</label>
                <input type="text" class="form-control" id="code" name="code" value="{{ $slip->code }}" required maxlength="20">
            </div>
            <div class="mb-3">
                <label for="name" class="form-label">Name</label>
                <input type="text" class="form-control" id="name" name="name" value="{{ $slip->name }}" required maxlength="200">
            </div>
            <div class="mb-3">
                <label for="description" class="form-label">Description</label>
                <textarea class="form-control" id="description" name="description">{{ $slip->description }}</textarea>
            </div>
            <div class="mb-3">
                <label for="officer_organisation_id" class="form-label">Officer Organisation</label>
                <select class="form-select" id="officer_organisation_id" name="officer_organisation_id" required>
                    @foreach ($organisations as $organisation)
                        <option value="{{ $organisation->id }}" {{ $organisation->id == $slip->officer_organisation_id ? 'selected' : '' }}>{{ $organisation->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="mb-3">
                <label for="user_organisation_id" class="form-label">User Organisation</label>
                <select class="form-select" id="user_organisation_id" name="user_organisation_id" required>
                    @foreach ($organisations as $organisation)
                        <option value="{{ $organisation->id }}" {{ $organisation->id == $slip->user_organisation_id ? 'selected' : '' }}>{{ $organisation->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="mb-3">
                <label for="user_id" class="form-label">User</label>
                <select class="form-select" id="user_id" name="user_id">
                    <option value="">None</option>
                    @foreach ($users as $user)
                        <option value="{{ $user->id }}" {{ $user->id == $slip->user_id ? 'selected' : '' }}>{{ $user->name }}</option>
                    @endforeach
                </select>
            </div>
            <button type="submit" class="btn btn-primary">{{ __('Update') }}</button>
            <a href="{{ route('slips.show', $slip) }}" class="btn btn-outline-secondary">{{ __('Cancel') }}</a>
        </form>
    </div>
@endsection
