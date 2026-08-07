<!-- resources/views/activities/partials/org-tree-item.blade.php -->

<div class="org-tree-item">
    <div class="org-node">
        <h5><strong>{{ $activity->code }}{{ $activity->name }}
                @if($activity->parent)
                    <span class="badge bg-secondary">{{ $activity->parent->code }}|(Activité)</span>

                @else
                    <span class="badge bg-primary">Mission</span>
                @endif</strong></h5>
        <p>{{ $activity->observation ?? 'N/A' }}</p>

        <a href="{{ route('activities.show', $activity->id) }}" class="btn btn-sm btn-outline-primary">
            <i class="bi bi-gear me-1"></i>Paramètres
        </a>
        @if($activity->children->isNotEmpty())
            <button class="btn btn-sm btn-outline-secondary toggle-children" data-target="children-{{ $activity->id }}">
                <i class="bi bi-chevron-down"></i>
            </button>
        @endif
    </div>
    @if($activity->children->isNotEmpty())
        <div class="org-children" id="children-{{ $activity->id }}">
            @foreach($activity->children as $child)
                @include('activities.partials.org-tree-item', ['activity' => $child])
            @endforeach
        </div>
    @endif
</div>
