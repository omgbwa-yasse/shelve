@extends('layouts.app')

@section('content')
    <div class="container-fluid">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1><i class="bi bi-diagram-3"></i> Arbre des notices</h1>
            <a href="{{ route('records.index') }}" class="btn btn-outline-secondary">Retour</a>
        </div>

        <div class="card mb-3">
            <div class="card-body">
                <div class="form-check form-switch">
                    <input class="form-check-input" type="checkbox" id="containerOnly" checked>
                    <label class="form-check-label" for="containerOnly">Dossiers uniquement (conteneurs)</label>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-body" id="tree"></div>
        </div>
    </div>
@endsection

@push('scripts')
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/jstree@3.3.16/dist/themes/default/style.min.css">
    <script src="https://cdn.jsdelivr.net/npm/jstree@3.3.16/dist/jstree.min.js"></script>
    <script>
        function loadTree() {
            const containerOnly = document.getElementById('containerOnly').checked ? '1' : '0';
            fetch(`{{ route('records.tree') }}?container_only=${containerOnly}`, {
                headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
            })
            .then(r => r.json())
            .then(data => {
                $('#tree').jstree('destroy');
                $('#tree').jstree({
                    core: {
                        data: data,
                        themes: { name: 'default' }
                    }
                });
            });
        }

        document.addEventListener('DOMContentLoaded', function () {
            loadTree();
            document.getElementById('containerOnly').addEventListener('change', loadTree);
        });
    </script>
@endpush
