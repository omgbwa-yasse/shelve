@extends('layouts.app')

@section('content')
<div class="container-fluid px-4 py-3">

    {{-- Header --}}
    <div class="d-flex align-items-center gap-3 mb-4">
        <a href="{{ url()->previous() }}" class="btn btn-sm btn-outline-secondary">
            <i class="bi bi-arrow-left"></i>
        </a>
        <div class="d-flex align-items-center gap-2">
            <div style="width:38px;height:38px;background:#fff3cd;border-radius:10px;display:flex;align-items:center;justify-content:center;">
                <i class="bi bi-folder-plus text-warning" style="font-size:1.3rem;"></i>
            </div>
            <div>
                <h5 class="mb-0 fw-semibold">Nouveau dossier</h5>
                <div class="text-muted" style="font-size:.75rem;">Créer un dossier numérique</div>
            </div>
        </div>
    </div>

    @if($errors->any())
    <div class="alert alert-danger alert-dismissible fade show py-2 mb-3" style="font-size:.83rem;">
        <i class="bi bi-exclamation-triangle me-2"></i>
        <strong>Veuillez corriger les erreurs suivantes :</strong>
        <ul class="mb-0 mt-1 ps-3">
            @foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach
        </ul>
        <button type="button" class="btn-close py-2" data-bs-dismiss="alert"></button>
    </div>
    @endif

    <div class="row justify-content-center">
        <div class="col-xl-8 col-lg-10">
            <div class="card border-0 shadow-sm">
                <div class="card-body p-4">
                    <form action="{{ route('folders.store') }}" method="POST">
                        @csrf
                        @include('repositories.folders._form', ['isEdit' => false, 'folder' => null])

                        <div class="d-flex justify-content-end gap-2 mt-4 pt-3 border-top">
                            <a href="{{ url()->previous() }}" class="btn btn-outline-secondary btn-sm">Annuler</a>
                            <button type="submit" class="btn btn-primary btn-sm">
                                <i class="bi bi-folder-plus me-1"></i>Créer le dossier
                            </button>
                        </div>
                    </form>
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
