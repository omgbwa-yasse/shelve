@extends('layouts.app')

@section('content')
<div class="container-fluid px-3 py-2">

    @include('workplaces.partials.site-header', ['activeTab' => 'members'])

    {{-- Toolbar --}}
    <div class="d-flex align-items-center gap-2 mb-3 flex-wrap">
        <span class="text-muted small me-auto">
            <i class="bi bi-people me-1"></i>{{ $members->count() }} membre(s)
        </span>
        <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#inviteModal">
            <i class="bi bi-person-plus me-1"></i>Inviter un membre
        </button>
    </div>

    {{-- ===== MEMBERS GRID ===== --}}
    <div class="row g-3 mb-4">
        @foreach($members as $member)
        @php
            $roleColors = [
                'owner'       => ['bg' => '#fce8e6', 'color' => '#c62828', 'label' => 'Propriétaire'],
                'admin'       => ['bg' => '#e8f0fe', 'color' => '#1a73e8', 'label' => 'Admin'],
                'editor'      => ['bg' => '#e6f4ea', 'color' => '#188038', 'label' => 'Éditeur'],
                'contributor' => ['bg' => '#fef9e0', 'color' => '#f9a825', 'label' => 'Contributeur'],
                'viewer'      => ['bg' => '#f1f3f4', 'color' => '#5f6368', 'label' => 'Lecteur'],
                'member'      => ['bg' => '#e8f0fe', 'color' => '#1a73e8', 'label' => 'Membre'],
            ];
            $rc = $roleColors[$member->role] ?? ['bg' => '#f1f3f4', 'color' => '#5f6368', 'label' => ucfirst($member->role)];
            $avatarColors = ['#3b82f6','#10b981','#f59e0b','#ef4444','#8b5cf6','#ec4899','#06b6d4','#84cc16'];
            $avatarBg = $avatarColors[$loop->index % count($avatarColors)];
            $initials = strtoupper(mb_substr($member->user->name ?? '?', 0, 2));
        @endphp
        <div class="col-12 col-sm-6 col-md-4 col-lg-3">
            <div class="card border-0 shadow-sm h-100 gd-member-card">
                <div class="card-body p-3">
                    <div class="d-flex align-items-start gap-3">
                        {{-- Avatar --}}
                        @if($member->user && ($member->user->profile_photo_path ?? null))
                        <img src="{{ $member->user->profile_photo_url }}" alt=""
                             class="rounded-circle flex-shrink-0"
                             style="width:42px;height:42px;object-fit:cover;">
                        @else
                        <div class="rounded-circle d-flex align-items-center justify-content-center flex-shrink-0 fw-bold text-white"
                             style="width:42px;height:42px;background:{{ $avatarBg }};font-size:.9rem;">
                            {{ $initials }}
                        </div>
                        @endif

                        <div class="flex-grow-1 min-w-0">
                            <div class="fw-semibold text-truncate" style="font-size:.85rem;">{{ $member->user->name ?? '—' }}</div>
                            <div class="text-muted text-truncate" style="font-size:.72rem;">{{ $member->user->email ?? '' }}</div>
                            <div class="d-flex align-items-center gap-1 mt-1 flex-wrap">
                                <span class="badge rounded-pill px-2"
                                      style="font-size:.62rem;background:{{ $rc['bg'] }};color:{{ $rc['color'] }};">
                                    {{ $rc['label'] }}
                                </span>
                                @if($member->can_create_folders)
                                <span class="badge bg-light text-muted border" style="font-size:.58rem;">Dossiers</span>
                                @endif
                                @if($member->can_create_documents)
                                <span class="badge bg-light text-muted border" style="font-size:.58rem;">Documents</span>
                                @endif
                                @if($member->can_delete)
                                <span class="badge bg-light text-muted border" style="font-size:.58rem;">Suppr.</span>
                                @endif
                                @if($member->can_share)
                                <span class="badge bg-light text-muted border" style="font-size:.58rem;">Partage</span>
                                @endif
                            </div>
                            <div class="text-muted mt-1" style="font-size:.68rem;">
                                Membre depuis {{ $member->joined_at->format('d/m/Y') }}
                            </div>
                        </div>
                    </div>
                </div>
                @if($member->role !== 'owner')
                <div class="card-footer border-0 bg-transparent px-3 pb-2 pt-0 d-flex gap-1 justify-content-end">
                    <button type="button" class="btn btn-sm btn-outline-secondary"
                            data-bs-toggle="modal" data-bs-target="#editMemberModal{{ $member->id }}"
                            title="Modifier le rôle">
                        <i class="bi bi-pencil"></i>
                    </button>
                    <form method="POST" action="{{ route('workplaces.members.destroy', [$workplace, $member]) }}"
                          onsubmit="return confirm('Retirer {{ $member->user->name ?? 'ce membre' }} ?')">
                        @csrf @method('DELETE')
                        <button type="submit" class="btn btn-sm btn-outline-danger" title="Retirer">
                            <i class="bi bi-person-dash"></i>
                        </button>
                    </form>
                </div>
                @endif
            </div>
        </div>

        {{-- Edit member modal --}}
        @if($member->role !== 'owner')
        <div class="modal fade" id="editMemberModal{{ $member->id }}" tabindex="-1">
            <div class="modal-dialog modal-sm">
                <div class="modal-content">
                    <div class="modal-header py-2">
                        <h6 class="modal-title">Modifier — {{ $member->user->name ?? '' }}</h6>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <form method="POST" action="{{ route('workplaces.members.update', [$workplace, $member]) }}">
                        @csrf @method('PUT')
                        <div class="modal-body">
                            <label class="form-label small fw-semibold">Rôle</label>
                            <select class="form-select form-select-sm" name="role" required>
                                <option value="viewer"       {{ $member->role === 'viewer'       ? 'selected' : '' }}>Lecteur</option>
                                <option value="contributor"  {{ $member->role === 'contributor'  ? 'selected' : '' }}>Contributeur</option>
                                <option value="editor"       {{ $member->role === 'editor'       ? 'selected' : '' }}>Éditeur</option>
                                <option value="admin"        {{ $member->role === 'admin'        ? 'selected' : '' }}>Admin</option>
                            </select>
                            <div class="mt-3">
                                <label class="form-label small fw-semibold">Permissions</label>
                                <div class="d-flex flex-column gap-1">
                                    @foreach([
                                        ['can_create_folders',   'Créer des dossiers'],
                                        ['can_create_documents', 'Créer des documents'],
                                        ['can_delete',           'Supprimer'],
                                        ['can_share',            'Partager'],
                                        ['can_invite',           'Inviter des membres'],
                                    ] as [$perm, $label])
                                    <div class="form-check form-switch mb-0">
                                        <input class="form-check-input" type="checkbox" id="{{ $perm }}_{{ $member->id }}"
                                               name="{{ $perm }}" value="1" {{ $member->$perm ? 'checked' : '' }}>
                                        <label class="form-check-label small" for="{{ $perm }}_{{ $member->id }}">{{ $label }}</label>
                                    </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer py-2">
                            <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Annuler</button>
                            <button type="submit" class="btn btn-primary btn-sm">Enregistrer</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        @endif
        @endforeach
    </div>

    {{-- ===== INVITATIONS EN ATTENTE ===== --}}
    @if($invitations->count() > 0)
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header py-2" style="background:#fafbfc;border-bottom:1px solid #eef0f3;">
            <span class="fw-semibold" style="font-size:.83rem;">
                <i class="bi bi-envelope-open me-2 text-warning"></i>
                Invitations en attente ({{ $invitations->count() }})
            </span>
        </div>
        <div class="card-body p-0">
            <table class="table table-sm mb-0" style="font-size:.8rem;">
                <thead style="background:#f8f9fa;font-size:.7rem;color:#5f6368;text-transform:uppercase;letter-spacing:.04em;">
                    <tr>
                        <th class="px-3 py-2 border-0">Email</th>
                        <th class="py-2 border-0">Rôle proposé</th>
                        <th class="py-2 border-0">Invité par</th>
                        <th class="py-2 border-0">Expire le</th>
                        <th class="py-2 border-0">Statut</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($invitations as $invitation)
                    <tr>
                        <td class="px-3 py-2"><i class="bi bi-envelope me-2 text-muted"></i>{{ $invitation->email }}</td>
                        <td class="py-2"><span class="badge bg-light text-dark border" style="font-size:.65rem;">{{ ucfirst($invitation->proposed_role) }}</span></td>
                        <td class="py-2 text-muted">{{ $invitation->inviter->name ?? '—' }}</td>
                        <td class="py-2 text-muted">{{ $invitation->expires_at->format('d/m/Y H:i') }}</td>
                        <td class="py-2">
                            @if($invitation->isExpired())
                            <span class="badge" style="background:#fce8e6;color:#c62828;font-size:.65rem;">Expirée</span>
                            @else
                            <span class="badge" style="background:#fff3e0;color:#e65100;font-size:.65rem;"><i class="bi bi-hourglass me-1"></i>En attente</span>
                            @endif
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    @endif

</div>

{{-- ===== MODAL: Ajouter un membre ===== --}}
<div class="modal fade" id="inviteModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header py-2">
                <h6 class="modal-title"><i class="bi bi-person-plus me-2 text-primary"></i>Ajouter un membre</h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="{{ route('workplaces.members.store', $workplace) }}" id="addMemberForm">
                @csrf
                <div class="modal-body">

                    {{-- Tabs: utilisateur existant vs invitation email --}}
                    <ul class="nav nav-pills nav-sm mb-3" id="memberAddTabs" role="tablist">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link active px-3 py-1" id="tab-user" data-bs-toggle="pill"
                                    data-bs-target="#pane-user" type="button" style="font-size:.8rem;">
                                <i class="bi bi-person-check me-1"></i>Utilisateur existant
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link px-3 py-1" id="tab-email" data-bs-toggle="pill"
                                    data-bs-target="#pane-email" type="button" style="font-size:.8rem;">
                                <i class="bi bi-envelope me-1"></i>Invitation par email
                            </button>
                        </li>
                    </ul>

                    <div class="tab-content">
                        {{-- Pane: utilisateur existant --}}
                        <div class="tab-pane fade show active" id="pane-user" role="tabpanel">
                            <div class="mb-3">
                                <label class="form-label small fw-semibold">Rechercher un utilisateur <span class="text-danger">*</span></label>
                                <div class="position-relative">
                                    <div class="input-group input-group-sm">
                                        <span class="input-group-text bg-white border-end-0">
                                            <i class="bi bi-search text-muted"></i>
                                        </span>
                                        <input type="text" class="form-control border-start-0 ps-0" id="userSearch"
                                               placeholder="Nom ou email..." autocomplete="off" style="box-shadow:none;">
                                        <div class="spinner-border spinner-border-sm text-muted d-none position-absolute"
                                             id="userSearchSpinner" style="right:10px;top:8px;z-index:10;" role="status"></div>
                                    </div>
                                    <input type="hidden" name="user_id" id="user_id_input">
                                </div>
                                <div id="userSearchResults" class="list-group mt-1 shadow"
                                     style="position:absolute;z-index:1055;width:calc(100% - 2rem);max-height:220px;overflow-y:auto;display:none;"></div>
                                <div id="userSelected" class="d-none mt-2 p-2 rounded-3 d-flex align-items-center gap-2"
                                     style="background:#e8f0fe;border:1px solid #c5d3f5;">
                                    <div id="userSelectedAvatar" class="rounded-circle d-flex align-items-center justify-content-center fw-bold text-white flex-shrink-0"
                                         style="width:32px;height:32px;font-size:.75rem;background:#4285f4;"></div>
                                    <div class="flex-grow-1 min-w-0">
                                        <div class="fw-semibold small" id="userSelectedName"></div>
                                        <div class="text-muted" style="font-size:.7rem;" id="userSelectedEmail"></div>
                                    </div>
                                    <button type="button" class="btn btn-sm btn-link p-0 text-muted" id="userClearBtn">
                                        <i class="bi bi-x-lg"></i>
                                    </button>
                                </div>
                            </div>
                        </div>

                        {{-- Pane: invitation email --}}
                        <div class="tab-pane fade" id="pane-email" role="tabpanel">
                            <div class="mb-3">
                                <label class="form-label small fw-semibold">Email <span class="text-danger">*</span></label>
                                <input type="email" class="form-control form-control-sm" name="email" id="inviteEmail"
                                       placeholder="exemple@organisation.com">
                                <div class="form-text" style="font-size:.72rem;">
                                    <i class="bi bi-info-circle me-1"></i>
                                    L'utilisateur recevra un email pour rejoindre cet espace.
                                </div>
                            </div>
                            <div class="mb-0">
                                <label class="form-label small fw-semibold">Message (optionnel)</label>
                                <textarea class="form-control form-control-sm" name="message" rows="2"
                                          placeholder="Message personnalisé..."></textarea>
                            </div>
                        </div>
                    </div>

                    {{-- Rôle (commun aux deux onglets) --}}
                    <div class="mt-3 pt-3 border-top">
                        <label class="form-label small fw-semibold">Rôle</label>
                        <div class="row g-2">
                            @foreach([
                                ['viewer',      'Lecteur',       'Lecture seule',              'bi-eye',         '#f1f3f4', '#5f6368'],
                                ['contributor', 'Contributeur',  'Peut ajouter du contenu',    'bi-pencil',      '#fef9e0', '#f9a825'],
                                ['editor',      'Éditeur',       'Peut modifier le contenu',   'bi-pencil-square','#e8f0fe','#1a73e8'],
                                ['admin',       'Admin',         'Tous les droits',            'bi-shield-check', '#fce8e6','#c62828'],
                            ] as [$val, $label, $desc, $icon, $bg, $color])
                            <div class="col-6">
                                <label class="gd-role-option d-block rounded-3 p-2 cursor-pointer" style="border:2px solid #e0e0e0;cursor:pointer;transition:border-color .12s;">
                                    <input type="radio" name="role" value="{{ $val }}" class="d-none gd-role-radio"
                                           {{ $val === 'editor' ? 'checked' : '' }}>
                                    <div class="d-flex align-items-center gap-2">
                                        <div style="width:28px;height:28px;border-radius:7px;background:{{ $bg }};color:{{ $color }};display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                                            <i class="bi {{ $icon }}" style="font-size:.8rem;"></i>
                                        </div>
                                        <div>
                                            <div class="fw-semibold" style="font-size:.78rem;color:#202124;">{{ $label }}</div>
                                            <div class="text-muted" style="font-size:.65rem;">{{ $desc }}</div>
                                        </div>
                                    </div>
                                </label>
                            </div>
                            @endforeach
                        </div>
                    </div>

                </div>
                <div class="modal-footer py-2">
                    <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Annuler</button>
                    <button type="submit" class="btn btn-primary btn-sm" id="addMemberSubmit">
                        <i class="bi bi-person-plus me-1"></i>Ajouter
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
.gd-member-card {
    border-radius: 10px !important;
    transition: box-shadow .15s, transform .12s;
}
.gd-member-card:hover {
    box-shadow: 0 4px 12px rgba(0,0,0,.1) !important;
    transform: translateY(-1px);
}
.gd-role-option:has(.gd-role-radio:checked) {
    border-color: #4285f4 !important;
    background: #f0f4ff;
}
.nav-pills .nav-link {
    font-size: .8rem;
    padding: .3rem .8rem;
    border-radius: 1rem;
    color: #5f6368;
}
.nav-pills .nav-link.active {
    background: #e8f0fe;
    color: #1a73e8;
}
</style>
@endpush

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const userSearch   = document.getElementById('userSearch');
    const userIdInput  = document.getElementById('user_id_input');
    const userResults  = document.getElementById('userSearchResults');
    const userSpinner  = document.getElementById('userSearchSpinner');
    const userSel      = document.getElementById('userSelected');
    const userClearBtn = document.getElementById('userClearBtn');
    const submitBtn    = document.getElementById('addMemberSubmit');
    const inviteEmail  = document.getElementById('inviteEmail');
    const tabUser      = document.getElementById('tab-user');
    const tabEmail     = document.getElementById('tab-email');
    let timeout = null;

    // Role option visual selection
    document.querySelectorAll('.gd-role-radio').forEach(radio => {
        radio.addEventListener('change', () => {
            document.querySelectorAll('.gd-role-option').forEach(opt => {
                opt.style.borderColor = '#e0e0e0';
                opt.style.background  = '';
            });
            if (radio.checked) {
                const label = radio.closest('.gd-role-option');
                label.style.borderColor = '#4285f4';
                label.style.background  = '#f0f4ff';
            }
        });
        // Init
        if (radio.checked) {
            const label = radio.closest('.gd-role-option');
            label.style.borderColor = '#4285f4';
            label.style.background  = '#f0f4ff';
        }
    });

    // Switch between tabs — toggle required
    tabUser?.addEventListener('shown.bs.tab', () => {
        if (inviteEmail) inviteEmail.removeAttribute('required');
    });
    tabEmail?.addEventListener('shown.bs.tab', () => {
        if (inviteEmail) inviteEmail.setAttribute('required', '');
        userIdInput.value = '';
        userSel.classList.add('d-none');
        userSearch.value = '';
    });

    // User search
    if (userSearch) {
        userSearch.addEventListener('input', function () {
            clearTimeout(timeout);
            const q = this.value.trim();
            if (q.length < 2) { userResults.style.display = 'none'; return; }
            userSpinner.classList.remove('d-none');
            timeout = setTimeout(() => {
                fetch('{{ route("workplaces.members.searchUsers", $workplace) }}?q=' + encodeURIComponent(q), {
                    headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' }
                })
                .then(r => r.json())
                .then(data => {
                    userSpinner.classList.add('d-none');
                    userResults.innerHTML = '';
                    if (!data.length) {
                        userResults.innerHTML = '<div class="list-group-item text-muted small py-2"><i class="bi bi-info-circle me-1"></i>Aucun utilisateur trouvé</div>';
                        userResults.style.display = 'block'; return;
                    }
                    const avatarColors = ['#3b82f6','#10b981','#f59e0b','#ef4444','#8b5cf6','#ec4899'];
                    data.forEach((user, i) => {
                        const initials = user.name.substring(0, 2).toUpperCase();
                        const bg = avatarColors[i % avatarColors.length];
                        const item = document.createElement('a');
                        item.href = '#';
                        item.className = 'list-group-item list-group-item-action py-2 px-3';
                        item.innerHTML = `<div class="d-flex align-items-center gap-2">
                            <div class="rounded-circle d-flex align-items-center justify-content-center fw-bold text-white flex-shrink-0"
                                 style="width:30px;height:30px;font-size:.7rem;background:${bg};">${initials}</div>
                            <div>
                                <div class="fw-semibold small">${user.name}</div>
                                <div class="text-muted" style="font-size:.7rem;">${user.email}</div>
                            </div>
                        </div>`;
                        item.addEventListener('click', e => {
                            e.preventDefault();
                            userIdInput.value = user.id;
                            document.getElementById('userSelectedName').textContent  = user.name;
                            document.getElementById('userSelectedEmail').textContent = user.email;
                            document.getElementById('userSelectedAvatar').textContent = initials;
                            document.getElementById('userSelectedAvatar').style.background = bg;
                            userSel.classList.remove('d-none');
                            userSearch.value = '';
                            userResults.style.display = 'none';
                        });
                        userResults.appendChild(item);
                    });
                    userResults.style.display = 'block';
                })
                .catch(() => { userSpinner.classList.add('d-none'); });
            }, 300);
        });

        userClearBtn?.addEventListener('click', () => {
            userIdInput.value = '';
            userSel.classList.add('d-none');
            userSearch.value = '';
        });

        document.addEventListener('click', e => {
            if (!userSearch.contains(e.target) && !userResults.contains(e.target)) {
                userResults.style.display = 'none';
            }
        });
    }

    // Reset on modal close
    document.getElementById('inviteModal')?.addEventListener('hidden.bs.modal', () => {
        userIdInput.value = '';
        userSearch.value  = '';
        userSel.classList.add('d-none');
        userResults.style.display = 'none';
        if (inviteEmail) inviteEmail.value = '';
        // Switch back to first tab
        const tabTrigger = document.getElementById('tab-user');
        if (tabTrigger) bootstrap.Tab.getOrCreateInstance(tabTrigger).show();
    });
});
</script>
@endpush
