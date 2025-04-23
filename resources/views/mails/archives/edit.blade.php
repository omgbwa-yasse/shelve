@extends('layouts.app')

@section('content')
<div class="container">
    <h1 class="mb-4">Modifier l'archivage</h1>
    
    <form action="{{ route('mail-archive.update', ['archive' => $mailArchive->id]) }}" method="POST">
        @csrf
        @method('PUT')
        
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0">Détails de l'archivage</h5>
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <label for="container_id" class="form-label">Container</label>
                    <select class="form-select" name="container_id" id="container_id">
                        @foreach ($mailContainers as $container)
                            <option value="{{ $container->id }}" {{ $container->id == $mailArchive->container_id ? 'selected' : '' }}>
                                {{ $container->code }} - {{ $container->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                
                <div class="mb-3">
                    <label for="mail_id" class="form-label">Courrier</label>
                    <select class="form-select" name="mail_id" id="mail_id">
                        @foreach ($mails as $mail)
                            <option value="{{ $mail->id }}" {{ $mail->id == $mailArchive->mail_id ? 'selected' : '' }}>
                                {{ $mail->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                
                <div class="mb-3">
                    <label for="document_type" class="form-label">Type de document</label>
                    <select class="form-select" name="document_type" id="document_type">
                        <option value="original" {{ $mailArchive->document_type == 'original' ? 'selected' : '' }}>Original</option>
                        <option value="copy" {{ $mailArchive->document_type == 'copy' ? 'selected' : '' }}>Copie</option>
                        <option value="duplicate" {{ $mailArchive->document_type == 'duplicate' ? 'selected' : '' }}>Duplicata</option>
                    </select>
                </div>
            </div>
        </div>
        
        <div class="d-flex justify-content-between">
            <a href="{{ route('mail-archive.index') }}" class="btn btn-secondary">Annuler</a>
            <button type="submit" class="btn btn-primary">Mettre à jour</button>
        </div>
    </form>
</div>
@endsection