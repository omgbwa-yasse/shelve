@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row mb-3">
        <div class="col-md-12">
            <div class="d-flex justify-content-between align-items-center">
                <h1 class="h3">
                    <i class="bi bi-diagram-3"></i> Arborescence des Dossiers
                </h1>
                <div>
                    <a href="{{ route('folders.index') }}" class="btn btn-secondary">
                        <i class="bi bi-list"></i> Vue Liste
                    </a>
                    <a href="{{ route('folders.create') }}" class="btn btn-success">
                        <i class="bi bi-plus-circle"></i> Nouveau dossier
                    </a>
                </div>
            </div>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <!-- Filtres -->
    <div class="card mb-3">
        <div class="card-body">
            <div class="row g-3">
                <div class="col-md-4">
                    <label for="filter-organisation" class="form-label">Organisation</label>
                    <select id="filter-organisation" class="form-select">
                        <option value="">-- Toutes les organisations --</option>
                        @foreach($organisations ?? [] as $org)
                            <option value="{{ $org->id }}">{{ $org->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4">
                    <label for="filter-type" class="form-label">Type</label>
                    <select id="filter-type" class="form-select">
                        <option value="">-- Tous les types --</option>
                        @foreach($types ?? [] as $type)
                            <option value="{{ $type->id }}">{{ $type->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4">
                    <label for="search-folder" class="form-label">Rechercher</label>
                    <input type="text" id="search-folder" class="form-control" placeholder="Nom ou code du dossier...">
                </div>
            </div>
            <div class="mt-3">
                <button type="button" id="btn-expand-all" class="btn btn-sm btn-outline-primary">
                    <i class="bi bi-arrows-expand"></i> Tout déplier
                </button>
                <button type="button" id="btn-collapse-all" class="btn btn-sm btn-outline-primary">
                    <i class="bi bi-arrows-collapse"></i> Tout replier
                </button>
                <button type="button" id="btn-refresh" class="btn btn-sm btn-outline-secondary">
                    <i class="bi bi-arrow-clockwise"></i> Actualiser
                </button>
            </div>
        </div>
    </div>

    <!-- Légende -->
    <div class="card mb-3">
        <div class="card-body">
            <div class="row">
                <div class="col-md-12">
                    <strong>Légende :</strong>
                    <span class="ms-3"><i class="bi bi-folder-fill text-primary"></i> Dossier</span>
                    <span class="ms-3"><i class="bi bi-file-earmark-text text-info"></i> Documents</span>
                    <span class="ms-3"><i class="bi bi-folder2-open text-warning"></i> Sous-dossiers</span>
                    <span class="ms-3"><span class="badge bg-success">Actif</span></span>
                    <span class="ms-2"><span class="badge bg-warning">Archivé</span></span>
                    <span class="ms-2"><span class="badge bg-secondary">Fermé</span></span>
                </div>
            </div>
        </div>
    </div>

    <!-- Arborescence -->
    <div class="card">
        <div class="card-body">
            <div id="loading-spinner" class="text-center py-5">
                <div class="spinner-border text-primary" role="status">
                    <span class="visually-hidden">Chargement...</span>
                </div>
                <p class="mt-2">Chargement de l'arborescence...</p>
            </div>

            <div id="folder-tree" class="d-none">
                <!-- L'arborescence sera générée ici par JavaScript -->
            </div>

            <div id="no-folders" class="alert alert-info d-none">
                <i class="bi bi-info-circle"></i> Aucun dossier trouvé.
            </div>
        </div>
    </div>
</div>

<style>
    .tree-node {
        margin-left: 0;
        padding-left: 0;
        list-style: none;
    }

    .tree-node > li {
        margin: 5px 0;
        padding: 8px 12px;
        border-left: 2px solid #e0e0e0;
        transition: all 0.2s;
    }

    .tree-node > li:hover {
        background-color: #f8f9fa;
        border-left-color: #0d6efd;
    }

    .tree-node-item {
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .tree-toggle {
        cursor: pointer;
        width: 20px;
        height: 20px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border-radius: 3px;
        transition: background-color 0.2s;
    }

    .tree-toggle:hover {
        background-color: #e9ecef;
    }

    .tree-toggle.empty {
        visibility: hidden;
    }

    .tree-children {
        margin-left: 25px;
        margin-top: 5px;
        display: none;
    }

    .tree-children.expanded {
        display: block;
    }

    .folder-info {
        flex: 1;
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .folder-name {
        font-weight: 500;
        color: #212529;
        text-decoration: none;
    }

    .folder-name:hover {
        color: #0d6efd;
        text-decoration: underline;
    }

    .folder-code {
        font-family: monospace;
        font-size: 0.85em;
        color: #6c757d;
    }

    .folder-stats {
        display: flex;
        gap: 8px;
        align-items: center;
        font-size: 0.875rem;
    }

    .folder-actions {
        display: flex;
        gap: 5px;
    }

    .highlight {
        background-color: #fff3cd !important;
        border-left-color: #ffc107 !important;
    }
</style>

@push('scripts')
<script>
let treeData = [];
let filteredData = [];

document.addEventListener('DOMContentLoaded', function() {
    loadTreeData();

    // Event listeners pour les filtres
    document.getElementById('filter-organisation').addEventListener('change', filterTree);
    document.getElementById('filter-type').addEventListener('change', filterTree);
    document.getElementById('search-folder').addEventListener('input', filterTree);

    // Event listeners pour les boutons
    document.getElementById('btn-expand-all').addEventListener('click', expandAll);
    document.getElementById('btn-collapse-all').addEventListener('click', collapseAll);
    document.getElementById('btn-refresh').addEventListener('click', loadTreeData);
});

function loadTreeData() {
    const loadingSpinner = document.getElementById('loading-spinner');
    const folderTree = document.getElementById('folder-tree');
    const noFolders = document.getElementById('no-folders');

    loadingSpinner.classList.remove('d-none');
    folderTree.classList.add('d-none');
    noFolders.classList.add('d-none');

    const organisationId = document.getElementById('filter-organisation').value;
    const typeId = document.getElementById('filter-type').value;

    const url = new URL('{{ route('folders.tree') }}', window.location.origin);
    if (organisationId) url.searchParams.append('organisation_id', organisationId);
    if (typeId) url.searchParams.append('type_id', typeId);

    fetch(url)
        .then(response => response.json())
        .then(data => {
            loadingSpinner.classList.add('d-none');

            if (data.success && data.tree && data.tree.length > 0) {
                treeData = data.tree;
                filteredData = treeData;
                renderTree(filteredData);
                folderTree.classList.remove('d-none');
            } else {
                noFolders.classList.remove('d-none');
            }
        })
        .catch(error => {
            console.error('Erreur lors du chargement:', error);
            loadingSpinner.classList.add('d-none');
            alert('Erreur lors du chargement de l\'arborescence');
        });
}

function renderTree(data, container = null) {
    if (!container) {
        container = document.getElementById('folder-tree');
        container.innerHTML = '';
    }

    const ul = document.createElement('ul');
    ul.className = 'tree-node';

    data.forEach(folder => {
        const li = createTreeNode(folder);
        ul.appendChild(li);
    });

    container.appendChild(ul);
}

function createTreeNode(folder) {
    const li = document.createElement('li');
    li.dataset.folderId = folder.id;

    const nodeItem = document.createElement('div');
    nodeItem.className = 'tree-node-item';

    // Toggle pour déplier/replier
    const toggle = document.createElement('span');
    toggle.className = 'tree-toggle';
    if (folder.children && folder.children.length > 0) {
        toggle.innerHTML = '<i class="bi bi-chevron-right"></i>';
        toggle.addEventListener('click', function() {
            toggleNode(li);
        });
    } else {
        toggle.className += ' empty';
    }

    // Informations du dossier
    const folderInfo = document.createElement('div');
    folderInfo.className = 'folder-info';

    const icon = document.createElement('i');
    icon.className = 'bi bi-folder-fill text-primary';

    const nameLink = document.createElement('a');
    nameLink.href = `/repositories/folders/${folder.id}`;
    nameLink.className = 'folder-name';
    nameLink.textContent = folder.name;

    const code = document.createElement('span');
    code.className = 'folder-code';
    code.textContent = `(${folder.code})`;

    // Statistiques
    const stats = document.createElement('div');
    stats.className = 'folder-stats';

    const docsBadge = document.createElement('span');
    docsBadge.className = 'badge bg-info';
    docsBadge.innerHTML = `<i class="bi bi-file-earmark-text"></i> ${folder.documents_count}`;

    const subfoldersBadge = document.createElement('span');
    subfoldersBadge.className = 'badge bg-primary';
    subfoldersBadge.innerHTML = `<i class="bi bi-folder2-open"></i> ${folder.subfolders_count || 0}`;

    const statusBadge = document.createElement('span');
    statusBadge.className = `badge bg-${folder.status === 'active' ? 'success' : folder.status === 'archived' ? 'warning' : 'secondary'}`;
    statusBadge.textContent = folder.status === 'active' ? 'Actif' : folder.status === 'archived' ? 'Archivé' : 'Fermé';

    const sizeBadge = document.createElement('small');
    sizeBadge.className = 'text-muted';
    sizeBadge.textContent = folder.total_size_human;

    stats.appendChild(docsBadge);
    stats.appendChild(subfoldersBadge);
    stats.appendChild(statusBadge);
    stats.appendChild(sizeBadge);

    // Actions
    const actions = document.createElement('div');
    actions.className = 'folder-actions';

    const viewBtn = document.createElement('a');
    viewBtn.href = `/repositories/folders/${folder.id}`;
    viewBtn.className = 'btn btn-sm btn-outline-primary';
    viewBtn.innerHTML = '<i class="bi bi-eye"></i>';
    viewBtn.title = 'Voir';

    const editBtn = document.createElement('a');
    editBtn.href = `/repositories/folders/${folder.id}/edit`;
    editBtn.className = 'btn btn-sm btn-outline-warning';
    editBtn.innerHTML = '<i class="bi bi-pencil"></i>';
    editBtn.title = 'Modifier';

    actions.appendChild(viewBtn);
    actions.appendChild(editBtn);

    // Assembler les éléments
    folderInfo.appendChild(icon);
    folderInfo.appendChild(nameLink);
    folderInfo.appendChild(code);
    folderInfo.appendChild(stats);

    nodeItem.appendChild(toggle);
    nodeItem.appendChild(folderInfo);
    nodeItem.appendChild(actions);

    li.appendChild(nodeItem);

    // Ajouter les enfants
    if (folder.children && folder.children.length > 0) {
        const childrenContainer = document.createElement('div');
        childrenContainer.className = 'tree-children';
        renderTree(folder.children, childrenContainer);
        li.appendChild(childrenContainer);
    }

    return li;
}

function toggleNode(node) {
    const toggle = node.querySelector('.tree-toggle');
    const children = node.querySelector('.tree-children');

    if (children) {
        const isExpanded = children.classList.contains('expanded');

        if (isExpanded) {
            children.classList.remove('expanded');
            toggle.innerHTML = '<i class="bi bi-chevron-right"></i>';
        } else {
            children.classList.add('expanded');
            toggle.innerHTML = '<i class="bi bi-chevron-down"></i>';
        }
    }
}

function expandAll() {
    document.querySelectorAll('.tree-children').forEach(children => {
        children.classList.add('expanded');
        const node = children.closest('li');
        const toggle = node.querySelector('.tree-toggle');
        if (toggle && !toggle.classList.contains('empty')) {
            toggle.innerHTML = '<i class="bi bi-chevron-down"></i>';
        }
    });
}

function collapseAll() {
    document.querySelectorAll('.tree-children').forEach(children => {
        children.classList.remove('expanded');
        const node = children.closest('li');
        const toggle = node.querySelector('.tree-toggle');
        if (toggle && !toggle.classList.contains('empty')) {
            toggle.innerHTML = '<i class="bi bi-chevron-right"></i>';
        }
    });
}

function filterTree() {
    const searchTerm = document.getElementById('search-folder').value.toLowerCase();

    if (searchTerm === '') {
        // Réinitialiser les highlights
        document.querySelectorAll('.tree-node li').forEach(li => {
            li.classList.remove('highlight');
            li.style.display = '';
        });
        return;
    }

    // Rechercher et mettre en évidence
    document.querySelectorAll('.tree-node li').forEach(li => {
        const name = li.querySelector('.folder-name')?.textContent.toLowerCase() || '';
        const code = li.querySelector('.folder-code')?.textContent.toLowerCase() || '';

        if (name.includes(searchTerm) || code.includes(searchTerm)) {
            li.classList.add('highlight');
            // Déplier les parents
            let parent = li.parentElement.closest('li');
            while (parent) {
                const children = parent.querySelector('.tree-children');
                if (children) {
                    children.classList.add('expanded');
                    const toggle = parent.querySelector('.tree-toggle');
                    if (toggle && !toggle.classList.contains('empty')) {
                        toggle.innerHTML = '<i class="bi bi-chevron-down"></i>';
                    }
                }
                parent = parent.parentElement.closest('li');
            }
        } else {
            li.classList.remove('highlight');
        }
    });
}
</script>
@endpush
@endsection
