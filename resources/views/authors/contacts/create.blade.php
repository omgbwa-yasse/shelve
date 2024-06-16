@extends('layouts.app')

@section('content')
    <h1>Add Contact for {{ $author->name }}</h1>
    <form action="{{ route('author-contacts.store', $author) }}" method="POST">
        @csrf
        <div class="form-group">
            <label for="phone1">Phone 1</label>
            <input type="text" name="phone1" id="phone1" class="form-control">
        </div>
        <div class="form-group">
            <label for="phone2">Phone 2</label>
            <input type="text" name="phone2" id="phone2" class="form-control">
        </div>
        <div class="form-group">
            <label for="email">Email</label>
            <input type="email" name="email" id="email" class="form-control">
        </div>
        <div class="form-group">
            <label for="address">Address</label>
            <input type="text" name="address" id="address" class="form-control">
        </div>
        <div class="form-group">
            <label for="website">Website</label>
            <input type="text" name="website" id="website" class="form-control">
        </div>
        <div class="form-group">
            <label for="fax">Fax</label>
            <input type="text" name="fax" id="fax" class="form-control">
        </div>
        <div class="form-group">
            <label for="other">Other</label>
            <textarea name="other" id="other" class="form-control"></textarea>
        </div>
        <div class="form-group">
            <label for="po_box">PO Box</label>
            <input type="text" name="po_box" id="po_box" class="form-control">
        </div>
        <input type="hidden" name="author_id" value="{{ $author->id }}">
        <button type="submit" class="btn btn-primary">Save</button>
    </form>
@endsection
