@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h4 class="mb-0">Nouvel Agent</h4>
                </div>
                <div class="card-body">
                    <form action="{{ route('agents.store') }}" method="POST">
                        @csrf
                        @include('agents.partials.form')
                        <div class="d-flex justify-content-between">
                            <a href="{{ route('agents.index') }}" class="btn btn-secondary">
                                <i class="bi bi-arrow-left"></i> Retour
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-save"></i> Créer
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
