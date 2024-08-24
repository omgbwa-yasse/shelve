@extends('layouts.app')

@section('content')
    <div class="container">
        <h1>Create New Transferring Slip</h1>
        <form action="{{ route('slips.store') }}" method="POST">
            @csrf
            <div class="d-flex mb-3">
            <div class="flex-grow-1 me-3">
                <label for="code" class="form-label">Code</label>
                <input type="text" class="form-control" id="code" name="code" required maxlength="20">
            </div>
            <div class="flex-grow-1">
                <label for="slip_status_id" class="form-label">Transferring Status</label>
                <select class="form-select" id="slip_status_id" name="slip_status_id" required>
                    @foreach ($slipStatuses as $status)
                        <option value="{{ $status->id }}">{{ $status->name }}</option>
                    @endforeach
                </select>
                </div>
            </div>
            <div class="mb-3">
                <label for="name" class="form-label">Name</label>
                <input type="text" class="form-control" id="name" name="name" required maxlength="200">
            </div>
            <div class="mb-3">
                <label for="description" class="form-label">Description</label>
                <textarea class="form-control" id="description" name="description"></textarea>
            </div>
            <div class="d-flex mb-3">
                <div class="flex-grow-1 me-3">
                    <label for="officer_organisation_id" class="form-label">Officer Organisation</label>
                    <select class="form-select" id="officer_organisation_id" name="officer_organisation_id" required>
                        @foreach ($organisations as $organisation)
                            <option value="{{ $organisation->id }}">{{ $organisation->name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="d-flex mb-3">
                <div class="flex-grow-1 me-3">
                    <label for="user_organisation_id" class="form-label">User Organisation</label>
                    <select class="form-select" id="user_organisation_id" name="user_organisation_id" required>
                        @foreach ($organisations as $organisation)
                            <option value="{{ $organisation->id }}">{{ $organisation->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="flex-grow-1">
                    <label for="user_id" class="form-label">User</label>
                    <select class="form-select" id="user_id" name="user_id">
                        <option value="">None</option>
                        @foreach ($users as $user)
                            <option value="{{ $user->id }}">{{ $user->name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <button type="submit" class="btn btn-primary">Create</button>
            <button type="reset" class="btn btn-danger">Annuler</button>
        </form>
    </div>
@endsection
