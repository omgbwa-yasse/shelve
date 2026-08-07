<div class="barcode-grid">
    @foreach ($barcodes->take($perPage) as $barcode)
        <div class="barcode-item">
            <div class="barcode-image">
                {!! $barcodeGenerator->getBarcodeHTML($barcode, $barcodeType, $barcodeWidth, $barcodeHeight) !!}
            </div>
            @if ($showText)
                <div class="barcode-text">{{ $barcode }}</div>
            @endif
        </div>
    @endforeach
</div>

<style>
    .barcode-grid {
        display: grid;
        grid-template-columns: repeat({{ $columns }}, 1fr);
        gap: 15px;
        padding: 15px;
    }
    .barcode-item {
        display: flex;
        flex-direction: column;
        align-items: center;
        padding: 10px;
        border: 1px solid #e0e0e0;
        border-radius: 4px;
        transition: box-shadow 0.3s ease;
    }
    .barcode-item:hover {
        box-shadow: 0 2px 5px rgba(0,0,0,0.1);
    }
    .barcode-image {
        margin-bottom: 5px;
    }
    .barcode-image svg {
        max-width: 100%;
        height: auto;
    }
    .barcode-text {
        font-size: 12px;
        color: #333;
        text-align: center;
        word-break: break-all;
    }
</style>
