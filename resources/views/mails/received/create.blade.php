@extends('layouts.app')

@section('content')
    <div class="container py-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1 class="h3">Créer un courrier entrant</h1>
        </div>

        @if ($errors->any())
            <div class="alert alert-danger">
                <ul>
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="card">
            <div class="card-body">
                <form action="{{ route('mail-received.store') }}" method="POST">
                    @csrf
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Nom du courrier</label>
                                <input type="text" name="name" class="form-control" value="{{ old('name') }}" required>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Date du courrier</label>
                                <input type="date" name="date" class="form-control" value="{{ old('date') }}" required>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Description</label>
                                <textarea name="description" class="form-control" rows="3">{{ old('description') }}</textarea>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Type de document</label>
                                <select name="document_type" class="form-select" required>
                                    <option value="">Sélectionner un type</option>
                                    <option value="original" {{ old('document_type') == 'original' ? 'selected' : '' }}>Original</option>
                                    <option value="duplicate" {{ old('document_type') == 'duplicate' ? 'selected' : '' }}>Duplicata</option>
                                    <option value="copy" {{ old('document_type') == 'copy' ? 'selected' : '' }}>Copie</option>
                                </select>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Organisation d'envoi</label>
                                <select name="sender_organisation_id" class="form-select" required>
                                    <option value="">Sélectionner une organisation</option>
                                    @foreach($senderOrganisations as $organisation)
                                        <option value="{{ $organisation->id }}" {{ old('sender_organisation_id') == $organisation->id ? 'selected' : '' }}>
                                            {{ $organisation->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Utilisateur expéditeur</label>
                                <select name="sender_user_id" class="form-select" required>
                                    <option value="">Sélectionner un utilisateur</option>
                                    @foreach($users as $user)
                                        <option value="{{ $user->id }}" {{ old('sender_user_id') == $user->id ? 'selected' : '' }}>
                                            {{ $user->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Action requise</label>
                                <select name="action_id" class="form-select" required>
                                    <option value="">Sélectionner une action</option>
                                    @foreach($mailActions as $action)
                                        <option value="{{ $action->id }}" {{ old('action_id') == $action->id ? 'selected' : '' }}>
                                            {{ $action->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Priorité</label>
                                <select name="priority_id" class="form-select" required>
                                    <option value="">Sélectionner une priorité</option>
                                    @foreach($priorities as $priority)
                                        <option value="{{ $priority->id }}" {{ old('priority_id') == $priority->id ? 'selected' : '' }}>
                                            {{ $priority->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Typologie</label>
                                <select name="typology_id" class="form-select" required>
                                    <option value="">Sélectionner une typologie</option>
                                    @foreach($typologies as $typology)
                                        <option value="{{ $typology->id }}" {{ old('typology_id') == $typology->id ? 'selected' : '' }}>
                                            {{ $typology->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="mt-4">
                        <button type="submit" class="btn btn-primary">Créer le courrier</button>
                        <a href="{{ route('mail-received.index') }}" class="btn btn-light ms-2">Annuler</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
