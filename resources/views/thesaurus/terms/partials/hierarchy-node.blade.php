{{-- Hierarchy Node Partial --}}
<div class="hierarchy-node hierarchy-level-{{ $level }}">
    @if($level > 0)
        @for($i = 0; $i < $level; $i++)
            @if($i == $level - 1)
                ├─
            @else
                │&nbsp;&nbsp;
            @endif
        @endfor
    @endif

    <a href="{{ route('thesaurus.concepts.show', $node['concept']) }}" class="concept-link">
        <strong>{{ $node['concept']->pref_label ?? 'Untitled Concept' }}</strong>
    </a>

    @if($node['concept']->notation)
        <small class="text-muted">({{ $node['concept']->notation }})</small>
    @endif

    @if($node['concept']->definition)
        <br>
        @for($i = 0; $i < $level; $i++)│&nbsp;&nbsp;@endfor
        <small class="text-muted">{{ Str::limit($node['concept']->definition, 80) }}</small>
    @endif
</div>

@if(isset($node['children']) && count($node['children']) > 0)
    @foreach($node['children'] as $childNode)
        @include('thesaurus.terms.partials.hierarchy-node', ['node' => $childNode, 'level' => $level + 1])
    @endforeach
@endif
