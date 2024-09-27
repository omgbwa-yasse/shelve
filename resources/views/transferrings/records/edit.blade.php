@extends('layouts.app')

@section('content')
    <div class="container">
        <div class="container">
            @if ($errors->any())
                <div class="alert alert-danger">
                    <ul>
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <h1> Ajouter un document </h1>
            <form action="{{ route('slips.records.update', [$slip, $slipRecord ] ) }}" method="POST">
                @csrf
                @method('PUT')
                <div class="mb-3">
                    Versement : <h3>{{ $slip->code }} - {{ $slip->name }}</h3>
                </div>
                <div class="row">
                    <div class="col-md-4 mb-3">
                        <label for="code" class="form-label">Code</label>
                        <input type="text" class="form-control" id="code" name="code" value="{{ $slipRecord->code }}" required maxlength="10">
                    </div>
                </div>
                <div class="mb-3">
                    <label for="name" class="form-label">Name</label>
                    <input type="text" class="form-control" id="name" value="{{ $slipRecord->name }}" name="name" required>
                </div>
                <div class="row">
                    <div class="col-md-4 mb-3">
                        <label for="date_start" class="form-label">Date Start</label>
                        <input type="text" class="form-control" id="date_start" name="date_start" value="{{ $slipRecord->date_start }}" maxlength="10">
                    </div>
                    <div class="col-md-4 mb-3">
                        <label for="date_end" class="form-label">Date End</label>
                        <input type="text" class="form-control" id="date_end" name="date_end" value="{{ $slipRecord->date_end }}" maxlength="10">
                    </div>
                    <div class="col-md-4 mb-3">
                        <label for="date_exact" class="form-label">Date Exact</label>
                        <input type="date" class="form-control" id="date_exact" value="{{ $slipRecord->date_exact }}" name="date_exact">
                    </div>
                </div>
                <div class="mb-3">
                    <label for="content" class="form-label">Content</label>
                    <textarea class="form-control" id="content" name="content" value="{{ $slipRecord->content }}"></textarea>
                </div>
                <div class="row">
                    <div class="col-md-4 mb-3">
                        <label for="level_id" class="form-label">Niveau de description</label>
                        <select class="form-select" id="level_id" name="level_id" required>
                            @foreach ($levels as $level)
                                <option value="{{ $level->id }}">{{ $level->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-4 mb-3">
                        <label for="width" class="form-label">Width</label>
                        <input type="number" class="form-control" id="width" name="width" value="{{ $slipRecord->width }}" step="0.01">
                    </div>
                    <div class="col-md-4 mb-3">
                        <label for="width_description" class="form-label">Width Description</label>
                        <input type="text" class="form-control" id="width_description" name="width_description" value="{{ $slipRecord->width_description }}" maxlength="100">
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-4 mb-3">
                        <label for="support_id" class="form-label">Support de conservation</label>
                        <select class="form-select" id="support_id" name="support_id" required>
                            @foreach ($supports as $support)
                                <option value="{{ $support->id }}" {{ $support->id == $slipRecord->support_id ? 'selected' : '' }}>{{ $support->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-4 mb-3">
                        <label for="activity_id" class="form-label">Activité</label>
                        <select class="form-select" id="activity_id" name="activity_id" required>
                            @foreach ($activities as $activity)
                                <option value="{{ $activity->id }}" {{ $activity->id == $slipRecord->activity_id ? 'selected' : '' }}>{{ $activity->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-4 mb-3">
                        <label for="container_id" class="form-label">Contenant</label>
                        <select class="form-select" id="container_id" name="container_id">
                            @foreach ($containers as $container)
                                <option value="{{ $container->id }}"  {{ $container->id == $slipRecord->container_id ? 'selected' : '' }}>{{ $container->code }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <button type="submit" class="btn btn-primary">Mettre à jour</button>
            </form>
        </div>

@endsection
