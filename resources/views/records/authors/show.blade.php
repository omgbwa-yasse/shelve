@extends('layouts.app')

@section('content')
    <div class="container mt-4">
        <!-- Header avec navigation -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div class="d-flex align-items-center gap-2">
                <a href="{{ route('record-author.index') }}" class="btn btn-link text-dark p-0">
                    <i class="bi bi-arrow-left"></i>
                </a>
                <h4 class="mb-0">{{ __('author_details') }}</h4>
            </div>
            <div class="btn-group">
                <a href="{{ route('records.sort') }}?categ=author&id={{ $author->id}}" class="btn btn-outline-success">
                    <i class="bi bi-archive me-1"></i>
                    {{ __('view_archives') }}
                </a>
                <a href="{{ route('record-author.edit', $author) }}" class="btn btn-outline-primary">
                    <i class="bi bi-pencil me-1"></i>
                    {{ __('edit') }}
                </a>
                <button type="button" class="btn btn-outline-danger" data-bs-toggle="modal" data-bs-target="#deleteModal">
                    <i class="bi bi-trash me-1"></i>
                    {{ __('delete') }}
                </button>
            </div>
        </div>

        <div class="row">
            <!-- Informations principales -->
            <div class="col-lg-8">
                <div class="card shadow-sm mb-4">
                    <div class="card-header bg-white py-3">
                        <div class="d-flex justify-content-between align-items-center">
                            <h5 class="mb-0">{{ $author->name }}</h5>
                            <span class="badge bg-secondary">{{ $author->authorType ? $author->authorType->name : 'N/A' }}</span>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            @if ($author->parallel_name)
                                <div class="col-md-6">
                                    <label class="text-muted small mb-1">{{ __('parallel_name') }}</label>
                                    <p class="mb-0">{{ $author->parallel_name }}</p>
                                </div>
                            @endif

                            @if ($author->other_name)
                                <div class="col-md-6">
                                    <label class="text-muted small mb-1">{{ __('other_name') }}</label>
                                    <p class="mb-0">{{ $author->other_name }}</p>
                                </div>
                            @endif

                            @if ($author->lifespan)
                                <div class="col-md-6">
                                    <label class="text-muted small mb-1">{{ __('lifespan') }}</label>
                                    <p class="mb-0">{{ $author->lifespan }}</p>
                                </div>
                            @endif

                            @if ($author->locations)
                                <div class="col-md-6">
                                    <label class="text-muted small mb-1">{{ __('locations') }}</label>
                                    <p class="mb-0">{{ $author->locations }}</p>
                                </div>
                            @endif

                            @if ($author->parent)
                                <div class="col-md-6">
                                    <label class="text-muted small mb-1">{{ __('parent_author') }}</label>
                                    <p class="mb-0">{{ $author->parent->name }}</p>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            <!-- Contacts -->
            <div class="col-lg-4">
                <div class="card shadow-sm mb-4">
                    <div class="card-header bg-white py-3">
                        <div class="d-flex justify-content-between align-items-center">
                            <h5 class="mb-0">{{ __('contact_information') }}</h5>
                            <button type="button" class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#addContactModal">
                                <i class="bi bi-plus-lg me-1"></i>
                                {{ __('add_contact') }}
                            </button>
                        </div>
                    </div>
                    <div class="card-body p-0">
                        @forelse($author->contacts as $contact)
                            <div class="contact-item p-3 border-bottom">
                                @if($contact->phone1 || $contact->phone2)
                                    <div class="mb-2">
                                        <i class="bi bi-telephone me-2"></i>
                                        {{ $contact->phone1 }}
                                        @if($contact->phone2)
                                            <br><i class="bi bi-telephone me-2"></i>{{ $contact->phone2 }}
                                        @endif
                                    </div>
                                @endif

                                @if($contact->email)
                                    <div class="mb-2">
                                        <i class="bi bi-envelope me-2"></i>
                                        {{ $contact->email }}
                                    </div>
                                @endif

                                @if($contact->address)
                                    <div class="mb-2">
                                        <i class="bi bi-geo-alt me-2"></i>
                                        {{ $contact->address }}
                                        @if($contact->po_box)
                                            <br><small class="text-muted ms-4">{{ __('po_box') }}: {{ $contact->po_box }}</small>
                                        @endif
                                    </div>
                                @endif

                                @if($contact->website)
                                    <div class="mb-2">
                                        <i class="bi bi-globe me-2"></i>
                                        {{ $contact->website }}
                                    </div>
                                @endif

                                @if($contact->fax)
                                    <div class="mb-2">
                                        <i class="bi bi-printer me-2"></i>
                                        {{ $contact->fax }}
                                    </div>
                                @endif

                                @if($contact->other)
                                    <div class="mb-2">
                                        <i class="bi bi-info-circle me-2"></i>
                                        {{ $contact->other }}
                                    </div>
                                @endif
                            </div>
                        @empty
                            <div class="text-center py-4">
                                <i class="bi bi-person-vcard text-muted display-4"></i>
                                <p class="text-muted mt-2">{{ __('no_contacts_found') }}</p>
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal de confirmation de suppression -->
    <div class="modal fade" id="deleteModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">{{ __('confirm_deletion') }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    {{ __('confirm_delete_author') }}
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        {{ __('cancel') }}
                    </button>
                    <form action="{{ route('record-author.destroy', $author) }}" method="POST" class="d-inline">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger">
                            {{ __('delete') }}
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal for adding a contact -->
    <div class="modal fade" id="addContactModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">{{ __('add_contact') }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form action="{{ route('record-author.update', $author) }}" method="POST">
                    @csrf
                    @method('PUT')
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="phone1" class="form-label">{{ __('phone') }} 1</label>
                            <input type="text" class="form-control" id="phone1" name="contact[phone1]">
                        </div>
                        <div class="mb-3">
                            <label for="phone2" class="form-label">{{ __('phone') }} 2</label>
                            <input type="text" class="form-control" id="phone2" name="contact[phone2]">
                        </div>
                        <div class="mb-3">
                            <label for="email" class="form-label">{{ __('email') }}</label>
                            <input type="email" class="form-control" id="email" name="contact[email]">
                        </div>
                        <div class="mb-3">
                            <label for="address" class="form-label">{{ __('address') }}</label>
                            <input type="text" class="form-control" id="address" name="contact[address]">
                        </div>
                        <div class="mb-3">
                            <label for="po_box" class="form-label">{{ __('po_box') }}</label>
                            <input type="text" class="form-control" id="po_box" name="contact[po_box]">
                        </div>
                        <div class="mb-3">
                            <label for="website" class="form-label">{{ __('website') }}</label>
                            <input type="text" class="form-control" id="website" name="contact[website]">
                        </div>
                        <div class="mb-3">
                            <label for="fax" class="form-label">{{ __('fax') }}</label>
                            <input type="text" class="form-control" id="fax" name="contact[fax]">
                        </div>
                        <div class="mb-3">
                            <label for="other" class="form-label">{{ __('other') }}</label>
                            <textarea class="form-control" id="other" name="contact[other]" rows="3"></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                            {{ __('cancel') }}
                        </button>
                        <button type="submit" class="btn btn-primary">
                            {{ __('save') }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    @push('styles')
        <style>
            .contact-item:last-child {
                border-bottom: none !important;
            }
            .contact-item:hover {
                background-color: #f8f9fa;
            }
            .btn-link {
                text-decoration: none;
            }
            .btn-link:hover {
                color: #0d6efd !important;
            }
        </style>
    @endpush
@endsection
