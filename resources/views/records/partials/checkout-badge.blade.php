@if($medium->isCheckedOut())
    <span class="badge bg-info">
        <i class="bi bi-lock"></i> Check-out
        @if($medium->checkedOutUser) par {{ $medium->checkedOutUser->name }} @endif
        @if($medium->checked_out_at) le {{ $medium->checked_out_at->format('d/m/Y H:i') }} @endif
    </span>
@else
    <span class="badge bg-light text-dark"><i class="bi bi-unlock"></i> Disponible</span>
@endif
