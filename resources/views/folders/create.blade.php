<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Create New Folder') }}
        </h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm p-6">
                <form method="POST" action="{{ route('folders.store') }}" class="space-y-6">
                    @csrf

                    {{-- Name --}}
                    <div>
                        <label for="name" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                            Name <span class="text-red-500">*</span>
                        </label>
                        <input type="text" name="name" id="name" value="{{ old('name') }}" required
                               class="mt-1 block w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100">
                        @error('name')
                            <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Description --}}
                    <div>
                        <label for="description" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                            Description
                        </label>
                        <textarea name="description" id="description" rows="3"
                                  class="mt-1 block w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100">{{ old('description') }}</textarea>
                        @error('description')
                            <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Parent Folder --}}
                    <div>
                        <label for="parent_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                            Parent Folder
                        </label>
                        <select name="parent_id" id="parent_id"
                                class="mt-1 block w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100">
                            <option value="">-- Root Level --</option>
                            @foreach($allFolders as $folder)
                                <option value="{{ $folder->id }}"
                                        {{ (old('parent_id') == $folder->id || ($parentFolder && $parentFolder->id == $folder->id)) ? 'selected' : '' }}>
                                    {{ str_repeat('—', $folder->depth ?? 0) }} {{ $folder->name }} ({{ $folder->code }})
                                </option>
                            @endforeach
                        </select>
                        @error('parent_id')
                            <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Organization --}}
                    <div>
                        <label for="organization_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                            Organization <span class="text-red-500">*</span>
                        </label>
                        <select name="organization_id" id="organization_id" required
                                class="mt-1 block w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100">
                            <option value="">-- Select Organization --</option>
                            @foreach(\App\Models\Organisation::all() as $org)
                                <option value="{{ $org->id }}" {{ old('organization_id') == $org->id ? 'selected' : '' }}>
                                    {{ $org->name }}
                                </option>
                            @endforeach
                        </select>
                        @error('organization_id')
                            <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Type --}}
                    <div>
                        <label for="type_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                            Folder Type <span class="text-red-500">*</span>
                        </label>
                        <select name="type_id" id="type_id" required
                                class="mt-1 block w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100">
                            <option value="">-- Select Type --</option>
                            @foreach(\App\Models\RecordDigitalFolderType::all() as $type)
                                <option value="{{ $type->id }}" {{ old('type_id') == $type->id ? 'selected' : '' }}>
                                    {{ $type->name }} - {{ $type->description }}
                                </option>
                            @endforeach
                        </select>
                        @error('type_id')
                            <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Access Level --}}
                    <div>
                        <label for="access_level" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                            Access Level
                        </label>
                        <select name="access_level" id="access_level"
                                class="mt-1 block w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100">
                            <option value="public" {{ old('access_level') == 'public' ? 'selected' : '' }}>Public</option>
                            <option value="internal" {{ old('access_level') == 'internal' ? 'selected' : '' }}>Internal</option>
                            <option value="confidential" {{ old('access_level') == 'confidential' ? 'selected' : '' }}>Confidential</option>
                            <option value="secret" {{ old('access_level') == 'secret' ? 'selected' : '' }}>Secret</option>
                        </select>
                        @error('access_level')
                            <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Metadata (optional JSON) --}}
                    <div x-data="{ showMetadata: false }">
                        <button type="button" @click="showMetadata = !showMetadata"
                                class="text-sm text-blue-600 dark:text-blue-400 hover:underline">
                            <span x-show="!showMetadata">+ Add metadata</span>
                            <span x-show="showMetadata">- Hide metadata</span>
                        </button>
                        <div x-show="showMetadata" x-cloak class="mt-2">
                            <label for="metadata" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                Metadata (JSON format)
                            </label>
                            <textarea name="metadata" id="metadata" rows="4" placeholder='{"key": "value"}'
                                      class="mt-1 block w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 font-mono text-sm">{{ old('metadata') }}</textarea>
                            @error('metadata')
                                <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    {{-- Actions --}}
                    <div class="flex items-center justify-end space-x-3 pt-6 border-t border-gray-200 dark:border-gray-700">
                        <a href="{{ route('folders.index') }}"
                           class="px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm text-sm font-medium text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600 transition-colors">
                            Cancel
                        </a>
                        <button type="submit"
                                class="px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors">
                            Create Folder
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
