@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h4 class="mb-0">{{ __('ai_model_details') }}</h4>
                    <div>
                        <a href="{{ route('ai.models.edit', ['model' => $aiModel->id]) }}" class="btn btn-primary btn-sm">{{ __('edit') }}</a>
                        <form action="{{ route('ai.models.destroy', ['model' => $aiModel->id]) }}" method="POST" class="d-inline">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('{{ __('confirm_model_deletion') }}')">{{ __('delete') }}</button>
                        </form>
                        <a href="{{ route('ai.models.index') }}" class="btn btn-secondary btn-sm ml-2">{{ __('back_to_list') }}</a>
                    </div>
                </div>

                <div class="card-body">
                    <div class="mb-4">
                        <h5>Model Information</h5>
                        <table class="table">
                            <tr>
                                <th style="width: 200px;">Name</th>
                                <td>{{ $aiModel->name }}</td>
                            </tr>
                            <tr>
                                <th>Provider</th>
                                <td>{{ $aiModel->provider }}</td>
                            </tr>
                            <tr>
                                <th>Version</th>
                                <td>{{ $aiModel->version }}</td>
                            </tr>
                            <tr>
                                <th>API Type</th>
                                <td>{{ $aiModel->api_type }}</td>
                            </tr>
                            <tr>
                                <th>Status</th>
                                <td>
                                    <span class="badge bg-{{ $aiModel->is_active ? 'success' : 'danger' }}">
                                        {{ $aiModel->is_active ? 'Active' : 'Inactive' }}
                                    </span>
                                </td>
                            </tr>

                            @if($aiModel->capabilities)
                                @php
                                    $capabilitiesData = json_decode($aiModel->capabilities, true);
                                @endphp

                                <tr>
                                    <th>Size</th>
                                    <td>{{ isset($capabilitiesData['size']) ? number_format($capabilitiesData['size'] / (1024 * 1024 * 1024), 2) . ' GB' : 'N/A' }}</td>
                                </tr>
                                <tr>
                                    <th>Last Modified</th>
                                    <td>{{ isset($capabilitiesData['modified_at']) ? \Carbon\Carbon::parse($capabilitiesData['modified_at'])->format('Y-m-d H:i:s') : 'N/A' }}</td>
                                </tr>
                                <tr>
                                    <th>Digest</th>
                                    <td><code>{{ $capabilitiesData['digest'] ?? 'N/A' }}</code></td>
                                </tr>

                                @if(isset($capabilitiesData['details']))
                                    <tr>
                                        <th>Model Format</th>
                                        <td>{{ $capabilitiesData['details']['format'] ?? 'N/A' }}</td>
                                    </tr>
                                    <tr>
                                        <th>Model Family</th>
                                        <td>{{ $capabilitiesData['details']['family'] ?? 'N/A' }}</td>
                                    </tr>
                                    <tr>
                                        <th>All Families</th>
                                        <td>
                                            @if(isset($capabilitiesData['details']['families']) && is_array($capabilitiesData['details']['families']))
                                                @foreach($capabilitiesData['details']['families'] as $family)
                                                    <span class="badge bg-info me-1">{{ $family }}</span>
                                                @endforeach
                                            @else
                                                N/A
                                            @endif
                                        </td>
                                    </tr>
                                    <tr>
                                        <th>Parameter Size</th>
                                        <td>{{ $capabilitiesData['details']['parameter_size'] ?? 'N/A' }}</td>
                                    </tr>
                                    <tr>
                                        <th>Quantization Level</th>
                                        <td>{{ $capabilitiesData['details']['quantization_level'] ?? 'N/A' }}</td>
                                    </tr>
                                    @if(isset($capabilitiesData['details']['parent_model']) && !empty($capabilitiesData['details']['parent_model']))
                                    <tr>
                                        <th>Parent Model</th>
                                        <td>{{ $capabilitiesData['details']['parent_model'] }}</td>
                                    </tr>
                                    @endif
                                @endif
                            @endif
                        </table>
                    </div>

                    @if($aiModel->capabilities)
                    <div class="mb-4">
                        <h5>Raw Capabilities Data</h5>
                        <div class="card">
                            <div class="card-body">
                                <pre class="mb-0"><code>{{ json_encode(json_decode($aiModel->capabilities), JSON_PRETTY_PRINT) }}</code></pre>
                            </div>
                        </div>
                    </div>
                    @endif

                    <div class="mt-4">
                        <a href="{{ route('ai.models.index') }}" class="btn btn-secondary">Back to List</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
