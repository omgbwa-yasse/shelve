{{--
    Dynamic Metadata Fields Component

    Displays dynamic metadata fields based on the selected folder or document type

    Props:
    - $metadataDefinitions: Collection of metadata definitions with pivot data
    - $currentValues: Array of current metadata values (for edit mode)
    - $readonly: Boolean to make all fields readonly (for show mode)
--}}

@props(['metadataDefinitions' => [], 'currentValues' => [], 'readonly' => false])

<div x-data="metadataFields" class="space-y-4">
    @foreach($metadataDefinitions as $definition)
        @php
            $fieldName = "metadata[{$definition->name}]";
            $currentValue = $currentValues[$definition->name] ?? $definition->pivot->default_value ?? '';
            $isMandatory = $definition->pivot->mandatory ?? false;
            $isReadonly = $readonly || ($definition->pivot->readonly ?? false);
            $isVisible = $definition->pivot->visible ?? true;
        @endphp

        @if($isVisible)
            <div class="metadata-field" data-field-name="{{ $definition->name }}">
                <label for="metadata_{{ $definition->name }}"
                       class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                    {{ $definition->label }}
                    @if($isMandatory)
                        <span class="text-red-500">*</span>
                    @endif
                </label>

                @if($definition->description)
                    <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">{{ $definition->description }}</p>
                @endif

                <div class="mt-1">
                    @switch($definition->data_type)
                        @case('text')
                            <input type="text"
                                   name="{{ $fieldName }}"
                                   id="metadata_{{ $definition->name }}"
                                   value="{{ old($fieldName, $currentValue) }}"
                                   {{ $isMandatory ? 'required' : '' }}
                                   {{ $isReadonly ? 'readonly' : '' }}
                                   class="block w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 {{ $isReadonly ? 'bg-gray-100 dark:bg-gray-600' : '' }}">
                            @break

                        @case('long_text')
                            <textarea name="{{ $fieldName }}"
                                      id="metadata_{{ $definition->name }}"
                                      rows="3"
                                      {{ $isMandatory ? 'required' : '' }}
                                      {{ $isReadonly ? 'readonly' : '' }}
                                      class="block w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 {{ $isReadonly ? 'bg-gray-100 dark:bg-gray-600' : '' }}">{{ old($fieldName, $currentValue) }}</textarea>
                            @break

                        @case('number')
                            <input type="number"
                                   name="{{ $fieldName }}"
                                   id="metadata_{{ $definition->name }}"
                                   value="{{ old($fieldName, $currentValue) }}"
                                   step="any"
                                   {{ $isMandatory ? 'required' : '' }}
                                   {{ $isReadonly ? 'readonly' : '' }}
                                   class="block w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 {{ $isReadonly ? 'bg-gray-100 dark:bg-gray-600' : '' }}">
                            @break

                        @case('date')
                            <input type="date"
                                   name="{{ $fieldName }}"
                                   id="metadata_{{ $definition->name }}"
                                   value="{{ old($fieldName, $currentValue) }}"
                                   {{ $isMandatory ? 'required' : '' }}
                                   {{ $isReadonly ? 'readonly' : '' }}
                                   class="block w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 {{ $isReadonly ? 'bg-gray-100 dark:bg-gray-600' : '' }}">
                            @break

                        @case('datetime')
                            <input type="datetime-local"
                                   name="{{ $fieldName }}"
                                   id="metadata_{{ $definition->name }}"
                                   value="{{ old($fieldName, $currentValue) }}"
                                   {{ $isMandatory ? 'required' : '' }}
                                   {{ $isReadonly ? 'readonly' : '' }}
                                   class="block w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 {{ $isReadonly ? 'bg-gray-100 dark:bg-gray-600' : '' }}">
                            @break

                        @case('boolean')
                            <div class="flex items-center">
                                <input type="checkbox"
                                       name="{{ $fieldName }}"
                                       id="metadata_{{ $definition->name }}"
                                       value="1"
                                       {{ old($fieldName, $currentValue) ? 'checked' : '' }}
                                       {{ $isReadonly ? 'disabled' : '' }}
                                       class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                                <input type="hidden" name="{{ $fieldName }}" value="0">
                            </div>
                            @break

                        @case('email')
                            <input type="email"
                                   name="{{ $fieldName }}"
                                   id="metadata_{{ $definition->name }}"
                                   value="{{ old($fieldName, $currentValue) }}"
                                   {{ $isMandatory ? 'required' : '' }}
                                   {{ $isReadonly ? 'readonly' : '' }}
                                   class="block w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 {{ $isReadonly ? 'bg-gray-100 dark:bg-gray-600' : '' }}">
                            @break

                        @case('url')
                            <input type="url"
                                   name="{{ $fieldName }}"
                                   id="metadata_{{ $definition->name }}"
                                   value="{{ old($fieldName, $currentValue) }}"
                                   {{ $isMandatory ? 'required' : '' }}
                                   {{ $isReadonly ? 'readonly' : '' }}
                                   class="block w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 {{ $isReadonly ? 'bg-gray-100 dark:bg-gray-600' : '' }}">
                            @break

                        @case('select')
                            @if($definition->referenceList)
                                <select name="{{ $fieldName }}"
                                        id="metadata_{{ $definition->name }}"
                                        {{ $isMandatory ? 'required' : '' }}
                                        {{ $isReadonly ? 'disabled' : '' }}
                                        class="block w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 {{ $isReadonly ? 'bg-gray-100 dark:bg-gray-600' : '' }}">
                                    <option value="">-- {{ __('Select') }} --</option>
                                    @foreach($definition->referenceList->values as $value)
                                        <option value="{{ $value->value }}"
                                                {{ old($fieldName, $currentValue) == $value->value ? 'selected' : '' }}>
                                            {{ $value->display_value }}
                                        </option>
                                    @endforeach
                                </select>
                            @endif
                            @break

                        @case('multi_select')
                            @if($definition->referenceList)
                                <select name="{{ $fieldName }}[]"
                                        id="metadata_{{ $definition->name }}"
                                        multiple
                                        {{ $isMandatory ? 'required' : '' }}
                                        {{ $isReadonly ? 'disabled' : '' }}
                                        class="block w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 {{ $isReadonly ? 'bg-gray-100 dark:bg-gray-600' : '' }}">
                                    @php
                                        $selectedValues = is_array(old($fieldName, $currentValue))
                                            ? old($fieldName, $currentValue)
                                            : (is_string($currentValue) ? json_decode($currentValue, true) : []);
                                    @endphp
                                    @foreach($definition->referenceList->values as $value)
                                        <option value="{{ $value->value }}"
                                                {{ in_array($value->value, $selectedValues ?? []) ? 'selected' : '' }}>
                                            {{ $value->display_value }}
                                        </option>
                                    @endforeach
                                </select>
                            @endif
                            @break

                        @case('json')
                            <textarea name="{{ $fieldName }}"
                                      id="metadata_{{ $definition->name }}"
                                      rows="4"
                                      placeholder='{"key": "value"}'
                                      {{ $isMandatory ? 'required' : '' }}
                                      {{ $isReadonly ? 'readonly' : '' }}
                                      class="block w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 font-mono text-sm {{ $isReadonly ? 'bg-gray-100 dark:bg-gray-600' : '' }}">{{ old($fieldName, is_array($currentValue) ? json_encode($currentValue, JSON_PRETTY_PRINT) : $currentValue) }}</textarea>
                            @break
                    @endswitch
                </div>

                @error($fieldName)
                    <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                @enderror
            </div>
        @endif
    @endforeach
</div>

<script>
    document.addEventListener('alpine:init', () => {
        Alpine.data('metadataFields', () => ({
            init() {
                // Additional initialization if needed
            }
        }));
    });
</script>
