@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">Edit Child Record {{ $child->reference }}</div>

                <div class="card-body">
                    <form action="{{ route('records.children.update', [$record->id, $child->id]) }}" method="PUT">
                        @csrf

                        <div class="form-group">
                            <label for="reference">Reference</label>
                            <input type="text" class="form-control" id="reference" name="reference" value="{{ $child->reference }}" required>
                        </div>
                        <div class="form-group">
                            <label for="name">Name</label>
                            <input type="text" class="form-control" id="name" name="name" value="{{ $child->name }}" required>
                        </div>
                        <!-- Add more fields as needed -->

                        <button type="submit" class="btn btn-primary">Update Child Record</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
