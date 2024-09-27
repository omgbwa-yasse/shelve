@extends('layouts.app')

@section('content')
    <div class="container">
        <h1>Détail de enregistrement</h1>
        <div class="col-md-12">
            <h4 class="border-bottom pb-3">
                Versement | <strong> {{ $slip->code?? ''}} :  {{ $slip->name }}</strong>
            </h4>
            <p class="lead"> Description : {{ $slip->description }}</p>
            <a name="" id=""  class="btn btn-primary mb-3" href="{{ route('slips.show', $slip) }}" role="button" >Consulter le bordereau</a>
        </div>
        <div class="card">
            <div class="card-body">
                <div class="row mb-4">


                    <div class="">
                        <p>
                            <strong>Code </strong> : {{ $slipRecord->code }}
                        </p>
                        <p class="card-title">
                            <strong>Intitulé </strong> : {{ $slipRecord->name }}
                        </p>
                        <p class="card-text">
                            <strong>Description </strong> : {{ $slipRecord->content }}
                        </p>
                    </div>

                </div>
                    <div class="d-flex -ml-3 justify-content-between">
                        <p>
                            @if (is_null($slipRecord->date_exact) && is_null($slipRecord->date_end))
                                Date : {{ $slipRecord->date_start }}
                            @elseif (is_null($slipRecord->date_exact) && !is_null($slipRecord->date_end))
                                Dates extrêmes : {{ $slipRecord->date_start }} - {{ $slipRecord->date_end }}
                            @else
                                Date : {{ $slipRecord->date_exact }}
                            @endif
                        </p>
                        <p>
                            <strong>Niveau de description </strong> :{{ $slipRecord->level->name}}
                        </p>
                        <p>
                            <strong>Width:</strong> :{{ $slipRecord->width }} cm ,  {{ $slipRecord->width_description }}</li></li>
                        </p>
                        <p>
                            <strong>support</strong> :{{ $slipRecord->support->name}}
                        </p>
                        <p>
                            <strong>Activité </strong> : {{ $slipRecord->activity->name }}
                        </p>
                        <p>
                            <strong>Boites/chrono :</strong> {{ $slipRecord->container->name }}
                        </p>
                    </div>

                <a href="{{ route('slips.records.index', $slip->id) }}" class="btn btn-secondary mt-3">Back</a>
                <a href="{{ route('slips.records.edit', [$slip, $slipRecord->id]) }}" class="btn btn-warning mt-3">Edit</a>
                <form action="{{ route('slips.records.destroy', [$slip->id, $slipRecord->id]) }}" method="POST" style="display: inline-block;">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger mt-3" onclick="return confirm('Are you sure you want to delete this slip record?')">Delete</button>
                </form>
        </div>
        </div>
    </div>
@endsection
