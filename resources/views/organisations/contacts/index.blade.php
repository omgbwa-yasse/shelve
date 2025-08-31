@extends('layouts.app')

@section('content')
<div class="container py-3">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4 class="mb-0">
            <i class="bi bi-building me-2"></i>{{ $organisation->name }}
            <small class="text-muted">— {{ __('Contacts') }}</small>
        </h4>
        <a href="{{ route('organisations.contacts.create', $organisation) }}" class="btn btn-primary">
            <i class="bi bi-plus-lg"></i> {{ __('Add contact') }}
        </a>
    </div>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <div class="card shadow-sm">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0 align-middle">
                    <thead class="table-light">
                        <tr>
                            <th style="width: 140px">{{ __('Type') }}</th>
                            <th>{{ __('Value') }}</th>
                            <th style="width: 200px">{{ __('Label') }}</th>
                            <th style="width: 160px" class="text-end">{{ __('Actions') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($contacts as $contact)
                            <tr>
                                <td><span class="badge bg-secondary">{{ $contact->type }}</span></td>
                                <td class="text-break">
                                    <span class="me-2" id="contact-val-{{ $contact->id }}">{{ $contact->value }}</span>
                                    <button type="button" class="btn btn-sm btn-outline-secondary copy-btn" data-target="contact-val-{{ $contact->id }}" title="{{ __('Copy') }}">
                                        <i class="bi bi-clipboard"></i>
                                    </button>
                                </td>
                                <td>{{ $contact->label }}</td>
                                <td class="text-end">
                                    <a class="btn btn-sm btn-outline-primary" href="{{ route('organisations.contacts.edit', [$organisation, $contact]) }}">
                                        <i class="bi bi-pencil"></i>
                                    </a>
                                    <form action="{{ route('organisations.contacts.destroy', [$organisation, $contact]) }}" method="POST" class="d-inline" onsubmit="return confirm('{{ __('Are you sure?') }}')">
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
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('.copy-btn').forEach(function(btn){
        btn.addEventListener('click', function(){
            const id = this.getAttribute('data-target');
            const text = document.getElementById(id)?.innerText || '';
            navigator.clipboard.writeText(text).then(() => {
                this.classList.remove('btn-outline-secondary');
                this.classList.add('btn-success');
                this.innerHTML = '<i class="bi bi-check2"></i>';
                setTimeout(() => {
                    this.classList.add('btn-outline-secondary');
                    this.classList.remove('btn-success');
                    this.innerHTML = '<i class="bi bi-clipboard"></i>';
                }, 1200);
            });
        });
    });
});
</script>
@endpush
