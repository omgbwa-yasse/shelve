@extends('layouts.app')

@section('content')
    <div class="container">
        <div class="card shadow-sm">
            <div class="card-header bg-light">
                <h2><i class="bi bi-file-earmark-arrow-down"></i> {{ __('Import Records') }}</h2>
            </div>
            <div class="card-body">
                @if(session('success'))
                    <div class="alert alert-success">
                        {{ session('success') }}
                    </div>
                @endif

                @if(session('error'))
                    <div class="alert alert-danger">
                        {{ session('error') }}
                    </div>
                @endif

                @if ($errors->any())
                    <div class="alert alert-danger">
                        <ul>
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form action="{{ route('records.import') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="form-group mb-3">
                        <label for="file" class="form-label">{{ __('File') }}:</label>
                        <input type="file" name="file" id="file" class="form-control @error('file') is-invalid @enderror" required>
                        @error('file')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="form-group mb-3">
                        <label for="format" class="form-label">{{ __('Format') }}:</label>
                        <select name="format" id="format" class="form-select @error('format') is-invalid @enderror" required>
                            <option value="">{{ __('Select a format') }}</option>
                            <option value="excel">Excel</option>
                            <option value="ead">EAD</option>
                            <option value="seda">SEDA</option>
                        </select>
                        @error('format')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="form-group mb-3 excel-options" style="display: none;">
                        <div class="card">
                            <div class="card-header">
                                {{ __('Excel Import Options') }}
                            </div>
                            <div class="card-body">
                                <div class="form-check mb-2">
                                    <input class="form-check-input" type="checkbox" name="has_headers" id="has_headers" value="1" checked>
                                    <label class="form-check-label" for="has_headers">
                                        {{ __('File has headers') }}
                                    </label>
                                </div>
                                <div class="form-check mb-2">
                                    <input class="form-check-input" type="checkbox" name="update_existing" id="update_existing" value="1">
                                    <label class="form-check-label" for="update_existing">
                                        {{ __('Update existing records') }}
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="d-flex justify-content-between">
                        <a href="{{ route('records.index') }}" class="btn btn-secondary">{{ __('Cancel') }}</a>
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-file-earmark-arrow-down me-1"></i>{{ __('Import') }}
                        </button>
                    </div>
                </form>
            </div>

            <div id="excel-instructions" class="card-footer bg-light" style="display: none;">
                <h5>{{ __('Excel Import Field Instructions') }}</h5>
                <div class="table-responsive">
                    <table class="table table-striped table-bordered">
                        <thead>
                            <tr class="bg-dark text-secondary">
                                <th>{{ __('Field Name') }}</th>
                                <th>{{ __('Description') }}</th>
                                <th>{{ __('Format') }}</th>
                                <th>{{ __('Required') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td><code>code</code></td>
                                <td>{{ __('Record unique identifier') }}</td>
                                <td>{{ __('Text') }}</td>
                                <td>{{ __('Yes') }}</td>
                            </tr>
                            <tr>
                                <td><code>name</code></td>
                                <td>{{ __('Record name/title') }}</td>
                                <td>{{ __('Text') }}</td>
                                <td>{{ __('Yes') }}</td>
                            </tr>
                            <tr>
                                <td><code>date_format</code></td>
                                <td>{{ __('Format of the date') }}</td>
                                <td>{{ __('Text') }}</td>
                                <td>{{ __('No') }}</td>
                            </tr>
                            <tr>
                                <td><code>start_date</code></td>
                                <td>{{ __('Starting date of the record') }}</td>
                                <td>{{ __('YYYY-MM-DD') }}</td>
                                <td>{{ __('No') }}</td>
                            </tr>
                            <tr>
                                <td><code>end_date</code></td>
                                <td>{{ __('Ending date of the record') }}</td>
                                <td>{{ __('YYYY-MM-DD') }}</td>
                                <td>{{ __('No') }}</td>
                            </tr>
                            <tr>
                                <td><code>exact_date</code></td>
                                <td>{{ __('Exact date of the record') }}</td>
                                <td>{{ __('YYYY-MM-DD') }}</td>
                                <td>{{ __('No') }}</td>
                            </tr>
                            <tr>
                                <td><code>level</code></td>
                                <td>{{ __('Hierarchical level') }}</td>
                                <td>{{ __('Text') }}</td>
                                <td>{{ __('Yes') }}</td>
                            </tr>
                            <tr>
                                <td><code>width</code></td>
                                <td>{{ __('Physical width of the record') }}</td>
                                <td>{{ __('Number') }}</td>
                                <td>{{ __('No') }}</td>
                            </tr>
                            <tr>
                                <td><code>width_description</code></td>
                                <td>{{ __('Description of the width') }}</td>
                                <td>{{ __('Text') }}</td>
                                <td>{{ __('No') }}</td>
                            </tr>
                            <tr>
                                <td><code>content</code></td>
                                <td>{{ __('Content description of the record') }}</td>
                                <td>{{ __('Text') }}</td>
                                <td>{{ __('No') }}</td>
                            </tr>
                            <tr>
                                <td><code>status</code></td>
                                <td>{{ __('Status of the record') }}</td>
                                <td>{{ __('Text') }}</td>
                                <td>{{ __('Yes') }}</td>
                            </tr>
                            <tr>
                                <td><code>support</code></td>
                                <td>{{ __('Physical support type') }}</td>
                                <td>{{ __('Text') }}</td>
                                <td>{{ __('Yes') }}</td>
                            </tr>
                            <tr>
                                <td><code>activity</code></td>
                                <td>{{ __('Related activity') }}</td>
                                <td>{{ __('Text') }}</td>
                                <td>{{ __('Yes') }}</td>
                            </tr>
                            <tr>
                                <td><code>authors</code></td>
                                <td>{{ __('Record authors') }}</td>
                                <td>{{ __('Comma separated names') }}</td>
                                <td>{{ __('No') }}</td>
                            </tr>
                            <tr>
                                <td><code>terms</code></td>
                                <td>{{ __('Related terms/tags') }}</td>
                                <td>{{ __('Comma separated values') }}</td>
                                <td>{{ __('No') }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <div class="alert alert-info mt-3">
                    <p><strong>{{ __('Note') }}:</strong> {{ __('For EAD and SEDA imports, files must be valid XML following the respective schema.') }}</p>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const formatSelect = document.getElementById('format');
        const excelOptions = document.querySelector('.excel-options');
        const excelInstructions = document.getElementById('excel-instructions');

        // Show/hide Excel options based on format selection
        formatSelect.addEventListener('change', function() {
            if (this.value === 'excel') {
                excelOptions.style.display = 'block';
            } else {
                excelOptions.style.display = 'none';
            }
        });

        // Trigger on page load if excel is already selected
        if (formatSelect.value === 'excel') {
            excelOptions.style.display = 'block';
        }

        // Show/hide Excel options and instructions based on format selection
        formatSelect.addEventListener('change', function() {
            if (this.value === 'excel') {
                excelOptions.style.display = 'block';
                excelInstructions.style.display = 'block';
            } else {
                excelOptions.style.display = 'none';
                excelInstructions.style.display = 'none';
            }
        });

        // Trigger on page load if excel is already selected
        if (formatSelect.value === 'excel') {
            excelOptions.style.display = 'block';
            excelInstructions.style.display = 'block';
        }

});


</script>
@endpush
