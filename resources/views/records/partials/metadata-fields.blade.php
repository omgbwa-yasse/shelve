@php($record = $record ?? null)
@if(!empty($metadataFields))
    <hr>
    <h6>Métadonnées du type</h6>
    <div class="row g-3">
        @foreach($metadataFields as $field)
            <div class="col-md-6">
                <label class="form-label">
                    {{ $field['name'] }}
                    @if($field['mandatory']) <span class="text-danger">*</span> @endif
                </label>
                @php($fieldValue = old('metadata.' . $field['code'], $record?->getMetadataValue($field['code'])))
                @if(in_array($field['data_type'], ['select', 'multi_select', 'reference_list']))
                    <select name="metadata[{{ $field['code'] }}]"
                            class="form-select"
                            @if($field['data_type'] === 'multi_select') multiple @endif
                            @if($field['mandatory']) required @endif
                            @if($field['readonly']) disabled @endif>
                        <option value="">—</option>
                        @foreach($field['options'] ?? [] as $option)
                            @php($optionValue = is_array($option) ? ($option['code'] ?? $option['value'] ?? '') : $option)
                            @php($optionLabel = is_array($option) ? ($option['value'] ?? $option['code'] ?? '') : $option)
                            <option value="{{ $optionValue }}" @selected($fieldValue == $optionValue || (is_array($fieldValue) && in_array($optionValue, $fieldValue)))>{{ $optionLabel }}</option>
                        @endforeach
                    </select>
                @elseif($field['data_type'] === 'boolean')
                    <select name="metadata[{{ $field['code'] }}]" class="form-select">
                        <option value="0" @selected(!$fieldValue)>Non</option>
                        <option value="1" @selected($fieldValue)>Oui</option>
                    </select>
                @elseif($field['data_type'] === 'textarea')
                    <textarea name="metadata[{{ $field['code'] }}]" class="form-control" rows="2" @if($field['mandatory']) required @endif>{{ $fieldValue }}</textarea>
                @elseif($field['data_type'] === 'date')
                    <input type="date" name="metadata[{{ $field['code'] }}]" value="{{ is_string($fieldValue) ? substr($fieldValue, 0, 10) : '' }}" class="form-control">
                @else
                    <input type="{{ $field['data_type'] === 'number' ? 'number' : 'text' }}"
                           name="metadata[{{ $field['code'] }}]"
                           value="{{ $fieldValue }}"
                           class="form-control"
                           @if($field['mandatory']) required @endif
                           @if($field['readonly']) disabled @endif>
                @endif
            </div>
        @endforeach
    </div>
@endif
