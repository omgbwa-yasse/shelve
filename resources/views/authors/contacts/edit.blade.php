@extends('layouts.app')

@section('content')
    <h1>Edit Contact</h1>
    <form action="{{ route('author-contacts.update', $authorContact) }}" method="POST">
        @csrf
        @method('PUT')
        <div class="form-group">
            <label for="phone1">Phone 1</label>
            <input type="text" name="phone1" id="phone1" class="form-control" value="{{ $authorContact->phone1 }}">
        </div>
        <div class="form-group">
            <label for="phone2">Phone 2</label>
            <input type="text" name="phone2" id="phone2" class="form-control" value="{{ $authorContact->phone2 }}">
        </div>
        <div class="form-group">
            <label for="email">Email</label>
            <input type="email" name="email" id="email" class="form-control" value="{{ $authorContact->email }}">
        </div>
        <div class="form-group">
            <label for="address">Address</label>
            <input type="text" name="address" id="address" class="form-control" value="{{ $authorContact->address }}">
        </div>
        <div class="form-group">
            <label for="website">Website</label>
            <input type="text" name="website" id="website" class="form-control" value="{{ $authorContact->website }}">
        </div>
        <div class="form-group">
            <label for="fax">Fax</label>
            <input type="text" name="fax" id="fax" class="form-control" value="{{ $authorContact->fax }}">
        </div>
        <div class="form-group">
            <label for="other">Other</label>
            <textarea name="other" id="other" class="form-control">{{ $authorContact->other }}</textarea>
        </div>
        <div class="form-group">
            <label for="po_box">PO Box</label>
            <input type="text" name="po_box" id="po_box" class="form-control" value="{{ $authorContact->po_box }}">
        </div>
        <button type="submit" class="btn btn-primary">Save</button>
    </form>
@endsection
