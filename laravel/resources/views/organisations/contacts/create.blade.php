@extends('layouts.app')

@section('content')
<div class="container py-3">
    <nav aria-label="breadcrumb" class="mb-3">
        <ol class="breadcrumb mb-0">
            <li class="breadcrumb-item"><a href="{{ route('organisations.index') }}">{{ __('Organisations') }}</a></li>
            <li class="breadcrumb-item"><a href="{{ route('organisations.contacts.index', $organisation) }}">{{ $organisation->name }}</a></li>
            <li class="breadcrumb-item active" aria-current="page">{{ __('Add contact') }}</li>
        </ol>
    </nav>

    <div class="card shadow-sm">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">{{ __('New contact') }}</h5>
        </div>
        <div class="card-body">
            <form method="POST" action="{{ route('organisations.contacts.store', $organisation) }}" id="contactForm">
                @csrf
                <div class="row g-3">
                    <div class="col-md-3">
                        <label class="form-label" for="contactType">{{ __('Type') }}</label>
                        <select name="type" id="contactType" class="form-select" required>
                            @foreach(['email','telephone','gps','fax','code_postal','adresse'] as $t)
                                <option value="{{ $t }}">{{ $t }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label" for="contactValue">{{ __('Value') }}</label>
                        <textarea name="value" id="contactValue" class="form-control" rows="2" required></textarea>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label" for="contactLabel">{{ __('Label') }}</label>
                        <input type="text" id="contactLabel" name="label" class="form-control" maxlength="190">
                    </div>
                    <div class="col-12">
                        <label class="form-label" for="contactNotes">{{ __('Notes') }}</label>
                        <textarea name="notes" id="contactNotes" class="form-control" rows="2"></textarea>
                    </div>
                </div>
                <div class="d-flex justify-content-end gap-2 mt-3">
                    <a href="{{ route('organisations.contacts.index', $organisation) }}" class="btn btn-outline-secondary">{{ __('Cancel') }}</a>
                    <button type="submit" class="btn btn-primary">{{ __('Save') }}</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    // Dynamiser le champ valeur selon le type choisi
    document.getElementById('contactType').addEventListener('change', function() {
        const type = this.value;
        const value = document.getElementById('contactValue');
        value.placeholder = '';
        if (type === 'email') value.placeholder = 'ex: org@example.com';
        if (type === 'telephone') value.placeholder = 'ex: +237 650000000';
        if (type === 'gps') value.placeholder = 'ex: 3.8616, 11.5021';
        if (type === 'fax') value.placeholder = 'ex: +237 222 00 00 00';
        if (type === 'code_postal') value.placeholder = 'ex: BP 12345';
        if (type === 'adresse') value.placeholder = "ex: Quartier, Ville";
    });
</script>
@endpush
