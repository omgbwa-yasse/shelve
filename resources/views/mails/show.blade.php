@extends('layouts.app')

@section('content')
    <div class="container-fluid">
        <div class="row justify-content-center">
            <div class="">
                <div class="">
                    <div class="card-header bg-primary text-white">
                        <h2 class="mb-0">{{ $mail->name }}</h2>
                    </div>
                    <div class="card-body">
                        <ul class="nav nav-tabs" id="mailTabs" role="tablist">
                            <li class="nav-item">
                                <a class="nav-link active" id="details-tab" data-toggle="tab" href="#details" role="tab">Details</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" id="attachments-tab" data-toggle="tab" href="#attachments" role="tab">Attachments</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" id="transactions-tab" data-toggle="tab" href="#transactions" role="tab">Transaction History</a>
                            </li>
                        </ul>
                        <div class="tab-content mt-3" id="mailTabsContent">
                            <div class="tab-pane fade show active" id="details" role="tabpanel">
                                <div class="row">
                                    <div class="col-md-6">
                                        <h5 class="border-bottom pb-2 mb-3">Basic Information</h5>
                                        <p><strong>ID:</strong> {{ $mail->id }}</p>
                                        <p><strong>Code:</strong> {{ $mail->code }}</p>
                                        <p><strong>Date:</strong> {{ $mail->date }}</p>
                                        <p><strong>Author(s):</strong>
                                            @foreach($mail->authors as $author)
                                                <span class="badge badge-info">{{ $author->name }}</span>
                                            @endforeach
                                        </p>
                                    </div>
                                    <div class="col-md-6">
                                        <h5 class="border-bottom pb-2 mb-3">Classification</h5>
                                        <p><strong>Priority:</strong> <span class="badge badge-{{ $mail->priority ? 'warning' : 'secondary' }}">{{ $mail->priority ? $mail->priority->name : 'N/A' }}</span></p>
                                        <p><strong>Mail Type:</strong> {{ $mail->type ? $mail->type->name : 'N/A' }}</p>
                                        <p><strong>Business Type:</strong> {{ $mail->typology ? $mail->typology->name : 'N/A' }}</p>
                                        <p><strong>Nature:</strong> {{ $mail->documentType ? $mail->documentType->name : 'N/A' }}</p>
                                    </div>
                                </div>
                                <div class="mt-4">
                                    <h5 class="border-bottom pb-2 mb-3">Description</h5>
                                    <p>{{ $mail->description }}</p>
                                </div>
                                <div class="mt-4">
                                    <button class="btn btn-secondary" onclick="window.history.back()">
                                        <i class="bi bi-arrow-left"></i> Back
                                    </button>
                                    <a href="{{ route('mails.edit', $mail->id) }}" class="btn btn-warning">
                                        <i class="bi bi-pencil"></i> Edit
                                    </a>
                                    <button class="btn btn-danger" onclick="confirmDelete()">
                                        <i class="bi bi-trash"></i> Delete
                                    </button>
                                    <a href="{{ route('mail-attachment.create', ['file' => $mail]) }}" class="btn btn-info">
                                        <i class="bi bi-paperclip"></i> Add Attachment
                                    </a>
                                </div>
                            </div>

                            <div class="tab-pane fade" id="attachments" role="tabpanel">
                                <h5 class="border-bottom pb-2 mb-3">Attachments</h5>
                                @if($mail->attachments->isNotEmpty())
                                    <ul class="list-group">
                                        @foreach($mail->attachments as $attachment)
                                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                                <div>
                                                    <i class="bi bi-file-earmark me-2"></i>
                                                    <span>{{ $attachment->name }}</span>
                                                    <small class="text-muted ms-2">({{ number_format($attachment->size / 1024, 2) }} KB)</small>
                                                </div>
                                                <div>
                                                    <a href="{{ route('mail-attachment.show', [$mail->id, $attachment->id]) }}" class="btn btn-sm btn-outline-primary" target="_blank">
                                                        <i class="bi bi-download"></i>
                                                    </a>
                                                </div>
                                            </li>
                                        @endforeach
                                    </ul>
                                @else
                                    <div class="alert alert-info">No attachments found.</div>
                                @endif

                                <form action="{{ route('mail-attachment.store', $mail->id) }}" method="POST" enctype="multipart/form-data">
                                    @csrf
                                    <div class="row mb-3">
                                            <div class="col">
                                                <label for="name" class="form-label">Nom du fichier</label>
                                                <input type="text" class="form-control" id="name" name="name" required>
                                            </div>
                                            <div class="col">
                                                <label for="file" class="form-label">Ajouter un fichier</label>
                                                <input class="form-control" type="file" id="formFile" name="file">
                                            </div>
                                        </div>
                                        <button type="submit" class="btn btn-primary">Enregistrer</button>
                                </form>

                            </div>
                            <div class="tab-pane fade" id="transactions" role="tabpanel">
                                <h5 class="border-bottom pb-2 mb-3">Transaction History</h5>
                                @if($mail->transactions->isNotEmpty())
                                    <div class="timeline">
                                        @foreach($mail->transactions as $transaction)
                                            <div class="timeline-item">
                                                <div class="timeline-badge bg-primary"></div>
                                                <div class="timeline-panel card">
                                                    <div class="card-body">
                                                        <h6 class="card-title">{{ $transaction->code }}</h6>
                                                        <p class="card-text">
                                                            <strong>Created:</strong> {{ $transaction->date_creation }}<br>
                                                            <strong>Sender:</strong> {{ $transaction->organisationSend ? $transaction->organisationSend->name : 'N/A' }} ({{ $transaction->userSend ? $transaction->userSend->name : 'N/A' }})<br>
                                                            <strong>Recipient:</strong> {{ $transaction->organisationReceived ? $transaction->organisationReceived->name : 'N/A' }} ({{ $transaction->userReceived ? $transaction->userReceived->name : 'N/A' }})<br>
                                                            <strong>Mail Type:</strong> {{ $transaction->type ? $transaction->type->name : 'N/A' }}<br>
                                                            <strong>Document Type:</strong> {{ $transaction->documentType ? $transaction->documentType->name : 'N/A' }}
                                                        </p>
                                                        <small class="text-muted">
                                                            Created: {{ $transaction->created_at->format('M d, Y H:i') }}<br>
                                                            Updated: {{ $transaction->updated_at->format('M d, Y H:i') }}
                                                        </small>
                                                    </div>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                @else
                                    <div class="alert alert-info">No transactions found.</div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('styles')

@endpush

@push('scripts')
    <script>
        function confirmDelete() {
            if (confirm('Are you sure you want to delete this mail?')) {
                document.getElementById('delete-form').submit();
            }
        }

        $(document).ready(function() {
            $('#mailTabs a').on('click', function (e) {
                e.preventDefault();
                $(this).tab('show');
            });
        });
    </script>
@endpush
