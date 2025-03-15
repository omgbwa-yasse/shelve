@extends('layouts.app')

@section('content')
    <div class="container">
        <h1>{{ __('Languages') }}</h1>
        <a href="{{ route('languages.create') }}" class="btn btn-primary mb-3">{{ __('add_language') }}</a>
        <table class="table">
            <thead>
                <tr>
                    <th>{{ __('Code') }}</th>
                    <th>{{ __('Name') }}</th>
                    <th>{{ __('Actions') }}</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($languages as $language)
                    <tr>
                        <td>{{ $language->code }}</td>
                        <td>{{ $language->name }}</td>
                        <td>
                            <a href="{{ route('languages.show', $language->id) }}" class="btn btn-info">{{ __('Settings') }}</a>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
@endsection
