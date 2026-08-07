@extends('layouts.app')

@section('content')
    <div class="container">
        {{-- Documents archivés --}}
        <div class="mb-4">
            <h2 class="mb-3">Documents archivés</h2>
            @if(empty($records))
                <div class="alert alert-info text-center" role="alert">
                    Aucun document archivé disponible
                </div>
            @else
                <div class="table-responsive">
                    <table class="table table-striped table-hover table-bordered">
                        <thead class="table-light">
                        <tr>
                            <th>ID</th>
                            <th>Code</th>
                            <th>Name</th>
                            <th>Actions</th>
                        </tr>
                        </thead>
                        <tbody>
                        @forelse ($records ?? [] as $record)
                            <tr>
                                <td>{{ $record->id }}</td>
                                <td>{{ $record->code }}</td>
                                <td>{{ $record->name }}</td>
                                <td>
                                    <a href="{{ route('records.show', $record) }}" class="btn btn-info btn-sm">Voir la fiche</a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="text-center">Aucune donnée disponible</td>
                            </tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>
            @endif
        </div>

        {{-- Courriers entrants --}}
        <div class="mb-4">
            <h2 class="mb-3">Courriers entrants</h2>
            @if(empty($incomingMails))
                <div class="alert alert-info text-center" role="alert">
                    Aucun courrier entrant disponible
                </div>
            @else
                <div class="table-responsive">
                    <table class="table table-striped table-hover table-bordered">
                        <thead class="table-light">
                        <tr>
                            <th>Code</th>
                            <th>Object, Auteur</th>
                            <th>Date</th>
                            <th>Producteur</th>
                            <th>Localisation</th>
                            <th>Type</th>
                            <th>Actions</th>
                        </tr>
                        </thead>
                        <tbody>
                        @forelse ($incomingMails ?? [] as $mail)
                            <tr>
                                <td>{{ $mail->code }}</td>
                                <td>
                                    {{ $mail->name }}
                                    @if(!empty($mail->authors))
                                        <small class="text-muted">
                                            par {{ collect($mail->authors)->pluck('name')->join(', ') }}
                                        </small>
                                    @endif
                                </td>
                                <td>{{ $mail->date }}</td>
                                <td>{{ $mail->creator->name ?? '-' }}</td>
                                <td>
                                    @if(!empty($mail->container))
                                        @foreach($mail->container as $container)
                                            <div>{{ $container->code ?? '' }} ({{ $container->name ?? 'Non conditionné' }})</div>
                                        @endforeach
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                                <td>{{ $mail->type->name ?? '-' }}</td>
                                <td>
                                    <a href="{{ route('incoming-mails.show', $mail->id) }}" class="btn btn-info btn-sm">Détails</a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center">Aucune donnée disponible</td>
                            </tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>
            @endif
        </div>

        {{-- Courriers sortants --}}
        <div class="mb-4">
            <h2 class="mb-3">Courriers sortants</h2>
            @if(empty($outgoingMails))
                <div class="alert alert-info text-center" role="alert">
                    Aucun courrier sortant disponible
                </div>
            @else
                <div class="table-responsive">
                    <table class="table table-striped table-hover table-bordered">
                        <thead class="table-light">
                        <tr>
                            <th>Code</th>
                            <th>Object, Auteur</th>
                            <th>Date</th>
                            <th>Producteur</th>
                            <th>Localisation</th>
                            <th>Type</th>
                            <th>Actions</th>
                        </tr>
                        </thead>
                        <tbody>
                        @forelse ($outgoingMails ?? [] as $mail)
                            <tr>
                                <td>{{ $mail->code }}</td>
                                <td>
                                    {{ $mail->name }}
                                    @if(!empty($mail->authors))
                                        <small class="text-muted">
                                            par {{ collect($mail->authors)->pluck('name')->join(', ') }}
                                        </small>
                                    @endif
                                </td>
                                <td>{{ $mail->date }}</td>
                                <td>{{ $mail->creator->name ?? '-' }}</td>
                                <td>
                                    @if(!empty($mail->container))
                                        @foreach($mail->container as $container)
                                            <div>{{ $container->code ?? '' }} ({{ $container->name ?? 'Non conditionné' }})</div>
                                        @endforeach
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                                <td>{{ $mail->type->name ?? '-' }}</td>
                                <td>
                                    <a href="{{ route('outgoing-mails.show', $mail->id) }}" class="btn btn-info btn-sm">Détails</a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center">Aucune donnée disponible</td>
                            </tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>
            @endif
        </div>

        {{-- Archives versées --}}
        <div class="mb-4">
            <h2 class="mb-3">Archives versées</h2>
            @if(empty($transferringRecords))
                <div class="alert alert-info text-center" role="alert">
                    Aucune archive versée disponible
                </div>
            @else
                <div class="table-responsive">
                    <table class="table table-striped table-hover table-bordered">
                        <thead class="table-light">
                        <tr>
                            <th>ID</th>
                            <th>Code</th>
                            <th>Name</th>
                            <th>Versement</th>
                            <th>Actions</th>
                        </tr>
                        </thead>
                        <tbody>
                        @forelse ($transferringRecords ?? [] as $record)
                            <tr>
                                <td>{{ $record->id }}</td>
                                <td>{{ $record->code }}</td>
                                <td>{{ $record->name }}</td>
                                <td>
                                    {{ $record->slip->code ?? '' }} {{ !empty($record->slip->name) ? '- ' . $record->slip->name : '' }}
                                </td>
                                <td>
                                    @if(!empty($record->slip))
                                        <a href="{{ route('slips.show', $record->slip->id) }}" class="btn btn-info btn-sm">Détails</a>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center">Aucune donnée disponible</td>
                            </tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>
            @endif
        </div>

        {{-- Versements --}}
        <div class="mb-4">
            <h2 class="mb-3">Versements</h2>
            @if(empty($transferrings))
                <div class="alert alert-info text-center" role="alert">
                    Aucun versement disponible
                </div>
            @else
                <div class="table-responsive">
                    <table class="table table-striped table-hover table-bordered">
                        <thead class="table-light">
                        <tr>
                            <th>ID</th>
                            <th>Code</th>
                            <th>Name</th>
                            <th>Date Format</th>
                            <th>Actions</th>
                        </tr>
                        </thead>
                        <tbody>
                        @forelse ($transferrings ?? [] as $record)
                            <tr>
                                <td>{{ $record->id }}</td>
                                <td>{{ $record->code }}</td>
                                <td>{{ $record->name }}</td>
                                <td>{{ $record->date_format }}</td>
                                <td>
                                    <a href="{{ route('slips.show', $record->id) }}" class="btn btn-info btn-sm">Détails</a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center">Aucune donnée disponible</td>
                            </tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </div>
@endsection
