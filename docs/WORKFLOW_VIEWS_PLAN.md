# 🎨 Plan Création Vues Workflow - Phase 3

**Objectif**: Créer l'interface utilisateur complète pour les fonctionnalités workflow des documents digitaux.

---

## 📋 Fichiers à Créer (7 fichiers)

### 1. Partials Workflow

```
resources/views/repositories/documents/partials/
├── checkout.blade.php         ← Gestion réservation
├── signature.blade.php        ← Gestion signature électronique
├── workflow.blade.php         ← Approbation/Rejet
└── version-actions.blade.php  ← Actions versions
```

### 2. Modales

```
resources/views/repositories/documents/modals/
├── checkin-modal.blade.php    ← Upload nouvelle version
├── sign-modal.blade.php       ← Signature avec mot de passe
└── revoke-modal.blade.php     ← Révocation signature
```

---

## 🔧 Spécifications Détaillées

### Partial 1: `checkout.blade.php`

**Emplacement**: Sidebar document.show  
**Visibilité**: Seulement si version courante

#### États et Actions

**État 1: Document libre**
```blade
✅ Badge: "Disponible"
🔵 Bouton: "Réserver le document"
   → POST /documents/{id}/checkout
   → Refresh page
```

**État 2: Document réservé par moi**
```blade
🟡 Badge: "Réservé par vous depuis le [date]"
🔵 Bouton: "Déposer une nouvelle version"
   → Ouvre modal-checkin (upload file)
🔴 Bouton: "Annuler la réservation"
   → POST /documents/{id}/cancel-checkout (confirmation)
```

**État 3: Document réservé par autre utilisateur**
```blade
🔴 Badge: "Réservé par [nom] depuis le [date]"
ℹ️ Texte: "Ce document n'est pas disponible pour modification"
❌ Aucun bouton (sauf admin peut cancel)
```

#### Code Structure

```blade
{{-- resources/views/repositories/documents/partials/checkout.blade.php --}}
<div class="card mb-3">
    <div class="card-header bg-info text-white">
        <i class="fas fa-lock"></i> Réservation Document
    </div>
    <div class="card-body">
        @if(!$document->is_current_version)
            <div class="alert alert-warning">
                <small>Seule la version courante peut être réservée.</small>
            </div>
        @elseif(!$document->isCheckedOut())
            {{-- État 1: Libre --}}
            <span class="badge badge-success mb-2">
                <i class="fas fa-unlock"></i> Disponible
            </span>
            <form action="{{ route('documents.checkout', $document) }}" method="POST">
                @csrf
                <button type="submit" class="btn btn-primary btn-sm btn-block">
                    <i class="fas fa-lock"></i> Réserver le document
                </button>
            </form>
            <small class="text-muted mt-2 d-block">
                La réservation empêche les autres utilisateurs de modifier ce document.
            </small>
        @elseif($document->isCheckedOutBy(Auth::user()))
            {{-- État 2: Réservé par moi --}}
            <span class="badge badge-warning mb-2">
                <i class="fas fa-user-lock"></i> Réservé par vous
            </span>
            <p class="small text-muted mb-2">
                Depuis le {{ $document->checked_out_at->format('d/m/Y à H:i') }}
            </p>
            
            {{-- Bouton Checkin --}}
            <button type="button" class="btn btn-success btn-sm btn-block mb-2" 
                    data-toggle="modal" data-target="#checkinModal">
                <i class="fas fa-upload"></i> Déposer une nouvelle version
            </button>
            
            {{-- Bouton Cancel --}}
            <form action="{{ route('documents.cancel-checkout', $document) }}" method="POST">
                @csrf
                <button type="submit" class="btn btn-outline-danger btn-sm btn-block"
                        onclick="return confirm('Annuler la réservation sans déposer de version ?')">
                    <i class="fas fa-times"></i> Annuler la réservation
                </button>
            </form>
        @else
            {{-- État 3: Réservé par autre --}}
            <span class="badge badge-danger mb-2">
                <i class="fas fa-user-lock"></i> Réservé
            </span>
            <p class="small mb-0">
                Par <strong>{{ $document->checkedOutUser->name }}</strong><br>
                Depuis le {{ $document->checked_out_at->format('d/m/Y à H:i') }}
            </p>
            <div class="alert alert-info mt-2 mb-0">
                <small>Ce document n'est pas disponible pour modification.</small>
            </div>
        @endif
    </div>
</div>

{{-- Inclure la modale checkin --}}
@include('repositories.documents.modals.checkin-modal')
```

---

### Partial 2: `signature.blade.php`

**Emplacement**: Sidebar document.show  
**Visibilité**: Seulement si version courante

#### États et Actions

**État 1: Document non signé**
```blade
⚪ Badge: "Non signé"
🔵 Bouton: "Signer électroniquement"
   → Ouvre modal-sign (password + reason)
```

**État 2: Document signé par moi**
```blade
🟢 Badge: "Signé par vous"
📅 Date: "Le [date]"
🔍 Bouton: "Vérifier la signature"
   → POST /documents/{id}/verify-signature
🔴 Bouton: "Révoquer ma signature"
   → Ouvre modal-revoke (reason required)
```

**État 3: Document signé par autre utilisateur**
```blade
🟢 Badge: "Signé par [nom]"
📅 Date: "Le [date]"
🔍 Bouton: "Vérifier la signature"
   → POST /documents/{id}/verify-signature
```

#### Code Structure

```blade
{{-- resources/views/repositories/documents/partials/signature.blade.php --}}
<div class="card mb-3">
    <div class="card-header bg-success text-white">
        <i class="fas fa-signature"></i> Signature Électronique
    </div>
    <div class="card-body">
        @if(!$document->is_current_version)
            <div class="alert alert-warning">
                <small>Seule la version courante peut être signée.</small>
            </div>
        @elseif($document->isCheckedOut())
            <div class="alert alert-warning">
                <small>Impossible de signer un document réservé.</small>
            </div>
        @elseif($document->signature_status === 'unsigned')
            {{-- État 1: Non signé --}}
            <span class="badge badge-secondary mb-2">
                <i class="fas fa-file"></i> Non signé
            </span>
            <button type="button" class="btn btn-success btn-sm btn-block"
                    data-toggle="modal" data-target="#signModal">
                <i class="fas fa-signature"></i> Signer électroniquement
            </button>
            <small class="text-muted mt-2 d-block">
                La signature garantit l'authenticité et l'intégrité du document.
            </small>
        @elseif($document->signature_status === 'signed')
            {{-- États 2 & 3: Signé --}}
            <span class="badge badge-success mb-2">
                <i class="fas fa-check-circle"></i> Document signé
            </span>
            <p class="small mb-2">
                <strong>Par:</strong> {{ $document->signer->name }}<br>
                <strong>Le:</strong> {{ $document->signed_at->format('d/m/Y à H:i') }}<br>
                @if($document->signature_data)
                    <strong>Raison:</strong> {{ $document->signature_data }}<br>
                @endif
                <strong>Hash:</strong> <code class="small">{{ Str::limit($document->signature_hash, 16) }}</code>
            </p>
            
            {{-- Bouton Verify --}}
            <form action="{{ route('documents.verify-signature', $document) }}" method="POST" class="mb-2">
                @csrf
                <button type="submit" class="btn btn-info btn-sm btn-block">
                    <i class="fas fa-shield-alt"></i> Vérifier la signature
                </button>
            </form>
            
            {{-- Bouton Revoke (si signataire) --}}
            @if($document->signed_by === Auth::id())
                <button type="button" class="btn btn-outline-danger btn-sm btn-block"
                        data-toggle="modal" data-target="#revokeModal">
                    <i class="fas fa-ban"></i> Révoquer ma signature
                </button>
            @endif
        @elseif($document->signature_status === 'revoked')
            {{-- État 4: Révoquée --}}
            <span class="badge badge-danger mb-2">
                <i class="fas fa-exclamation-triangle"></i> Signature révoquée
            </span>
            <p class="small mb-0">
                <strong>Le:</strong> {{ $document->signature_revoked_at->format('d/m/Y') }}<br>
                <strong>Raison:</strong> {{ $document->signature_revocation_reason }}
            </p>
        @endif
    </div>
</div>

{{-- Inclure les modales --}}
@include('repositories.documents.modals.sign-modal')
@include('repositories.documents.modals.revoke-modal')
```

---

### Partial 3: `workflow.blade.php`

**Emplacement**: Sidebar document.show  
**Visibilité**: Seulement si `requires_approval = true`

#### États et Actions

```blade
{{-- resources/views/repositories/documents/partials/workflow.blade.php --}}
<div class="card mb-3">
    <div class="card-header bg-primary text-white">
        <i class="fas fa-tasks"></i> Workflow Approbation
    </div>
    <div class="card-body">
        @if(!$document->requires_approval)
            <p class="text-muted small mb-0">Ce document ne nécessite pas d'approbation.</p>
        @elseif($document->approved_at)
            {{-- Approuvé --}}
            <span class="badge badge-success mb-2">
                <i class="fas fa-check-circle"></i> Approuvé
            </span>
            <p class="small mb-0">
                <strong>Par:</strong> {{ $document->approver->name }}<br>
                <strong>Le:</strong> {{ $document->approved_at->format('d/m/Y à H:i') }}
                @if($document->approval_notes)
                    <br><strong>Notes:</strong> {{ $document->approval_notes }}
                @endif
            </p>
        @else
            {{-- En attente approbation --}}
            <span class="badge badge-warning mb-3">
                <i class="fas fa-clock"></i> En attente d'approbation
            </span>
            
            {{-- Formulaire Approve --}}
            <form action="{{ route('documents.approve', $document) }}" method="POST" class="mb-2">
                @csrf
                <div class="form-group">
                    <label class="small">Notes d'approbation (optionnel)</label>
                    <textarea name="approval_notes" class="form-control form-control-sm" rows="2"></textarea>
                </div>
                <button type="submit" class="btn btn-success btn-sm btn-block">
                    <i class="fas fa-check"></i> Approuver
                </button>
            </form>
            
            {{-- Formulaire Reject --}}
            <button type="button" class="btn btn-outline-danger btn-sm btn-block" 
                    data-toggle="collapse" data-target="#rejectForm">
                <i class="fas fa-times"></i> Rejeter
            </button>
            <div id="rejectForm" class="collapse mt-2">
                <form action="{{ route('documents.reject', $document) }}" method="POST">
                    @csrf
                    <div class="form-group">
                        <label class="small">Raison du rejet (requis)</label>
                        <textarea name="rejection_reason" class="form-control form-control-sm" 
                                  rows="3" required></textarea>
                    </div>
                    <button type="submit" class="btn btn-danger btn-sm btn-block">
                        <i class="fas fa-ban"></i> Confirmer le rejet
                    </button>
                </form>
            </div>
        @endif
    </div>
</div>
```

---

### Partial 4: `version-actions.blade.php`

**Emplacement**: Page versions.blade.php  
**Contexte**: Liste des versions avec actions par version

```blade
{{-- resources/views/repositories/documents/partials/version-actions.blade.php --}}
{{-- 
    Variables attendues:
    - $version: Instance RecordDigitalDocument
    - $currentDocument: Version courante
--}}

<div class="btn-group btn-group-sm" role="group">
    {{-- Download --}}
    @if($version->attachment)
        <a href="{{ route('documents.versions.download', [$currentDocument, $version->version_number]) }}" 
           class="btn btn-outline-primary" title="Télécharger">
            <i class="fas fa-download"></i>
        </a>
    @endif
    
    {{-- Restore (si pas version courante) --}}
    @if(!$version->is_current_version)
        <form action="{{ route('documents.versions.restore', [$currentDocument, $version->version_number]) }}" 
              method="POST" class="d-inline">
            @csrf
            <button type="submit" class="btn btn-outline-success" title="Restaurer cette version"
                    onclick="return confirm('Restaurer la version {{ $version->version_number }} ? Cela créera une nouvelle version.')">
                <i class="fas fa-undo"></i>
            </button>
        </form>
    @else
        <span class="btn btn-success" title="Version courante">
            <i class="fas fa-check"></i> Actuelle
        </span>
    @endif
</div>
```

---

## 🔔 Modales à Créer

### Modal 1: `checkin-modal.blade.php`

```blade
{{-- resources/views/repositories/documents/modals/checkin-modal.blade.php --}}
<div class="modal fade" id="checkinModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <form action="{{ route('documents.checkin', $document) }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="modal-header bg-success text-white">
                    <h5 class="modal-title">
                        <i class="fas fa-upload"></i> Déposer une nouvelle version
                    </h5>
                    <button type="button" class="close text-white" data-dismiss="modal">
                        <span>&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-info">
                        <small>
                            <i class="fas fa-info-circle"></i>
                            Cette action créera automatiquement la version <strong>{{ $document->version_number + 1 }}</strong> 
                            et libérera la réservation.
                        </small>
                    </div>
                    
                    {{-- Upload File --}}
                    <div class="form-group">
                        <label>Fichier <span class="text-danger">*</span></label>
                        <input type="file" name="file" class="form-control-file" required>
                        <small class="form-text text-muted">
                            Taille max: 50 MB
                            @if($document->type->allowed_mime_types)
                                <br>Types acceptés: {{ implode(', ', json_decode($document->type->allowed_mime_types)) }}
                            @endif
                        </small>
                    </div>
                    
                    {{-- Version Notes --}}
                    <div class="form-group">
                        <label>Notes de version</label>
                        <textarea name="checkin_notes" class="form-control" rows="3" 
                                  placeholder="Décrivez les modifications apportées..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Annuler</button>
                    <button type="submit" class="btn btn-success">
                        <i class="fas fa-check"></i> Déposer la version
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
```

### Modal 2: `sign-modal.blade.php`

```blade
{{-- resources/views/repositories/documents/modals/sign-modal.blade.php --}}
<div class="modal fade" id="signModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <form action="{{ route('documents.sign', $document) }}" method="POST">
                @csrf
                <div class="modal-header bg-success text-white">
                    <h5 class="modal-title">
                        <i class="fas fa-signature"></i> Signature Électronique
                    </h5>
                    <button type="button" class="close text-white" data-dismiss="modal">
                        <span>&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-warning">
                        <i class="fas fa-exclamation-triangle"></i>
                        <strong>Attention:</strong> La signature électronique garantit que vous approuvez 
                        l'intégrité et le contenu de ce document. Cette action est irréversible (sauf révocation).
                    </div>
                    
                    {{-- Password Confirmation --}}
                    <div class="form-group">
                        <label>Votre mot de passe <span class="text-danger">*</span></label>
                        <input type="password" name="signature_password" class="form-control" 
                               placeholder="Confirmez votre identité" required autofocus>
                        <small class="form-text text-muted">
                            Requis pour valider votre signature électronique.
                        </small>
                    </div>
                    
                    {{-- Signature Reason --}}
                    <div class="form-group">
                        <label>Raison de la signature (optionnel)</label>
                        <input type="text" name="signature_reason" class="form-control" 
                               placeholder="Ex: Validation technique, Approbation managériale...">
                    </div>
                    
                    {{-- Info Signature --}}
                    <div class="card bg-light">
                        <div class="card-body p-2">
                            <small class="text-muted">
                                <strong>Informations de signature:</strong><br>
                                <i class="fas fa-user"></i> Signataire: {{ Auth::user()->name }}<br>
                                <i class="fas fa-calendar"></i> Date: {{ now()->format('d/m/Y à H:i') }}<br>
                                <i class="fas fa-hashtag"></i> Hash: SHA256 (calculé automatiquement)
                            </small>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Annuler</button>
                    <button type="submit" class="btn btn-success">
                        <i class="fas fa-pen"></i> Signer le document
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
```

### Modal 3: `revoke-modal.blade.php`

```blade
{{-- resources/views/repositories/documents/modals/revoke-modal.blade.php --}}
<div class="modal fade" id="revokeModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <form action="{{ route('documents.revoke-signature', $document) }}" method="POST">
                @csrf
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title">
                        <i class="fas fa-ban"></i> Révoquer la signature
                    </h5>
                    <button type="button" class="close text-white" data-dismiss="modal">
                        <span>&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-danger">
                        <i class="fas fa-exclamation-circle"></i>
                        <strong>Action critique:</strong> La révocation invalide la signature électronique. 
                        Le document ne sera plus considéré comme signé.
                    </div>
                    
                    {{-- Revocation Reason --}}
                    <div class="form-group">
                        <label>Raison de la révocation <span class="text-danger">*</span></label>
                        <textarea name="revocation_reason" class="form-control" rows="3" 
                                  placeholder="Expliquez pourquoi vous révoquez cette signature..." 
                                  required></textarea>
                        <small class="form-text text-muted">
                            Cette raison sera enregistrée et visible dans l'historique.
                        </small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Annuler</button>
                    <button type="submit" class="btn btn-danger">
                        <i class="fas fa-trash-alt"></i> Révoquer définitivement
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
```

---

## 🔗 Intégration dans `show.blade.php`

### Modification à faire

```blade
{{-- resources/views/repositories/documents/show.blade.php --}}
@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row">
        {{-- Colonne principale (8/12) --}}
        <div class="col-md-8">
            {{-- Header document --}}
            <div class="card mb-3">
                <div class="card-header">
                    <h4>{{ $document->name }}</h4>
                    <small class="text-muted">Code: {{ $document->code }}</small>
                </div>
                <div class="card-body">
                    {{-- Informations document existantes --}}
                </div>
            </div>
            
            {{-- Versions history --}}
            <div class="card">
                <div class="card-header">
                    <i class="fas fa-history"></i> Historique des versions
                </div>
                <div class="card-body">
                    <table class="table table-sm">
                        {{-- Liste versions avec version-actions partial --}}
                        @foreach($versions as $version)
                        <tr>
                            <td>v{{ $version->version_number }}</td>
                            <td>{{ $version->created_at->format('d/m/Y H:i') }}</td>
                            <td>{{ $version->creator->name }}</td>
                            <td class="text-right">
                                @include('repositories.documents.partials.version-actions', [
                                    'version' => $version,
                                    'currentDocument' => $document
                                ])
                            </td>
                        </tr>
                        @endforeach
                    </table>
                </div>
            </div>
        </div>
        
        {{-- Sidebar workflow (4/12) --}}
        <div class="col-md-4">
            {{-- Actions rapides --}}
            <div class="card mb-3">
                <div class="card-body">
                    <a href="{{ route('documents.download', $document) }}" class="btn btn-primary btn-block">
                        <i class="fas fa-download"></i> Télécharger
                    </a>
                    <a href="{{ route('documents.edit', $document) }}" class="btn btn-secondary btn-block">
                        <i class="fas fa-edit"></i> Modifier
                    </a>
                </div>
            </div>
            
            {{-- Workflow Partials --}}
            @include('repositories.documents.partials.checkout')
            @include('repositories.documents.partials.signature')
            
            @if($document->requires_approval)
                @include('repositories.documents.partials.workflow')
            @endif
            
            {{-- Métadonnées --}}
            <div class="card">
                <div class="card-header">Informations</div>
                <div class="card-body">
                    {{-- Stats document --}}
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
```

---

## ✅ Checklist Création

### Partials
- [ ] checkout.blade.php (3 états)
- [ ] signature.blade.php (4 états)
- [ ] workflow.blade.php (2 états)
- [ ] version-actions.blade.php (actions inline)

### Modales
- [ ] checkin-modal.blade.php (upload + notes)
- [ ] sign-modal.blade.php (password + reason)
- [ ] revoke-modal.blade.php (reason required)

### Intégration
- [ ] Modifier show.blade.php (sidebar workflow)
- [ ] Modifier versions.blade.php (actions restore)
- [ ] Tester tous les états possibles

### Validation
- [ ] Test checkout libre → réserver → annuler
- [ ] Test checkout → checkin nouvelle version
- [ ] Test signature → verify → revoke
- [ ] Test restauration version ancienne
- [ ] Vérifier permissions (user vs admin)
- [ ] Vérifier messages flash (success/error)

---

**Prochaine commande**: Créer les 7 fichiers Blade ci-dessus.
