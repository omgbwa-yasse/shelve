@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h4 class="mb-0">AI Model Details</h4>
                    <div>
                        <a href="{{ route('ai.models.edit', $aiModel) }}" class="btn btn-primary btn-sm">Edit</a>
                        <form action="{{ route('ai.models.destroy', $aiModel) }}" method="POST" class="d-inline">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure you want to delete this model?')">Delete</button>
                        </form>
                    </div>
                </div>

                <div class="card-body">
                    <div class="mb-4">
                        <h5>Basic Information</h5>
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
                        </table>
                    </div>

                    @if($aiModel->capabilities)
                    <div class="mb-4">
                        <h5>Capabilities</h5>
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
