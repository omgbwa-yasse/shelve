@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">Edit Record</div>

                <div class="card-body">
                    @if (session('success'))
                        <div class="alert alert-success" role="alert">
                            {{ session('success') }}
                        </div>
                    @endif

                    <form action="{{ route('records.update', $record->id) }}" method="POST">
                        @csrf
                        @method('PUT')

                        <div class="form-group">
                            <label for="reference">Reference</label>
                            <input type="text" class="form-control" id="reference" name="reference" value="{{ $record->reference }}" required>
                        </div>
                        <div class="form-group">
                            <label for="name">Name</label>
                            <input type="text" class="form-control" id="name" name="name" value="{{ $record->name }}" required>
                        </div>
                        <div class="form-group">
                            <label for="date_format">Date Format</label>
                            <input type="text" class="form-control" id="date_format" name="date_format" value="{{ $record->date_format }}" required>
                        </div>
                        <div class="form-group">
                            <label for="date_start">Date Start</label>
                            <input type="date" class="form-control" id="date_start" name="date_start" value="{{ $record->date_start }}" required>
                        </div>
                        <div class="form-group">
                            <label for="date_end">Date End</label>
                            <input type="date" class="form-control" id="date_end" name="date_end" value="{{ $record->date_end }}" required>
                        </div>
                        <div class="form-group">
                            <label for="date_exact">Date Exact</label>
                            <input type="date" class="form-control" id="date_exact" name="date_exact" value="{{ $record->date_exact }}" required>
                        </div>
                        <div class="form-group">
                            <label for="description">Description</label>
                            <textarea class="form-control" id="description" name="description" required>{{ $record->description }}</textarea>
                        </div>
                        <div class="form-group">
                            <label for="level_id">Level</label>
                            <select class="form-control" id="level_id" name="level_id" required>
                                @foreach ($levels as $level)
                                    <option value="{{ $level->id }}" {{ $level->id == $record->level_id ? 'selected' : '' }}>{{ $level->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="status_id">Status</label>
                            <select class="form-control" id="status_id" name="status_id" required>
                                @foreach ($statuses as $status)
                                    <option value="{{ $status->id }}" {{ $status->id == $record->status_id ? 'selected' : '' }}>{{ $status->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="support_id">Support</label>
                            <select class="form-control" id="support_id" name="support_id" required>
                                @foreach ($supports as $support)
                                    <option value="{{ $support->id }}" {{ $support->id == $record->support_id ? 'selected' : '' }}>{{ $support->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="classification_id">Classification</label>
                            <select class="form-control" id="classification_id" name="classification_id" required>
                                @foreach ($classifications as $classification)
                                    <option value="{{ $classification->id }}" {{ $classification->id == $record->classification_id ? 'selected' : '' }}>{{ $classification->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="parent_id">Parent</label>
                            <select class="form-control" id="parent_id" name="parent_id">
                                <option value="">None</option>
                                @foreach ($records as $record)
                                    <option value="{{ $record->id }}" {{ $record->id == $record->parent_id ? 'selected' : '' }}>{{ $record->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="container_id">Container</label>
                            <select class="form-control" id="container_id" name="container_id" required>
                                @foreach ($containers as $container)
                                    <option value="{{ $container->id }}" {{ $container->id == $record->container_id ? 'selected' : '' }}>{{ $container->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="transfer_id">Transfer</label>
                            <select class="form-control" id="transfer_id" name="transfer_id">
                                <option value="">None</option>
                                @foreach ($transfers as $transfer)
                                    <option value="{{ $transfer->id }}" {{ $transfer->id == $record->transfer_id ? 'selected' : '' }}>{{ $transfer->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="user_id">User</label>
                            <select class="form-control" id="user_id" name="user_id" required>
                                @foreach ($users as $user)
                                    <option value="{{ $user->id }}" {{ $user->id == $record->user_id ? 'selected' : '' }}>{{ $user->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <button type="submit" class="btn btn-primary">Update</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
