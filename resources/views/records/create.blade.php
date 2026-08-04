@extends('layouts.app')

@section('content')
    <div class="container-fluid">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1><i class="bi bi-plus-circle"></i> Nouvelle notice</h1>
            <a href="{{ route('records.index') }}" class="btn btn-outline-secondary">Retour</a>
        </div>

        @if($errors->any())
            <div class="alert alert-danger">
                <ul class="mb-0">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
            </div>
        @endif

        <div class="card">
            <div class="card-body">
                <form method="POST" action="{{ route('records.store') }}" enctype="multipart/form-data">
                    @csrf
                    @include('records.partials.form-fields', ['record' => null])
                    <div class="mt-4">
                        <button class="btn btn-primary"><i class="bi bi-check-lg"></i> Créer la notice</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        (function () {
            const select = document.getElementById('recordTypeSelect');
            const container = document.getElementById('dynamicMetadataFields');

            if (!select || !container) {
                return;
            }

            const fieldTpl = {
                select: function (f) {
                    let opts = '<option value="">—</option>';
                    (f.options || []).forEach(function (o) {
                        const v = typeof o === 'object' ? (o.code || o.value || '') : o;
                        const l = typeof o === 'object' ? (o.value || o.code || '') : o;
                        opts += '<option value="' + v + '">' + l + '</option>';
                    });
                    return '<select name="metadata[' + f.code + ']" class="form-select"' + (f.mandatory ? ' required' : '') + '>' + opts + '</select>';
                },
                boolean: function (f) {
                    return '<select name="metadata[' + f.code + ']" class="form-select">' +
                        '<option value="0">Non</option><option value="1">Oui</option></select>';
                },
                textarea: function (f) {
                    return '<textarea name="metadata[' + f.code + ']" class="form-control" rows="2"' + (f.mandatory ? ' required' : '') + '></textarea>';
                },
                date: function (f) {
                    return '<input type="date" name="metadata[' + f.code + ']" class="form-control">';
                },
                number: function (f) {
                    return '<input type="number" name="metadata[' + f.code + ']" class="form-control"' + (f.mandatory ? ' required' : '') + '>';
                }
            };

            function buildField(f) {
                let input;
                if (['select', 'multi_select', 'reference_list'].includes(f.data_type)) {
                    input = fieldTpl.select(f);
                } else if (f.data_type === 'boolean') {
                    input = fieldTpl.boolean(f);
                } else if (f.data_type === 'textarea') {
                    input = fieldTpl.textarea(f);
                } else if (f.data_type === 'date') {
                    input = fieldTpl.date(f);
                } else {
                    const t = f.data_type === 'number' ? 'number' : 'text';
                    input = '<input type="' + t + '" name="metadata[' + f.code + ']" class="form-control"' + (f.mandatory ? ' required' : '') + '>';
                }
                return '<div class="col-md-6"><label class="form-label">' + f.name + (f.mandatory ? ' <span class="text-danger">*</span>' : '') + '</label>' + input + '</div>';
            }

            function loadFields(typeId) {
                if (!typeId) {
                    container.innerHTML = '';
                    return;
                }
                fetch('{{ route('records.type-metadata-fields') }}?type_id=' + typeId, {
                    headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
                })
                .then(r => r.json())
                .then(fields => {
                    if (!fields.length) {
                        container.innerHTML = '';
                        return;
                    }
                    container.innerHTML = '<hr><h6>Métadonnées du type</h6><div class="row g-3">' +
                        fields.map(buildField).join('') + '</div>';
                })
                .catch(() => { container.innerHTML = ''; });
            }

            select.addEventListener('change', function () {
                loadFields(this.value);
            });

            if (select.value) {
                loadFields(select.value);
            }
        })();
    </script>
@endpush
