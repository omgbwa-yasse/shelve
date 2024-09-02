@extends('layouts.app')

@section('content')
    <div class="container">
        <h1>Organisations</h1>
        <table class="table">
            <thead>
                <tr>
                    <th>Code</th>
                    <th>Name</th>
                    <th>Description</th>
                    <th>versements émis</th>
                    <th>versements reçus</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($organisations as $organisation)
                    <tr>
                        <td>{{ $organisation->code }}</td>
                        <td>
                            {{ $organisation->name }}
                        </td>
                        <td>
                            {{ $organisation->description }}
                        </td>
                        <td>
                            <a href="{{ route('slips-sort')}}?categ=user-organisation&id={{ $organisation->id }}">
                                {{ $organisation->userSlips->count() }}
                            </a>
                        </td>
                        <td>
                            <a href="{{ route('slips-sort')}}?categ=officer-organisation&id={{ $organisation->id }}">
                                {{ $organisation->officerSlips->count() }}
                            </a>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
@endsection
