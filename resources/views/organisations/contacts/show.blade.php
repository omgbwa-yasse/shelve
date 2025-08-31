@extends('layouts.app')

@section('content')
<div class="container py-3">
    <nav aria-label="breadcrumb" class="mb-3">
        <ol class="breadcrumb mb-0">
            <li class="breadcrumb-item"><a href="{{ route('organisations.index') }}">{{ __('Organisations') }}</a></li>
            <li class="breadcrumb-item"><a href="{{ route('organisations.contacts.index', $organisation) }}">{{ $organisation->name }}</a></li>
            <li class="breadcrumb-item active" aria-current="page">{{ __('Contact') }}</li>
        </ol>
    </nav>

    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4 class="mb-0">
            <i class="bi bi-person-lines-fill me-2"></i>{{ __('Contact details') }}
        </h4>
        <div class="d-flex gap-2">
            <a href="{{ route('organisations.contacts.create', $organisation) }}" class="btn btn-sm btn-primary">
                <i class="bi bi-plus-lg"></i> {{ __('Add contact') }}
            </a>
            <a href="{{ route('organisations.contacts.edit', [$organisation, $contact]) }}" class="btn btn-sm btn-outline-primary">
                <i class="bi bi-pencil"></i> {{ __('Edit') }}
            </a>
        </div>
    </div>

    <div class="row g-3">
        <div class="col-lg-5">
            <div class="card shadow-sm h-100">
                <div class="card-header">
                    <strong>{{ __('Selected contact') }}</strong>
                </div>
                <div class="card-body">
                    <dl class="row mb-0">
                        <dt class="col-4">{{ __('Type') }}</dt>
                        <dd class="col-8"><span class="badge bg-secondary">{{ $contact->type }}</span></dd>
                        <dt class="col-4">{{ __('Value') }}</dt>
                        <dd class="col-8">
                            <span id="contact-value">{{ $contact->value }}</span>
                            <button type="button" class="btn btn-sm btn-outline-secondary ms-2" id="copyBtn">
                                <i class="bi bi-clipboard"></i>
                            </button>
                        </dd>
                        <dt class="col-4">{{ __('Label') }}</dt>
                        <dd class="col-8">{{ $contact->label }}</dd>
                        <dt class="col-4">{{ __('Notes') }}</dt>
                        <dd class="col-8">{{ $contact->notes }}</dd>
                    </dl>
                </div>
            </div>
        </div>
        <div class="col-lg-7">
            <div class="card shadow-sm h-100">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <strong>{{ __('Contacts of') }} {{ $organisation->name }}</strong>
                    <a href="{{ route('organisations.contacts.create', $organisation) }}" class="btn btn-sm btn-primary">
                        <i class="bi bi-plus-lg"></i> {{ __('Add contact') }}
                    </a>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0 align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th style="width: 120px">{{ __('Type') }}</th>
                                    <th>{{ __('Value') }}</th>
                                    <th style="width: 180px">{{ __('Label') }}</th>
                                    <th style="width: 150px" class="text-end">{{ __('Actions') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($contacts as $c)
                                    <tr @class(['table-active' => $c->id === $contact->id])>
                                        <td><span class="badge bg-secondary">{{ $c->type }}</span></td>
                                        <td class="text-break">{{ $c->value }}</td>
                                        <td>{{ $c->label }}</td>
                                        <td class="text-end">
                                            <a class="btn btn-sm btn-outline-secondary" href="{{ route('organisations.contacts.show', [$organisation, $c]) }}">
                                                <i class="bi bi-eye"></i>
                                            </a>
                                            <a class="btn btn-sm btn-outline-primary" href="{{ route('organisations.contacts.edit', [$organisation, $c]) }}">
                                                <i class="bi bi-pencil"></i>
                                            </a>
                                            <form action="{{ route('organisations.contacts.destroy', [$organisation, $c]) }}" method="POST" class="d-inline" onsubmit="return confirm('{{ __('Are you sure?') }}')">
                                                @csrf
                                                @method('DELETE')
                                                <button class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button>
                                            </form>
                                        </td>
                                    </tr>
                                @empty
                                    <tr><td colspan="4" class="text-center text-muted py-4">{{ __('No contacts yet.') }}</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    document.getElementById('copyBtn')?.addEventListener('click', function() {
        const text = document.getElementById('contact-value')?.innerText || '';
        navigator.clipboard.writeText(text).then(() => {
            const btn = document.getElementById('copyBtn');
            btn.classList.remove('btn-outline-secondary');
            btn.classList.add('btn-success');
            btn.innerHTML = '<i class="bi bi-check2"></i>';
            setTimeout(() => {
                btn.classList.add('btn-outline-secondary');
                btn.classList.remove('btn-success');
                btn.innerHTML = '<i class="bi bi-clipboard"></i>';
            }, 1200);
        });
    });
</script>
@endpush
