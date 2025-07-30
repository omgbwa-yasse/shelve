@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">{{ __('Show User') }}</div>

                <div class="card-body">
                    <div class="form-group">
                        <label for="name">Name</label>
                        <input type="text" class="form-control" id="name" value="{{ $user->name }}" readonly>
                    </div>

                    <div class="form-group">
                        <label for="surname">Surname</label>
                        <input type="text" class="form-control" id="surname" value="{{ $user->surname }}" readonly>
                    </div>

                    <div class="form-group">
                        <label for="birthday">Birthday</label>
                        <input type="date" class="form-control" id="birthday" value="{{ $user->birthday }}" readonly>
                    </div>

                    <div class="form-group">
                        <label for="email">Email</label>
                        <input type="email" class="form-control" id="email" value="{{ $user->email }}" readonly>
                    </div>

                    <!-- Section pour afficher les organisations -->
                    <div class="form-group">
                        <h5>{{ __('Organisations affiliées') }}</h5>
                        @if($user->organisations && $user->organisations->count() > 0)
                            <div class="list-group">
                                @foreach($user->organisations as $organisation)
                                    <div class="list-group-item d-flex justify-content-between align-items-center">
                                        <div>
                                            <h6 class="mb-1">{{ $organisation->name }}</h6>
                                            @if($organisation->description)
                                                <p class="mb-1 text-muted">{{ $organisation->description }}</p>
                                            @endif
                                        </div>
                                        @if($user->current_organisation_id == $organisation->id)
                                            <span class="badge badge-primary">{{ __('Organisation actuelle') }}</span>
                                        @endif
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <div class="alert alert-info">
                                {{ __('Cet utilisateur n\'est affilié à aucune organisation.') }}
                            </div>
                        @endif
                    </div>

                    <a href="{{ route('users.edit', $user->id ) }}" class="btn btn-secondary">Modifier</a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
