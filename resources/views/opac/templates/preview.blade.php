@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-12">
            <div class="card mb-3">
                <div class="card-header d-flex justify-content-between align-items-center bg-dark text-white">
                    <h5 class="mb-0">
                        <i class="bi bi-display"></i> {{ __('Template Preview') }}: {{ $template->name }}
                    </h5>
                    <a href="{{ route('public.opac-templates.show', $template) }}" class="btn btn-sm btn-light">
                        <i class="bi bi-x-circle"></i> {{ __('Close Preview') }}
                    </a>
                </div>
            </div>

            <div class="card">
                <div class="card-body p-0">
                    @if($template->custom_css)
                        <style>
                            {!! $template->custom_css !!}
                        </style>
                    @endif

                    <div class="template-preview-content">
                        {!! $renderedContent !!}
                    </div>

                    @if($template->custom_js)
                        <script>
                            {!! $template->custom_js !!}
                        </script>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.template-preview-content {
    min-height: 500px;
    padding: 20px;
}
</style>
@endsection
