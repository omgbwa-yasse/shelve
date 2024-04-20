@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">Create Record</div>

                <div class="card-body">
                    @if (session('success'))
                        <div class="alert alert-success" role="alert">
                            {{ session('success') }}
                        </div>
                    @endif

                    <form action="{{ route('records.store') }}" method="POST">
                        @csrf

                        <div class="form-group">
                            <label for="reference">Reference</label>
                            <input type="text" class="form-control" id="reference" name="reference" required>
                        </div>
                        <div class="form-group">
                            <label for="name">Name</label>
                            <input type="text" class="form-control" id="name" name="name" required>
                        </div>
                        <div class="form-group">
                            <label for="date_format">Date Format</label>
                            <input type="text" class="form-control" id="date_format" name="date_format" required>
                        </div>
                        <div class="form-group">
                            <label for="date_start">Date Start</label>
                            <input type="date" class="form-control" id="date_start" name="date_start" required>
                        </div>
                        <div class="form-group">
                            <label for="date_end">Date End</label>
                            <input type="date" class="form-control" id="date_end" name="date_end" required>
                        </div>
                        <div class="form-group">
                            <label for="date_exact">Date Exact</label>
                            <input type="date" class="form-control" id="date_exact" name="date_exact" required>
                        </div>
                        <div class="form-group">
                            <label for="description">Description</label>
                            <textarea class="form-control" id="description" name="description" required></textarea>
                        </div>
                        <div class="form-group">
                            <label for="level_id">Level</label>
                            <select class="form-control" id="level_id" name="level_id" required>
                                @foreach ($levels as $level)
                                    <option value="{{ $level->id }}">{{ $level->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="status_id">Status</label>
                            <select class="form-control" id="status_id" name="status_id" required>
                                @foreach ($statuses as $status)
                                    <option value="{{ $status->id }}">{{ $status->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="support_id">Support</label>
                            <select class="form-control" id="support_id" name="support_id" required>
                                @foreach ($supports as $support)
                                    <option value="{{ $support->id }}">{{ $support->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="classification_id">Classification</label>
                            <select class="form-control" id="classification_id" name="classification_id" required>
                                @foreach ($classifications as $classification)
                                    <option value="{{ $classification->id }}">{{ $classification->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="container_id">Container</label>
                            <select class="form-control" id="container_id" name="container_id" required>
                                @foreach ($containers as $container)
                                    <option value="{{ $container->id }}">{{ $container->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <button type="submit" class="btn btn-primary">Create</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
