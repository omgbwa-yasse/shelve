{{-- activities/pdf-item.blade.php --}}
<div class="activity-item">
    <div class="activity-content {{ $item['level'] === 0 ? 'mission' : 'indented' }}"
         style="margin-left: {{ $item['level'] * 20 }}px">
        <span class="code">{{ $item['activity']->code }}</span>
        <span class="name">{{ $item['activity']->name }}</span>
        @if($item['activity']->observation)
            <div class="observation">{{ $item['activity']->observation }}</div>
        @endif
    </div>
    @if($item['children']->count() > 0)
        @foreach($item['children'] as $child)
            @include('activities.pdf-item', ['item' => $child])
        @endforeach
    @endif
</div>
