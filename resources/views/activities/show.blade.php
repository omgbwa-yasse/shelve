@extends('layouts.app')

@section('content')
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-9">
                <ul class="nav nav-tabs" id="activityTabs" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active" id="retention-tab" data-bs-toggle="tab" data-bs-target="#retention" type="button" role="tab" aria-controls="retention" aria-selected="true">Retention Rules</button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="communicability-tab" data-bs-toggle="tab" data-bs-target="#communicability" type="button" role="tab" aria-controls="communicability" aria-selected="false">Office Retention</button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="inherited-tab" data-bs-toggle="tab" data-bs-target="#inherited" type="button" role="tab" aria-controls="inherited" aria-selected="false">Inherited Retention</button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="children-tab" data-bs-toggle="tab" data-bs-target="#children" type="button" role="tab" aria-controls="children" aria-selected="false">Child Activities</button>
                    </li>
                </ul>

                <div class="tab-content mt-3" id="activityTabContent">
                    <div class="tab-pane fade show active" id="retention" role="tabpanel" aria-labelledby="retention-tab">
                        <h3>Retention Rules</h3>
                        <table class="table table-striped">
                            <thead>
                            <tr>
                                <th>Code</th>
                                <th>Duration</th>
                                <th>Description</th>
                                <th>Actions</th>
                            </tr>
                            </thead>
                            <tbody>
                            @forelse ($activity->retentions as $retention)
                                <tr>
                                    <td>{{ $retention->code }}</td>
                                    <td>{{ $retention->duration }} years</td>
                                    <td>{{ $retention->description ?? 'No description' }}</td>
                                    <td>
                                        <a href="{{ route('activities.retentions.edit', [$activity->id, $retention->id]) }}" class="btn btn-primary btn-sm">Edit</a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="text-center">No retention rules found.</td>
                                </tr>
                            @endforelse
                            </tbody>
                        </table>
                    </div>

                    <div class="tab-pane fade" id="communicability" role="tabpanel" aria-labelledby="communicability-tab">
                        <h3>Office Retention</h3>
                        @if ($activity->communicability)
                            <table class="table table-striped">
                                <thead>
                                <tr>
                                    <th>Code</th>
                                    <th>Duration</th>
                                    <th>Actions</th>
                                </tr>
                                </thead>
                                <tbody>
                                <tr>
                                    <td>{{ $activity->communicability->code }}</td>
                                    <td>{{ $activity->communicability->duration }} years</td>
                                    <td>
                                        <a href="{{ route('activities.communicabilities.edit', [$activity->id, $activity->communicability->id]) }}" class="btn btn-primary btn-sm">Edit</a>
                                    </td>
                                </tr>
                                </tbody>
                            </table>
                        @else
                            <p>No office retention period set.</p>
                        @endif
                    </div>

                    <div class="tab-pane fade" id="inherited" role="tabpanel" aria-labelledby="inherited-tab">
                        <h3>Inherited Retention Rules</h3>
                        @php
                            $currentActivity = $activity->parent;
                            $level = 1;
                        @endphp
                        @while ($currentActivity)
                            <h4>Inherited from parent (n+{{ $level }}): {{ $currentActivity->code }} - {{ $currentActivity->name }}</h4>
                            <table class="table table-striped">
                                <thead>
                                <tr>
                                    <th>Code</th>
                                    <th>Duration</th>
                                    <th>Description</th>
                                </tr>
                                </thead>
                                <tbody>
                                @forelse ($currentActivity->retentions as $retention)
                                    <tr>
                                        <td>{{ $retention->code }}</td>
                                        <td>{{ $retention->duration }} years</td>
                                        <td>{{ $retention->description ?? 'No description' }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="3" class="text-center">No inherited retention rules found.</td>
                                    </tr>
                                @endforelse
                                </tbody>
                            </table>
                            @php
                                $currentActivity = $currentActivity->parent;
                                $level++;
                            @endphp
                        @endwhile
                    </div>

                    <div class="tab-pane fade" id="children" role="tabpanel" aria-labelledby="children-tab">
                        <h3>Child Activities</h3>
                        <table class="table table-striped">
                            <thead>
                            <tr>
                                <th>Code</th>
                                <th>Name</th>
                                <th>Actions</th>
                            </tr>
                            </thead>
                            <tbody>
                            @forelse ($activity->children as $child)
                                <tr>
                                    <td>{{ $child->code }}</td>
                                    <td>{{ $child->name }}</td>
                                    <td>
                                        <a href="{{ route('activities.show', $child->id) }}" class="btn btn-info btn-sm">View</a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="3" class="text-center">No child activities found.</td>
                                </tr>
                            @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card mb-4">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0">Activity Details</h5>
                    </div>
                    <div class="card-body">
                        <h3 class="card-title">{{ $activity->name }}</h3>
                        <p class="card-text"><strong>Code:</strong> {{ $activity->code }}</p>
                        <p class="card-text"><strong>Observation:</strong> {{ $activity->observation ?: 'N/A' }}</p>
                        @if ($activity->parent_id)
                            <p class="card-text">
                                <strong>Parent Activity:</strong>
                                <a href="{{ route('activities.show', $activity->parent_id) }}">
                                    {{ $activity->parent->code }} - {{ $activity->parent->name }}
                                </a>
                            </p>
                        @endif
                    </div>
                </div>

                <div class="card mb-4">
                    <div class="card-header bg-info text-white">
                        <h5 class="mb-0">Quick Actions</h5>
                    </div>
                    <div class="card-body">
                        <div class="d-grid gap-2">
                            <a href="{{ route('activities.edit', $activity->id) }}" class="btn btn-warning btn-sm">Edit Activity</a>
                            <a href="{{ route('activities.retentions.create', $activity) }}" class="btn btn-secondary btn-sm">Add Retention Rule</a>
                            <a href="{{ route('activities.communicabilities.create', $activity) }}" class="btn btn-info btn-sm">Add Transfer Delay</a>
                            <button class="btn btn-danger btn-sm" data-bs-toggle="modal" data-bs-target="#deleteModal">Delete Activity</button>
                        </div>
                    </div>
                </div>
            </div>


        </div>
    </div>

    <!-- Delete Confirmation Modal -->
    <div class="modal fade" id="deleteModal" tabindex="-1" aria-labelledby="deleteModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="deleteModalLabel">Confirm Deletion</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    Are you sure you want to delete this activity?
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <form action="{{ route('activities.destroy', $activity->id) }}" method="POST">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger">Delete</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            var tabs = new bootstrap.Tab(document.querySelector('#activityTabs button[data-bs-toggle="tab"]'));
            tabs.show();

            // Add any additional JavaScript functionality here
        });
    </script>
@endpush
