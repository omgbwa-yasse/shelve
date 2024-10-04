@extends('layouts.app')

@section('content')
    <div class="container">
        <h1><i class="bi bi-building"></i> Organisations</h1>
        <table class="table">
            <thead>
            <tr>
                <th><i class="bi bi-hash"></i> Code</th>
                <th><i class="bi bi-person-vcard"></i> Name</th>
                <th><i class="bi bi-info-circle"></i> Description</th>
                <th><i class="bi bi-arrow-up-circle"></i> Versements émis</th>
                <th><i class="bi bi-arrow-down-circle"></i> Versements reçus</th>
            </tr>
            </thead>
            <tbody>
            @foreach ($organisations as $organisation)
                <tr>
                    <td>{{ $organisation->code }}</td>
                    <td>{{ $organisation->name }}</td>
                    <td>{{ $organisation->description }}</td>
                    <td>
                        <a href="{{ route('slips-sort')}}?categ=user-organisation&id={{ $organisation->id }}">
                            <i class="bi bi-arrow-up-circle"></i> {{ $organisation->userSlips->count() }}
                        </a>
                    </td>
                    <td>
                        <a href="{{ route('slips-sort')}}?categ=officer-organisation&id={{ $organisation->id }}">
                            <i class="bi bi-arrow-down-circle "></i> {{ $organisation->officerSlips->count() }}
                        </a>
                    </td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>
@endsection
