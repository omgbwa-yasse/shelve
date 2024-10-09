@extends('layouts.app')

@section('content')
    <div class="container mt-4">
        <div class="card">

            <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                <h3 class="h3 mb-0">Attachments for {{ $record->name }}</h3>
                <a href="{{ route('records.attachments.create', $record) }}" class="btn btn-light">
                    <i class="fas fa-plus"></i> Add Attachment
                </a>
                <a href="{{ route('records.show', $record) }}" class="btn btn-light">
                     <- Back
                </a>
            </div>
            <div class="card-body">
                @if($attachments->isEmpty())
                    <p class="text-muted">No attachments found.</p>
                @else
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead class="thead-light">
                            <tr>
                                <th>Name</th>
                                <th>Actions</th>
                            </tr>
                            </thead>
                            <tbody>
                            @foreach($attachments as $attachment)
                                <tr>
                                    <td>{{ $attachment->name }}</td>

                                    <td>
                                        {{-- <form action="{{ route('records.attachments.destroy', [$record, $attachment]) }}" method="POST" class="d-inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure you want to delete this attachment?')">
                                                <i class="fas fa-trash"></i> Delete
                                            </button>
                                        </form> --}}
                                        <a href="#" class="btn btn-info btn-sm">
                                            <i class="fas fa-download"></i> Download
                                        </a>
                                    </td>
                                </tr>
                            @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>
        </div>
    </div>
@endsection


    <style>
        .card-header {
            background-color: #4a5568 !important;
        }
        .btn-light {
            background-color: #fff;
            border-color: #cbd5e0;
        }
        .btn-light:hover {
            background-color: #f7fafc;
        }
    </style>
