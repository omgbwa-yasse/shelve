@props([
    'name' => 'content',
    'id' => null,
    'value' => '',
    'rows' => 10,
    'required' => false,
    'placeholder' => 'Commencez à écrire...'
])

@php
    $editorId = $id ?? 'textarea_' . $name;
@endphp

<div class="mb-3">
    <label class="form-label" for="{{ $editorId }}">Contenu</label>
    <textarea
        name="{{ $name }}"
        id="{{ $editorId }}"
        class="form-control @error($name) is-invalid @enderror"
        rows="{{ $rows }}"
        placeholder="{{ $placeholder }}"
        @if($required) required @endif
    >{{ $value }}</textarea>

    @error($name)
        <div class="invalid-feedback">{{ $message }}</div>
    @enderror
</div>
