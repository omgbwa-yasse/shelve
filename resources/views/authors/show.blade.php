@extends('layouts.app')

@section('content')
<div class="container-fluid mt-4">
    <div class="row justify-content-center">
        <div class="col-md-10">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-white border-bottom py-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <h4 class="mb-0 text-dark">
                            <i class="fas fa-user me-2 text-primary"></i>
                            {{ __('author_details') }}
                        </h4>
                        <a href="{{ route('mail-author.index') }}" class="btn btn-outline-secondary btn-sm">
                            <i class="fas fa-arrow-left me-2"></i>{{ __('back_to_authors') }}
                        </a>
                    </div>
                </div>

                <div class="card-body p-4">
                    <!-- Author Information Section -->
                    <div class="row mb-4">
                        <div class="col-md-8">
                            <h5 class="card-title text-primary mb-3">{{ $author->name }}</h5>
                            
                            <div class="author-info-grid">
                                <div class="info-item">
                                    <i class="fas fa-tag text-muted me-2"></i>
                                    <strong>{{ __('type') }}:</strong> {{ $author->authorType->name ?? __('not_defined') }}
                                </div>
                                
                                @if ($author->parallel_name)
                                <div class="info-item">
                                    <i class="fas fa-user-tag text-muted me-2"></i>
                                    <strong>{{ __('parallel_name_label') }}:</strong> {{ $author->parallel_name }}
                                </div>
                                @endif
                                
                                @if ($author->other_name)
                                <div class="info-item">
                                    <i class="fas fa-user-plus text-muted me-2"></i>
                                    <strong>{{ __('other_name_label') }}:</strong> {{ $author->other_name }}
                                </div>
                                @endif
                                
                                @if ($author->lifespan)
                                <div class="info-item">
                                    <i class="fas fa-calendar text-muted me-2"></i>
                                    <strong>{{ __('lifespan_label') }}:</strong> {{ $author->lifespan }}
                                </div>
                                @endif
                                
                                @if ($author->locations)
                                <div class="info-item">
                                    <i class="fas fa-map-marker-alt text-muted me-2"></i>
                                    <strong>{{ __('locations') }}:</strong> {{ $author->locations }}
                                </div>
                                @endif
                                
                                @if ($author->parent)
                                <div class="info-item">
                                    <i class="fas fa-sitemap text-muted me-2"></i>
                                    <strong>{{ __('parent_author') }}:</strong> {{ $author->parent->name }}
                                </div>
                                @endif
                            </div>
                        </div>
                        
                        <div class="col-md-4">
                            <div class="text-center">
                                <div class="author-avatar mb-3">
                                    <i class="fas fa-user-circle fa-4x text-primary"></i>
                                </div>
                                <div class="d-grid gap-2">
                                    <a href="{{ route('mail-author.edit', $author) }}" class="btn btn-outline-primary">
                                        <i class="fas fa-edit me-2"></i>{{ __('edit') }}
                                    </a>
                                    <a href="{{ route('author-contact.create', $author) }}" class="btn btn-success">
                                        <i class="fas fa-plus me-2"></i>{{ __('add_contact') }}
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Contacts Section -->
                    @if($author->contacts && $author->contacts->count() > 0)
                    <div class="contacts-section">
                        <h6 class="text-muted mb-3">
                            <i class="fas fa-address-book me-2"></i>{{ __('contacts') }}
                        </h6>
                        
                        <div class="row">
                            @foreach($author->contacts as $contact)
                            <div class="col-md-6 mb-3">
                                <div class="contact-card p-3 border rounded">
                                    <div class="contact-info">
                                        @if ($contact->phone1)
                                        <div class="contact-item">
                                            <i class="fas fa-phone text-primary me-2"></i>
                                            <strong>{{ __('phone1') }}:</strong> {{ $contact->phone1 }}
                                        </div>
                                        @endif
                                        
                                        @if ($contact->phone2)
                                        <div class="contact-item">
                                            <i class="fas fa-phone text-primary me-2"></i>
                                            <strong>{{ __('phone2') }}:</strong> {{ $contact->phone2 }}
                                        </div>
                                        @endif
                                        
                                        @if ($contact->email)
                                        <div class="contact-item">
                                            <i class="fas fa-envelope text-primary me-2"></i>
                                            <strong>{{ __('email') }}:</strong> 
                                            <a href="mailto:{{ $contact->email }}">{{ $contact->email }}</a>
                                        </div>
                                        @endif
                                        
                                        @if ($contact->address)
                                        <div class="contact-item">
                                            <i class="fas fa-map-marker-alt text-primary me-2"></i>
                                            <strong>{{ __('address') }}:</strong> {{ $contact->address }}
                                        </div>
                                        @endif
                                        
                                        @if ($contact->website)
                                        <div class="contact-item">
                                            <i class="fas fa-globe text-primary me-2"></i>
                                            <strong>{{ __('website') }}:</strong> 
                                            <a href="{{ $contact->website }}" target="_blank">{{ $contact->website }}</a>
                                        </div>
                                        @endif
                                        
                                        @if ($contact->fax)
                                        <div class="contact-item">
                                            <i class="fas fa-fax text-primary me-2"></i>
                                            <strong>{{ __('fax') }}:</strong> {{ $contact->fax }}
                                        </div>
                                        @endif
                                        
                                        @if ($contact->other)
                                        <div class="contact-item">
                                            <i class="fas fa-info-circle text-primary me-2"></i>
                                            <strong>{{ __('other') }}:</strong> {{ $contact->other }}
                                        </div>
                                        @endif
                                        
                                        @if ($contact->po_box)
                                        <div class="contact-item">
                                            <i class="fas fa-inbox text-primary me-2"></i>
                                            <strong>{{ __('po_box') }}:</strong> {{ $contact->po_box }}
                                        </div>
                                        @endif
                                    </div>
                                </div>
                            </div>
                            @endforeach
                        </div>
                    </div>
                    @else
                    <div class="text-center py-4">
                        <div class="mb-3">
                            <i class="fas fa-address-book fa-3x text-muted"></i>
                        </div>
                        <h6 class="text-muted mb-2">{{ __('no_contacts_found') }}</h6>
                        <p class="text-muted mb-3">{{ __('start_adding_contacts') }}</p>
                        <a href="{{ route('author-contact.create', $author) }}" class="btn btn-success">
                            <i class="fas fa-plus me-2"></i>{{ __('add_contact') }}
                        </a>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
.card {
    border-radius: 0.75rem;
    box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
}

.card-header {
    background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
    border-bottom: 1px solid #dee2e6;
}

.author-info-grid {
    display: grid;
    gap: 1rem;
}

.info-item {
    padding: 0.75rem;
    background: #f8f9fa;
    border-radius: 0.5rem;
    border-left: 4px solid #0d6efd;
}

.info-item i {
    width: 20px;
    text-align: center;
}

.author-avatar {
    color: #0d6efd;
}

.contacts-section {
    border-top: 1px solid #dee2e6;
    padding-top: 2rem;
}

.contact-card {
    background: #f8f9fa;
    transition: all 0.3s ease;
}

.contact-card:hover {
    background: #e9ecef;
    transform: translateY(-2px);
    box-shadow: 0 0.25rem 0.5rem rgba(0, 0, 0, 0.1);
}

.contact-info {
    display: grid;
    gap: 0.5rem;
}

.contact-item {
    display: flex;
    align-items: center;
    font-size: 0.9rem;
}

.contact-item i {
    width: 20px;
    text-align: center;
    margin-right: 0.5rem;
}

.contact-item a {
    color: #0d6efd;
    text-decoration: none;
}

.contact-item a:hover {
    text-decoration: underline;
}

.btn {
    border-radius: 0.5rem;
    padding: 0.5rem 1rem;
    font-weight: 500;
}

.btn-outline-primary {
    border-color: #0d6efd;
    color: #0d6efd;
}

.btn-outline-primary:hover {
    background-color: #0d6efd;
    border-color: #0d6efd;
}

.btn-success {
    background-color: #198754;
    border-color: #198754;
}

.btn-success:hover {
    background-color: #157347;
    border-color: #146c43;
}

/* Responsive adjustments */
@media (max-width: 768px) {
    .col-md-4 {
        margin-top: 2rem;
    }
    
    .author-info-grid {
        grid-template-columns: 1fr;
    }
    
    .contact-info {
        grid-template-columns: 1fr;
    }
}

/* Animation for cards */
.contact-card {
    animation: fadeInUp 0.6s ease-out;
}

@keyframes fadeInUp {
    from {
        opacity: 0;
        transform: translateY(20px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}
</style>
@endpush
