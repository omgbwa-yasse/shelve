@extends('layouts.app')

@section('content')
<div class="container-fluid px-4 py-3">

    {{-- Header --}}
    <div class="d-flex align-items-center gap-3 mb-4">
        <a href="{{ route('folders.show', $folder) }}" class="btn btn-sm btn-outline-secondary">
            <i class="bi bi-arrow-left"></i>
        </a>
        <div class="d-flex align-items-center gap-2">
            <div style="width:38px;height:38px;background:#fff3cd;border-radius:10px;display:flex;align-items:center;justify-content:center;">
                <i class="bi bi-folder-fill text-warning" style="font-size:1.3rem;"></i>
            </div>
            <div>
                <h5 class="mb-0 fw-semibold">Modifier le dossier</h5>
                <div class="text-muted text-truncate" style="font-size:.75rem;max-width:300px;">{{ $folder->name }}</div>
            </div>
        </div>
        <div class="ms-auto d-flex gap-2">
            <a href="{{ route('folders.show', $folder) }}" class="btn btn-sm btn-outline-primary">
                <i class="bi bi-eye me-1"></i>Voir
            </a>
        </div>
    </div>

    @if($errors->any())
    <div class="alert alert-danger alert-dismissible fade show py-2 mb-3" style="font-size:.83rem;">
        <i class="bi bi-exclamation-triangle me-2"></i>
        <strong>Veuillez corriger les erreurs :</strong>
        <ul class="mb-0 mt-1 ps-3">
            @foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach
        </ul>
        <button type="button" class="btn-close py-2" data-bs-dismiss="alert"></button>
    </div>
    @endif

    <div class="row justify-content-center">
        <div class="col-xl-8 col-lg-10">

            {{-- Formulaire principal --}}
            <div class="card border-0 shadow-sm mb-3">
                <div class="card-body p-4">
                    <form action="{{ route('folders.update', $folder) }}" method="POST">
                        @csrf @method('PUT')
                        @include('repositories.folders._form', ['isEdit' => true])

                        <div class="d-flex justify-content-end gap-2 mt-4 pt-3 border-top">
                            <a href="{{ route('folders.show', $folder) }}" class="btn btn-outline-secondary btn-sm">Annuler</a>
                            <button type="submit" class="btn btn-primary btn-sm">
                                <i class="bi bi-check2 me-1"></i>Enregistrer les modifications
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            {{-- Infos système --}}
            <div class="card border-0 shadow-sm">
                <div class="card-header py-2" style="background:#fafbfc;border-bottom:1px solid #eef0f3;">
                    <span class="fw-semibold small"><i class="bi bi-info-circle me-2 text-muted"></i>Informations système</span>
                </div>
                <div class="card-body p-0">
                    <table class="table table-sm mb-0" style="font-size:.78rem;">
                        <tbody>
                            <tr><td class="text-muted ps-3 py-2 border-0" style="width:140px;">Code</td><td class="py-2 border-0"><code style="font-size:.73rem;">{{ $folder->code }}</code></td></tr>
                            <tr><td class="text-muted ps-3 py-2">Créé par</td><td class="py-2">{{ $folder->creator->name ?? '—' }}</td></tr>
                            <tr><td class="text-muted ps-3 py-2">Créé le</td><td class="py-2">{{ $folder->created_at?->format('d/m/Y H:i') }}</td></tr>
                            <tr><td class="text-muted ps-3 py-2">Modifié le</td><td class="py-2">{{ $folder->updated_at?->format('d/m/Y H:i') }}</td></tr>
                            <tr>
                                <td class="text-muted ps-3 py-2">Contenu</td>
                                <td class="py-2">
                                    <span class="badge bg-primary me-1">{{ $folder->documents_count }} doc</span>
                                    <span class="badge bg-info me-1">{{ $folder->children_count ?? $folder->subfolders_count ?? 0 }} sous-dossiers</span>
                                    <span class="badge bg-light text-dark border">{{ number_format(($folder->total_size ?? 0) / 1024 / 1024, 2) }} MB</span>
                                </td>
                            </tr>
                            @if($folder->requires_approval && $folder->approved_by)
                            <tr><td class="text-muted ps-3 py-2">Approbation</td><td class="py-2 text-success"><i class="bi bi-check2-circle me-1"></i>Approuvé par {{ $folder->approver->name ?? '—' }} le {{ $folder->approved_at?->format('d/m/Y') }}</td></tr>
                            @endif
                        </tbody>
                    </table>
                </div>
            </div>

        </div>
    </div>

</div>
@endsection

@push('scripts')
<script>
function loadMetadata(typeId) {
    const container = document.getElementById('metadata-container');
    const fieldsDiv = document.getElementById('metadata-fields');
    if (!typeId) { container.style.display = 'none'; fieldsDiv.innerHTML = ''; return; }
    fetch(`/api/v1/metadata/folder-types/${typeId}`, {
        headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '' }
    })
    .then(r => r.json())
    .then(data => {
        if (data.success && data.data?.length > 0) {
            container.style.display = 'block';
            renderMetadataFields(data.data);
        } else {
            container.style.display = 'none';
            fieldsDiv.innerHTML = '';
        }
    })
    .catch(() => { container.style.display = 'none'; fieldsDiv.innerHTML = ''; });
}

function renderMetadataFields(metadata) {
    const fieldsDiv = document.getElementById('metadata-fields');
    fieldsDiv.innerHTML = '';
    metadata.forEach(field => {
        if (!field.visible) return;
        const col = document.createElement('div');
        col.className = 'col-md-6';
        const req = field.mandatory ? 'required' : '';
        const ro  = field.readonly  ? 'readonly'  : '';
        const fn  = `metadata[${field.name}]`;
        let inp;
        switch (field.data_type) {
            case 'textarea': inp = `<textarea class="form-control form-control-sm" name="${fn}" rows="2" ${req} ${ro}>${field.default_value||''}</textarea>`; break;
            case 'number':   inp = `<input type="number" class="form-control form-control-sm" name="${fn}" value="${field.default_value||''}" ${req} ${ro}>`; break;
            case 'date':     inp = `<input type="date"   class="form-control form-control-sm" name="${fn}" value="${field.default_value||''}" ${req} ${ro}>`; break;
            case 'boolean':  inp = `<div class="form-check"><input type="checkbox" class="form-check-input" name="${fn}" value="1" ${field.default_value?'checked':''}></div>`; break;
            case 'select':
            case 'reference_list':
                if (field.reference_list?.values) {
                    let opts = '<option value="">--</option>';
                    field.reference_list.values.forEach(v => { opts += `<option value="${v.value}" ${v.value===field.default_value?'selected':''}>${v.display_value}</option>`; });
                    inp = `<select class="form-select form-select-sm" name="${fn}" ${req}>${opts}</select>`;
                } else { inp = `<input type="text" class="form-control form-control-sm" name="${fn}" value="${field.default_value||''}" ${req}>`; }
                break;
            default: inp = `<input type="text" class="form-control form-control-sm" name="${fn}" value="${field.default_value||''}" ${req} ${ro}>`;
        }
        col.innerHTML = `<label class="form-label fw-semibold small">${field.name}${field.mandatory?' <span class="text-danger">*</span>':''}</label>${inp}${field.description?`<div class="form-text">${field.description}</div>`:''}`;
        fieldsDiv.appendChild(col);
    });
}

document.addEventListener('DOMContentLoaded', function() {
    const t = document.getElementById('type_id');
    if (t?.value) loadMetadata(t.value);
});
</script>
@endpush
