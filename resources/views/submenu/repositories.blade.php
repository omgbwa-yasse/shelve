<style>
    .sidebar {
        width: 250px;
        height: 100vh;
        position: fixed;
        top: 0;
        left: 0;
        overflow-y: auto;
        transition: all 0.3s;
    }

    .sidebar-header {
        background-color: #f8f9fa;
    }

    .sidebar-link {
        padding: 10px 15px;
        display: flex;
        align-items: center;
        color: #333;
        text-decoration: none;
        transition: all 0.3s;
    }

    .sidebar-link:hover {
        background-color: #f8f9fa;
        color: #007bff;
    }

    .sidebar-link i {
        font-size: 1.1rem;
    }

    .sub-link {
        padding-left: 40px;
    }

    .sidebar-nav {
        padding-top: 15px;
    }

    .sidebar-item {
        border-bottom: 1px solid #f1f1f1;
    }

    .sidebar-item:last-child {
        border-bottom: none;
    }
</style>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Toggle submenu
        const sidebarLinks = document.querySelectorAll('.sidebar-link[data-bs-toggle="collapse"]');
        sidebarLinks.forEach(link => {
            link.addEventListener('click', function(e) {
                e.preventDefault();
                const submenuId = this.getAttribute('data-bs-target');
                const submenu = document.querySelector(submenuId);
                submenu.classList.toggle('show');
                this.querySelector('.bi-chevron-down').classList.toggle('rotate-icon');
            });
        });

        // Highlight active link
        const currentLocation = window.location.href;
        const menuItems = document.querySelectorAll('.sidebar-link');
        menuItems.forEach(item => {
            if (item.href === currentLocation) {
                item.classList.add('active');
                if (item.closest('.collapse')) {
                    item.closest('.collapse').classList.add('show');
                    item.closest('.sidebar-item').querySelector('.sidebar-link').classList.add('active');
                }
            }
        });

        // Toggle sidebar on mobile
        const sidebarToggle = document.getElementById('sidebarToggle');
        const sidebar = document.getElementById('sidebar');
        sidebarToggle.addEventListener('click', function() {
            sidebar.classList.toggle('active');
        });
    });
</script>
<aside class="sidebar bg-white shadow-sm" id="sidebar">
    <div class="sidebar-header p-3 border-bottom">
        <h5 class="mb-0">Archive Management</h5>
    </div>
    <nav class="sidebar-nav">
        <ul class="list-unstyled">
            <li class="sidebar-item">
                <a href="#" class="sidebar-link" data-bs-toggle="collapse" data-bs-target="#searchSubmenu">
                    <i class="bi bi-search me-2"></i>
                    <span>Recherche</span>
                    <i class="bi bi-chevron-down ms-auto"></i>
                </a>
                <ul class="collapse list-unstyled" id="searchSubmenu">
                    <li><a class="sidebar-link sub-link" href="{{ route('records.index') }}"><i class="bi bi-list-check me-2"></i>Mes archives</a></li>
                    <li><a class="sidebar-link sub-link" href="{{ route('record-author.index') }}"><i class="bi bi-person me-2"></i>Detenteurs</a></li>
                    <li><a class="sidebar-link sub-link" href="{{ route('record-select-date')}}"><i class="bi bi-calendar me-2"></i>Dates</a></li>
                    <li><a class="sidebar-link sub-link" href="{{ route('record-select-word')}}"><i class="bi bi-key me-2"></i>Mots-clés</a></li>
                    <li><a class="sidebar-link sub-link" href="{{ route('record-select-activity')}}"><i class="bi bi-briefcase me-2"></i>Activités</a></li>
                    <li><a class="sidebar-link sub-link" href="{{ route('record-select-building')}}"><i class="bi bi-building me-2"></i>Locaux</a></li>
                    <li><a class="sidebar-link sub-link" href=""><i class="bi bi-archive me-2"></i>Fonds d'archives</a></li>
                    <li><a class="sidebar-link sub-link" href="{{ route('record-select-last')}}"><i class="bi bi-clock-history me-2"></i>Récents</a></li>
                </ul>
            </li>
            <li class="sidebar-item">
                <a href="#" class="sidebar-link" data-bs-toggle="collapse" data-bs-target="#registerSubmenu">
                    <i class="bi bi-journal-plus me-2"></i>
                    <span>Enregistrement</span>
                    <i class="bi bi-chevron-down ms-auto"></i>
                </a>
                <ul class="collapse list-unstyled" id="registerSubmenu">
                    <li><a class="sidebar-link sub-link" href="{{ route('records.create') }}"><i class="bi bi-plus-square me-2"></i>Nouveau</a></li>
                    <li><a class="sidebar-link sub-link" href="{{ route('record-author.create') }}"><i class="bi bi-plus-square me-2"></i>Producteur</a></li>
                </ul>
            </li>
            <li class="sidebar-item">
                <a href="#" class="sidebar-link" data-bs-toggle="collapse" data-bs-target="#lifecycleSubmenu">
                    <i class="bi bi-cart me-2"></i>
                    <span>Cycle de vie</span>
                    <i class="bi bi-chevron-down ms-auto"></i>
                </a>
                <ul class="collapse list-unstyled" id="lifecycleSubmenu">
                    <li><a class="sidebar-link sub-link" href="{{ route('records.tostore')}}"><i class="bi bi-folder-check me-2"></i>A transférer</a></li>
                    <li><a class="sidebar-link sub-link" href="{{ route('records.toretain')}}"><i class="bi bi-folder-check me-2"></i>Dossiers actifs</a></li>
                    <li><a class="sidebar-link sub-link" href="{{ route('records.totransfer')}}"><i class="bi bi-arrow-right-square me-2"></i>A verser</a></li>
                    <li><a class="sidebar-link sub-link" href="{{ route('records.toeliminate')}}"><i class="bi bi-trash me-2"></i>A éliminer</a></li>
                    <li><a class="sidebar-link sub-link" href="{{ route('records.tokeep')}}"><i class="bi bi-archive me-2"></i>A conserver</a></li>
                    <li><a class="sidebar-link sub-link" href="{{ route('records.tosort')}}"><i class="bi bi-sort-down me-2"></i>A trier</a></li>
                </ul>
            </li>
            <li class="sidebar-item">
                <a href="#" class="sidebar-link" data-bs-toggle="collapse" data-bs-target="#importExportSubmenu">
                    <i class="bi bi-arrow-left-right me-2"></i>
                    <span>Import / Export</span>
                    <i class="bi bi-chevron-down ms-auto"></i>
                </a>
                <ul class="collapse list-unstyled" id="importExportSubmenu">
                    <li><a class="sidebar-link sub-link" href="{{ route('records.import.form') }}"><i class="bi bi-upload me-2"></i>Import</a></li>
                    <li><a class="sidebar-link sub-link" href="{{ route('records.export.form') }}"><i class="bi bi-download me-2"></i>Export</a></li>
                </ul>
            </li>
        </ul>
    </nav>
</aside>

