@extends('layouts.app')

@section('title', 'Configuration OPAC')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                <h4 class="mb-sm-0 font-size-18">Configuration OPAC</h4>
                <div class="page-title-right">
                    <ol class="breadcrumb m-0">
                        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Administration</a></li>
                        <li class="breadcrumb-item active">Configuration OPAC</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title mb-0">
                        <i class="fas fa-cogs text-primary me-2"></i>
                        Configuration du catalogue public (OPAC)
                    </h4>
                </div>
                <div class="card-body">
                    @if(session('success'))
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            <i class="fas fa-check-circle me-2"></i>
                            {{ session('success') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif

                    @if(session('error'))
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <i class="fas fa-exclamation-triangle me-2"></i>
                            {{ session('error') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif

                    <form method="POST" action="{{ route('admin.opac.update') }}" enctype="multipart/form-data">
                        @csrf

                        <!-- Basic Settings -->
                        <div class="mb-4">
                            <h5>Paramètres généraux</h5>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="site_name" class="form-label">Nom du site</label>
                                        <input type="text" class="form-control @error('site_name') is-invalid @enderror"
                                               id="site_name" name="site_name"
                                               value="{{ old('site_name', $config->site_name ?? 'Archive OPAC') }}" required>
                                        @error('site_name')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="contact_email" class="form-label">Email de contact</label>
                                        <input type="email" class="form-control @error('contact_email') is-invalid @enderror"
                                               id="contact_email" name="contact_email"
                                               value="{{ old('contact_email', $config->contact_email ?? '') }}">
                                        @error('contact_email')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                            </div>

                            <div class="mb-3">
                                <label for="site_description" class="form-label">Description du site</label>
                                <textarea class="form-control @error('site_description') is-invalid @enderror"
                                          id="site_description" name="site_description" rows="3">{{ old('site_description', $config->site_description ?? 'Online Public Access Catalog') }}</textarea>
                                @error('site_description')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <!-- Options -->
                        <div class="mb-4">
                            <h5>Options</h5>

                            <div class="row">
                                <div class="col-md-4">
                                    <div class="form-check form-switch mb-3">
                                        <input class="form-check-input" type="checkbox" id="is_enabled" name="is_enabled" value="1"
                                               {{ old('is_enabled', $config->is_enabled ?? true) ? 'checked' : '' }}>
                                        <label class="form-check-label" for="is_enabled">
                                            OPAC activé
                                        </label>
                                    </div>
                                </div>

                                <div class="col-md-4">
                                    <div class="form-check form-switch mb-3">
                                        <input class="form-check-input" type="checkbox" id="allow_downloads" name="allow_downloads" value="1"
                                               {{ old('allow_downloads', $config->allow_downloads ?? true) ? 'checked' : '' }}>
                                        <label class="form-check-label" for="allow_downloads">
                                            Autoriser les téléchargements
                                        </label>
                                    </div>
                                </div>

                                <div class="col-md-4">
                                    <div class="form-check form-switch mb-3">
                                        <input class="form-check-input" type="checkbox" id="require_login_for_downloads" name="require_login_for_downloads" value="1"
                                               {{ old('require_login_for_downloads', $config->require_login_for_downloads ?? false) ? 'checked' : '' }}>
                                        <label class="form-check-label" for="require_login_for_downloads">
                                            Connexion requise pour télécharger
                                        </label>
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="items_per_page" class="form-label">Éléments par page</label>
                                        <input type="number" class="form-control @error('items_per_page') is-invalid @enderror"
                                               id="items_per_page" name="items_per_page" min="5" max="100"
                                               value="{{ old('items_per_page', $config->items_per_page ?? 20) }}" required>
                                        @error('items_per_page')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="logo" class="form-label">Logo</label>
                                        <input type="file" class="form-control @error('logo') is-invalid @enderror"
                                               id="logo" name="logo" accept="image/*">
                                        @if($config->logo_path ?? false)
                                            <small class="text-muted">Logo actuel : {{ basename($config->logo_path) }}</small>
                                        @endif
                                        @error('logo')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Footer Text -->
                        <div class="mb-4">
                            <label for="footer_text" class="form-label">Texte du pied de page</label>
                            <textarea class="form-control @error('footer_text') is-invalid @enderror"
                                      id="footer_text" name="footer_text" rows="3">{{ old('footer_text', $config->footer_text ?? '') }}</textarea>
                            @error('footer_text')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Custom CSS -->
                        <div class="mb-4">
                            <label for="custom_css" class="form-label">CSS personnalisé</label>
                            <textarea class="form-control @error('custom_css') is-invalid @enderror"
                                      id="custom_css" name="custom_css" rows="10">{{ old('custom_css', $config->custom_css ?? '') }}</textarea>
                            @error('custom_css')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Custom JS -->
                        <div class="mb-4">
                            <label for="custom_js" class="form-label">JavaScript personnalisé</label>
                            <textarea class="form-control @error('custom_js') is-invalid @enderror"
                                      id="custom_js" name="custom_js" rows="10">{{ old('custom_js', $config->custom_js ?? '') }}</textarea>
                            @error('custom_js')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="d-flex justify-content-between">
                            <a href="{{ route('admin.dashboard') }}" class="btn btn-secondary">
                                <i class="fas fa-arrow-left me-1"></i> Retour
                            </a>
                            <div>
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-save me-1"></i> Enregistrer
                                </button>
                                <a href="{{ route('admin.opac.preview') }}" class="btn btn-outline-info" target="_blank">
                                    <i class="fas fa-eye me-1"></i> Prévisualiser
                                </a>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
