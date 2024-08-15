@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">Edit User Organisation</div>
                <div class="card-body">
                    <form action="{{ route('user-organisation.update', Auth::user()->getAuthIdentifier()) }}" method="POST">
                        @csrf
                        @method('PUT')
                        <div class="form-group">
                            <select class="form-select" name="organisation_id" id="organisation_id">
                                @foreach($organisations as $organisation)
                                    <option value="{{ $organisation->id }}" {{ $userOrganisation->organisation_id == $organisation->id ? 'selected' : '' }}>
                                        {{ $organisation->code }} - {{ $organisation->name }}
                                        @if($userOrganisation->organisation_id == $organisation->id)
                                            (poste actuel)
                                        @endif
                                    </option>
                                @endforeach
                            </select>
                        </div>
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
