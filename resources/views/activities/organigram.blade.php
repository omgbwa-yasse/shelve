<!-- resources/views/activities/index.blade.php -->

@extends('layouts.app')

@section('content')
    <div class="container-fluid py-4">
        <div class="row mb-4 align-items-center">
            <div class="col">
                <h1 class="h2 mb-0"><b>Plan de classement</b></h1>
            </div>
            <div class="col-auto">
                <a href="{{ route('activities.create') }}" class="btn btn-primary">
                    <i class="bi bi-plus-circle me-2"></i>Ajouter une activité
                </a>
            </div>
        </div>
        <div class="card shadow-sm">
            <div class="card-body">
                <div class="org-tree">
                    @foreach($activities->whereNull('parent_id') as $rootActivity)
                        @include('activities.partials.org-tree-item', ['activity' => $rootActivity])
                    @endforeach
                </div>
            </div>
        </div>
    </div>

    {{--    @include('activities.partials.org-tree-styles')--}}
@endsection
<!-- resources/views/activities/partials/org-tree-styles.blade.php -->

<style>
    .org-tree {
        display: flex;
        flex-direction: column;
        align-items: flex-start;
    }
    .org-tree-item {
        margin-bottom: 20px;
    }
    .org-node {
        border: 1px solid #ccc;
        padding: 10px;
        border-radius: 5px;
        background-color: #f8f8f8;
        margin-bottom: 10px;
    }
    .org-children {
        margin-left: 40px;
        position: relative;
        display: none;
    }
    .org-children::before {
        content: '';
        position: absolute;
        top: 0;
        left: -20px;
        border-left: 1px solid #ccc;
        height: 100%;
    }
    .org-children .org-tree-item::before {
        content: '';
        position: absolute;
        top: 20px;
        left: -20px;
        border-top: 1px solid #ccc;
        width: 20px;
    }
    .badge {
        font-size: 0.8em;
        margin-right: 5px;
    }
    .toggle-children {
        margin-left: 10px;
    }
</style>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        document.querySelectorAll('.toggle-children').forEach(button => {
            button.addEventListener('click', function() {
                const targetId = this.getAttribute('data-target');
                const targetElement = document.getElementById(targetId);
                if (targetElement.style.display === 'none' || targetElement.style.display === '') {
                    targetElement.style.display = 'block';
                    this.innerHTML = '<i class="bi bi-chevron-up"></i>';
                } else {
                    targetElement.style.display = 'none';
                    this.innerHTML = '<i class="bi bi-chevron-down"></i>';
                }
            });
        });
    });
</script>
