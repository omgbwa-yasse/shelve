@extends('layouts.app')

@section('content')
    <div class="container">
        <h1>Edit Slip Record for Slip {{ $slip->name }}</h1>
        <form action="{{ route('slips.records.update', [$slip, $slipRecord]) }}" method="POST">
            @csrf
            @method('PUT')
            <div class="mb-3">
                <label for="code" class="form-label">Code</label>
                <input type="text" class="form-control" id="code" name="code" value="{{ $slipRecord->code }}" required maxlength="10">
            </div>
            <div class="mb-3">
                <label for="name" class="form-label">Name</label>
                <input type="text" class="form-control" id="name" name="name" value="{{ $slipRecord->name }}" required>
            </div>
            <div class="mb-3">
                <label for="date_format" class="form-label">Date Format</label>
                <input type="text" class="form-control" id="date_format" name="date_format" value="{{ $slipRecord->date_format }}" required maxlength="1">
            </div>
            <div class="mb-3">
                <label for="date_start" class="form-label">Date Start</label>
                <input type="text" class="form-control" id="date_start" name="date_start" value="{{ $slipRecord->date_start }}" maxlength="10">
            </div>
            <div class="mb-3">
                <label for="date_end" class="form-label">Date End</label>
                <input type="text" class="form-control" id="date_end" name="date_end" value="{{ $slipRecord->date_end }}" maxlength="10">
            </div>
            <div class="mb-3">
                <label for="date_exact" class="form-label">Date Exact</label>
                <input type="date" class="form-control" id="date_exact" name="date_exact" value="{{ $slipRecord->date_exact }}">
            </div>
            <div class="mb-3">
                <label for="content" class="form-label">Content</label>
                <textarea class="form-control" id="content" name="content">{{ $slipRecord->content }}</textarea>
            </div>
            <div class="mb-3">
                <label for="level_id" class="form-label">Level ID</label>
                <input type="number" class="form-control" id="level_id" name="level_id" value="{{ $slipRecord->level_id }}" required>
            </div>
            <div class="mb-3">
                <label for="width" class="form-label">Width</label>
                <input type="number" class="form-control" id="width" name="width" value="{{ $slipRecord->width }}" step="0.01">
            </div>
            <div class="mb-3">
                <label for="width_description" class="form-label">Width Description</label>
                <input type="text" class="form-control" id="width_description" name="width_description" value="{{ $slipRecord->width_description }}" maxlength="100">
            </div>
            <div class="mb-3">
                <label for="support_id" class="form-label">Support ID</label>
                <select class="form-select" id="support_id" name="support_id" required>
                    @foreach ($supports as $support)
                        <option value="{{ $support->id }}" {{ $support->id == $slipRecord->support_id ? 'selected' : '' }}>{{ $support->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="mb-3">
                <label for="activity_id" class="form-label">Activity ID</label>
                <select class="form-select" id="activity_id" name="activity_id" required>
                    @foreach ($activities as $activity)
                        <option value="{{ $activity->id }}" {{ $activity->id == $slipRecord->activity_id ? 'selected' : '' }}>{{ $activity->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="mb-3">
                <label for="container_id" class="form-label">Container ID</label>
                <select class="form-select" id="container_id" name="container_id">
                    <option value="">None</option>
                    @foreach ($containers as $container)
                        <option value="{{ $container->id }}" {{ $container->id == $slipRecord->container_id ? 'selected' : '' }}>{{ $container->name }}</option>
                    @endforeach
                </select>
            </div>
            <button type="submit" class="btn btn-primary">Update</button>
        </form>
    </div>
@endsection
