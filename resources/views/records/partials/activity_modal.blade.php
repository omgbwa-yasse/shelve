{{-- partials/activity_modal.blade.php --}}
<div class="modal fade" id="activityModal" tabindex="-1" aria-labelledby="activityModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="activityModalLabel">{{ __('select_activity') }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <input type="text" id="activity-search" class="form-control mb-3" placeholder="{{ __('search_activity') }}">
                <div id="activity-list" class="list-group">
                    @foreach ($activities as $activity)
                        <a href="#" class="list-group-item list-group-item-action" data-id="{{ $activity->id }}">
                            {{ $activity->code }} - {{ $activity->name }}
                        </a>
                    @endforeach
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ __('close') }}</button>
                <button type="button" class="btn btn-primary" id="save-activity">{{ __('save') }}</button>
            </div>
        </div>
    </div>
</div>
