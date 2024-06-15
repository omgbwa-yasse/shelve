@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">Edit User Organisation</div>

                <div class="card-body">
                    <form action="{{ route('user-organisations.update', [$userOrganisation->user_id, $userOrganisation->organisation_id]) }}" method="POST">
                        @csrf
                        @method('PUT')

                        <div class="form-group">
                            <label for="active">Active</label>
                            <input type="checkbox" name="active" id="active" {{ $userOrganisation->active ? 'checked' : '' }}>
                        </div>

                        <button type="submit" class="btn btn-primary">Update</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
