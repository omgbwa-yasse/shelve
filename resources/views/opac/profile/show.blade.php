@extends('opac.layouts.app')

@section('title', __('My Profile') . ' - OPAC')

@section('content')
<div class="container my-5" style="max-width:700px;">
    <div class="mb-4 d-flex align-items-center gap-3">
        <a href="{{ route('opac.dashboard') }}" class="btn btn-sm btn-opac-outline">
            <i class="fas fa-arrow-left me-1"></i>{{ __('Dashboard') }}
        </a>
        <h1 class="h3 mb-0">{{ __('My Profile') }}</h1>
    </div>

    @if(session('status'))
        <div class="alert alert-success mb-4"><i class="fas fa-check-circle me-2"></i>{{ session('status') }}</div>
    @endif

    <div class="opac-card">
        <div class="opac-card-header"><i class="fas fa-user-edit"></i> {{ __('Personal Information') }}</div>
        <div class="card-body opac-card-body">
            <form method="POST" action="{{ route('opac.profile.update') }}">
                @csrf @method('PUT')

                <div class="row g-3">
                    <div class="col-md-6">
                        <label for="name" class="form-label fw-semibold">{{ __('Last Name') }} <span class="text-danger">*</span></label>
                        <input type="text" name="name" id="name"
                            class="form-control @error('name') is-invalid @enderror"
                            value="{{ old('name', $user->name) }}" required>
                        @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="col-md-6">
                        <label for="first_name" class="form-label fw-semibold">{{ __('First Name') }}</label>
                        <input type="text" name="first_name" id="first_name"
                            class="form-control @error('first_name') is-invalid @enderror"
                            value="{{ old('first_name', $user->first_name) }}">
                        @error('first_name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="col-12">
                        <label for="email" class="form-label fw-semibold">{{ __('Email') }} <span class="text-danger">*</span></label>
                        <input type="email" name="email" id="email"
                            class="form-control @error('email') is-invalid @enderror"
                            value="{{ old('email', $user->email) }}" required>
                        @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="col-md-6">
                        <label for="phone1" class="form-label fw-semibold">{{ __('Phone') }}</label>
                        <input type="text" name="phone" id="phone1"
                            class="form-control @error('phone') is-invalid @enderror"
                            value="{{ old('phone', $user->phone1) }}">
                        @error('phone')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="col-12">
                        <label for="address" class="form-label fw-semibold">{{ __('Address') }}</label>
                        <textarea name="address" id="address" rows="3"
                            class="form-control @error('address') is-invalid @enderror">{{ old('address', $user->address) }}</textarea>
                        @error('address')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                </div>

                <div class="mt-4 d-flex gap-2">
                    <button type="submit" class="btn btn-opac-primary">
                        <i class="fas fa-save me-2"></i>{{ __('Save Changes') }}
                    </button>
                    <a href="{{ route('opac.dashboard') }}" class="btn btn-opac-outline">{{ __('Cancel') }}</a>
                </div>
            </form>
        </div>
    </div>

    {{-- Account info (read-only) --}}
    <div class="opac-card mt-4">
        <div class="opac-card-header"><i class="fas fa-info-circle"></i> {{ __('Account Information') }}</div>
        <div class="card-body opac-card-body">
            <div class="row g-2 text-sm">
                <div class="col-md-6">
                    <span class="text-muted">{{ __('Member since') }}</span><br>
                    <strong>{{ $user->created_at->format('d/m/Y') }}</strong>
                </div>
                <div class="col-md-6">
                    <span class="text-muted">{{ __('Account status') }}</span><br>
                    @if($user->is_approved)
                        <span class="badge bg-success">{{ __('Approved') }}</span>
                    @else
                        <span class="badge bg-warning text-dark">{{ __('Pending approval') }}</span>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
