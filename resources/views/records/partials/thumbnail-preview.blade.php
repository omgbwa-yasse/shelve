@php
    use Illuminate\Support\Facades\Storage;

    // Récupérer les vignettes selon le type de record
    $thumbnails = collect();

    if ($recordType === 'folder') {
        // Pour les dossiers numériques, récupérer les vignettes des documents
        // Utiliser la collection eager-loadée du controller
        if ($record->relationLoaded('documents') && $record->documents->isNotEmpty()) {
            $thumbnails = $record->documents
                ->take(3)
                ->filter(fn($doc) => $doc->attachment !== null)
                ->map(fn($doc) => $doc->attachment);
        }
    } elseif ($recordType === 'document') {
        // Pour les documents, récupérer la vignette de l'attachment principal
        if ($record->attachment) {
            $thumbnails = collect([$record->attachment]);
        }
    } elseif ($recordType === 'physical') {
        // Pour les dossiers physiques avec documents numériques
        if (method_exists($record, 'digitalDocuments') && $record->relationLoaded('digitalDocuments') && $record->digitalDocuments->isNotEmpty()) {
            $thumbnails = $record->digitalDocuments
                ->take(3)
                ->filter(fn($doc) => $doc->attachment !== null)
                ->map(fn($doc) => $doc->attachment);
        }
    }
@endphp

@if($thumbnails->isNotEmpty())
    @if($recordType === 'folder' || $recordType === 'physical')
        {{-- Afficher 3 vignettes superposées et décalées pour les dossiers --}}
        <div class="position-relative" style="width: 80px; height: 100px;">
            @foreach($thumbnails as $idx => $thumbnail)
                @php
                    $offset = $idx * 8; // Décalage en pixels
                    $zIndex = 10 - $idx; // Z-index décroissant
                @endphp
                <div class="position-absolute rounded border border-light shadow-sm"
                     style="
                        width: 70px;
                        height: 90px;
                        left: {{ $offset }}px;
                        top: {{ $offset }}px;
                        z-index: {{ $zIndex }};
                        overflow: hidden;
                        background: #f8f9fa;
                     ">
                    @php
                        $thumbUrl = null;
                        if ($thumbnail->thumbnail_path && Storage::disk('local')->exists($thumbnail->thumbnail_path)) {
                            $thumbUrl = asset('storage/' . $thumbnail->thumbnail_path);
                        } else {
                            $thumbUrl = $thumbnail->getDefaultThumbnailUrl();
                        }
                    @endphp
                    <img src="{{ $thumbUrl }}"
                         alt="{{ $thumbnail->name }}"
                         class="w-100 h-100 object-fit-cover"
                         title="{{ $thumbnail->name }}">
                </div>
            @endforeach
        </div>
    @else
        {{-- Afficher une seule vignette pour les documents --}}
        <div class="rounded border border-light shadow-sm" style="width: 70px; height: 90px; overflow: hidden;">
            @php
                $thumbnail = $thumbnails->first();
                $thumbUrl = null;
                if ($thumbnail && $thumbnail->thumbnail_path && Storage::disk('local')->exists($thumbnail->thumbnail_path)) {
                    $thumbUrl = asset('storage/' . $thumbnail->thumbnail_path);
                } elseif ($thumbnail) {
                    $thumbUrl = $thumbnail->getDefaultThumbnailUrl();
                }
            @endphp
            @if($thumbUrl)
                <img src="{{ $thumbUrl }}"
                     alt="{{ $thumbnail->name ?? 'Thumbnail' }}"
                     class="w-100 h-100 object-fit-cover"
                     title="{{ $thumbnail->name ?? 'Document' }}">
            @else
                <div class="d-flex align-items-center justify-content-center h-100 bg-light text-muted">
                    <i class="bi bi-file-earmark" style="font-size: 1.2rem;"></i>
                </div>
            @endif
        </div>
    @endif
@endif
