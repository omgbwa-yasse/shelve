@php($account = $account ?? null)

<div class="row g-3">
    <div class="col-md-6">
        <label class="form-label">Nom du compte <span class="text-danger">*</span></label>
        <input type="text" name="name" value="{{ old('name', $account?->name) }}" class="form-control" required>
    </div>
    <div class="col-md-6">
        <label class="form-label">Adresse email <span class="text-danger">*</span></label>
        <input type="email" name="email_address" value="{{ old('email_address', $account?->email_address) }}" class="form-control" required>
    </div>
    <div class="col-md-6">
        <label class="form-label">Nom d'expéditeur affiché</label>
        <input type="text" name="default_from_name" value="{{ old('default_from_name', $account?->default_from_name) }}" class="form-control">
    </div>
    <div class="col-md-6 d-flex align-items-end">
        <div class="form-check">
            <input type="checkbox" name="is_active" value="1" class="form-check-input" id="isActive" @checked(old('is_active', $account?->is_active ?? true))>
            <label class="form-check-label" for="isActive">Compte actif (synchronisé automatiquement)</label>
        </div>
    </div>
</div>

<hr>
<h6><i class="bi bi-inbox"></i> Réception (IMAP)</h6>
<div class="row g-3">
    <div class="col-md-5">
        <label class="form-label">Serveur IMAP <span class="text-danger">*</span></label>
        <input type="text" name="imap_host" value="{{ old('imap_host', $account?->imap_host) }}" class="form-control" placeholder="imap.exemple.com" required>
    </div>
    <div class="col-md-2">
        <label class="form-label">Port <span class="text-danger">*</span></label>
        <input type="number" name="imap_port" value="{{ old('imap_port', $account?->imap_port ?? 993) }}" class="form-control" required>
    </div>
    <div class="col-md-2">
        <label class="form-label">Chiffrement</label>
        <select name="imap_encryption" class="form-select">
            @foreach(['ssl' => 'SSL', 'tls' => 'TLS', 'none' => 'Aucun'] as $val => $label)
                <option value="{{ $val }}" @selected(old('imap_encryption', $account?->imap_encryption ?? 'ssl') == $val)>{{ $label }}</option>
            @endforeach
        </select>
    </div>
    <div class="col-md-3">
        <label class="form-label">Identifiant <span class="text-danger">*</span></label>
        <input type="text" name="imap_username" value="{{ old('imap_username', $account?->imap_username) }}" class="form-control" required>
    </div>
    <div class="col-md-6">
        <label class="form-label">Mot de passe {{ $account ? '' : '*' }}</label>
        <input type="password" name="imap_password" class="form-control" autocomplete="new-password" {{ $account ? '' : 'required' }}>
        @if($account)<div class="form-text">Laisser vide pour conserver le mot de passe actuel.</div>@endif
    </div>
</div>

<hr>
<h6><i class="bi bi-send"></i> Envoi (SMTP)</h6>
<div class="row g-3">
    <div class="col-md-5">
        <label class="form-label">Serveur SMTP <span class="text-danger">*</span></label>
        <input type="text" name="smtp_host" value="{{ old('smtp_host', $account?->smtp_host) }}" class="form-control" placeholder="smtp.exemple.com" required>
    </div>
    <div class="col-md-2">
        <label class="form-label">Port <span class="text-danger">*</span></label>
        <input type="number" name="smtp_port" value="{{ old('smtp_port', $account?->smtp_port ?? 587) }}" class="form-control" required>
    </div>
    <div class="col-md-2">
        <label class="form-label">Chiffrement</label>
        <select name="smtp_encryption" class="form-select">
            @foreach(['ssl' => 'SSL', 'tls' => 'TLS', 'none' => 'Aucun'] as $val => $label)
                <option value="{{ $val }}" @selected(old('smtp_encryption', $account?->smtp_encryption ?? 'tls') == $val)>{{ $label }}</option>
            @endforeach
        </select>
    </div>
    <div class="col-md-3">
        <label class="form-label">Identifiant <span class="text-danger">*</span></label>
        <input type="text" name="smtp_username" value="{{ old('smtp_username', $account?->smtp_username) }}" class="form-control" required>
    </div>
    <div class="col-md-6">
        <label class="form-label">Mot de passe {{ $account ? '' : '*' }}</label>
        <input type="password" name="smtp_password" class="form-control" autocomplete="new-password" {{ $account ? '' : 'required' }}>
        @if($account)<div class="form-text">Laisser vide pour conserver le mot de passe actuel.</div>@endif
    </div>
</div>
