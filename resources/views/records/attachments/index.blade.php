@extends('layouts.app')

@section('content')
    <h1>Attachments for {{ $record->name }}</h1>
    <ul>
        @foreach($attachments as $attachment)
            <li>{{ $attachment->file_path }} - {{ $attachment->name }}
{{--                <form action="{{ route('records.attachments.destroy', [$record, $attachment]) }}" method="POST" style="display:inline;">--}}
{{--                    @csrf--}}
{{--                    @method('DELETE')--}}
{{--                    <button type="submit">Delete</button>--}}
{{--                </form>--}}
            </li>
        @endforeach
    </ul>
    <a href="{{ route('records.attachments.create', $record) }}">Add Attachment</a>
@endsection
