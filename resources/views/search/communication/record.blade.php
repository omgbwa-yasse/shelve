@extends('layouts.app')

@section('content')
    <div class="container">
        <table class="table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Code</th>
                    <th>Name</th>
                    <th>Description </th>
                    <th>Dates </th>
                    <th>Producteur</th>
                    <th>Bordereau communication</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($records as $record)
                <tr>
                    <td>{{ $record->id }}</td>
                    <td>{{ $record->code }}</td>
                    <td>{{ $record->name }}</td>
                    <td>{{ $record->content }}</td>
                    <td>
                        @if ($record->date_start && $record->date_end)
                            {{ $record->date_start }} - {{ $record->date_end }}
                        @elseif ($record->date_exact)
                            {{ $record->date_exact }}
                        @else
                            {{ 'Sans date' }}
                        @endif
                    </td>
                    <td>{{ $record->author->name ?? 'Sans producteur' }}</td>
                    <td>{{ $record->communication->code }} - {{ $record->communication->name }}</td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>
@endsection
