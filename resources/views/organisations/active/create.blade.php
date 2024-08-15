@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">{{ __('Create Organisation Active') }}</div>

                <div class="card-body">
                    <form method="POST" action="{{ route('organisation-active.store') }}">
                        @csrf
                        <div class="form-group">
                            <label for="organisation_id">Organisation</label>
                            <select name="organisation_id" id="organisation_id" class="form-control" required>
                                @foreach ($organisations as $organisation)
                                    <option value="{{ $organisation->id }}">{{ $organisation->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <button type="submit" class="btn btn-primary">{{ __('Create') }}</button>
                    </form>

                </div>
            </div>
        </div>
    </div>
</div>
@endsection
