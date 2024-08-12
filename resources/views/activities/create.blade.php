@extends('layouts.app')

@section('content')
    <div class="container ">

            <div class="">
                <h1 class="mb-0">Create Activity</h1>
            </div>
            <div class="card-body">
                <form action="{{ route('activities.store') }}" method="POST">
                    @csrf
                    <div class="form-group mb-4">
                        <label for="code" class="form-label"><i class="bi bi-barcode"></i> Code</label>
                        <input type="text" name="code" id="code" class="form-control form-control-lg" required>
                    </div>
                    <div class="form-group mb-4">
                        <label for="name" class="form-label"><i class="bi bi-tag"></i> Name</label>
                        <input type="text" name="name" id="name" class="form-control form-control-lg" required>
                    </div>
                    <div class="form-group mb-4">
                        <label for="observation" class="form-label"><i class="bi bi-chat-left-text"></i> Observation</label>
                        <textarea name="observation" id="observation" class="form-control form-control-lg"></textarea>
                    </div>
                    <div class="form-group mb-4">
                        <label for="parent_id" class="form-label"><i class="bi bi-diagram-3"></i> Parent ID</label>
                        <select name="parent_id" id="parent_id" class="form-control form-control-lg">
                            <option value="">None</option>
                            @foreach ($parents as $parent)
                                <option value="{{ $parent->id }}">{{ $parent->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <button type="submit" class="btn btn-primary btn-lg btn-block">Create</button>
                </form>
            </div>
        </div>

@endsection
