@extends('layouts.app')

@section('content')
<div class="container">
    <h1>{{ __('Mail Actions') }}</h1>
    <a href="{{ route('mail-action.create') }}" class="btn btn-primary mb-3">{{ __('Create New Mail Action') }}</a>
    <table class="table">
        <thead>
            <tr>
                <th>{{ __('Name') }}</th>
                <th>{{ __('Description') }}</th>
                <th>{{ __('Actions') }}</th>
            </tr>
        </thead>
        <tbody>
            @foreach($mailActions as $mailAction)
                <tr>
                    <td>{{ $mailAction->name }}</td>
                    <td>{{ $mailAction->description }}</td>
                    <td>
                        <a href="{{ route('mail-action.edit', $mailAction->id) }}" class="btn btn-sm btn-primary">{{ __('Edit') }}</a>
                        <form action="{{ route('mail-action.destroy', $mailAction->id) }}" method="POST" style="display: inline-block;">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('{{ __('Are you sure you want to delete this mail action?') }}')">{{ __('Delete') }}</button>
                        </form>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endsection
