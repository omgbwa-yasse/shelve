@extends('layouts.app')
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const form = document.getElementById('taskForm');
        const tabs = document.querySelectorAll('.nav-link');
        const tabContents = document.querySelectorAll('.tab-pane');
        const prevBtn = document.getElementById('prevBtn');
        const nextBtn = document.getElementById('nextBtn');
        const submitBtn = document.getElementById('submitBtn');
        const helpBtn = document.getElementById('helpBtn');
        const helpModal = new bootstrap.Modal(document.getElementById('helpModal'));

        let currentTab = 0;

        function showTab(index) {
            tabs.forEach(tab => tab.classList.remove('active'));
            tabs[index].classList.add('active');
            tabContents.forEach(content => content.classList.remove('show', 'active'));
            tabContents[index].classList.add('show', 'active');
            currentTab = index;

            prevBtn.style.display = currentTab === 0 ? 'none' : 'inline-block';
            nextBtn.style.display = currentTab === 3 ? 'none' : 'inline-block';
            submitBtn.style.display = currentTab === 3 ? 'inline-block' : 'inline-block';
        }

        function validateTab(index) {
            const inputs = tabContents[index].querySelectorAll('input[required], select[required], textarea[required]');
            let isValid = true;
            inputs.forEach(input => {
                if (!input.value) {
                    isValid = false;
                    input.classList.add('is-invalid');
                } else {
                    input.classList.remove('is-invalid');
                }
            });
            return isValid;
        }

        prevBtn.addEventListener('click', () => {
            if (currentTab > 0) {
                showTab(currentTab - 1);
            }
        });

        nextBtn.addEventListener('click', () => {
            if (validateTab(currentTab) && currentTab < tabs.length - 1) {
                showTab(currentTab + 1);
            }
        });

        helpBtn.addEventListener('click', () => helpModal.show());

        // Ajout d'un gestionnaire d'événements pour le bouton de fermeture du modal
        document.querySelector('#helpModal .btn-close').addEventListener('click', () => helpModal.hide());
        document.querySelector('#helpModal .modal-footer .btn-secondary').addEventListener('click', () => helpModal.hide());

        form.addEventListener('submit', (e) => {
            e.preventDefault();
            if (validateTab(currentTab)) {
                form.submit();
            }
        });

        // Initialiser l'affichage du premier onglet
        showTab(currentTab);
        // Search functionality for select inputs
        const searchInputs = [
            { searchId: 'userSearch', selectId: 'user_ids' },
            { searchId: 'mailSearch', selectId: 'mail_ids' },
            { searchId: 'containerSearch', selectId: 'container_ids' },
            { searchId: 'recordSearch', selectId: 'record_ids' },
            { searchId: 'rememberUserSearch', selectId: 'remember_user_id' },
            { searchId: 'parentTaskSearch', selectId: 'parent_task_id' },
            { searchId: 'supervisionUserSearch', selectId: 'supervision_user_id' },
            { searchId: 'organizationSearch', selectId: 'organisation_ids' }
        ];

        searchInputs.forEach(({ searchId, selectId }) => {
            const searchInput = document.getElementById(searchId);
            const select = document.getElementById(selectId);
            const options = Array.from(select.options);

            searchInput.addEventListener('input', function() {
                const searchTerm = this.value.toLowerCase();
                options.forEach(option => {
                    const isVisible = option.text.toLowerCase().includes(searchTerm);
                    option.style.display = isVisible ? '' : 'none';
                });
            });
        });

        // Initialize Select2 for multiple select inputs
        $('.select2-multiple').select2({
            theme: 'bootstrap-5',
            width: '100%'
        });

        // Initialize Flatpickr for all date inputs
        flatpickr("input[type=date]", {
            dateFormat: "Y-m-d",
            allowInput: true
        });

        tabs.forEach((tab, index) => {
            tab.addEventListener('click', (e) => {
                e.preventDefault();
                showTab(index);
            });
        });
    });
</script>
@section('content')
    <div class="container">
        @if ($errors->any())
            <div class="alert alert-danger">
                <ul>
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif
        <div class="card">
            <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                <h1 class="h3 mb-0">
                    <i class="bi bi-card-text me-2"></i>
                    Create New Task
                </h1>
                <button type="button" class="btn btn-outline-light" id="helpBtn">
                    <i class="bi bi-question-circle"></i> Help
                </button>
            </div>
            <div class="card-body">
                <form action="{{ route('tasks.store') }}" method="POST" enctype="multipart/form-data" id="taskForm">
                    @csrf
                    <ul class="nav nav-tabs mb-3" id="taskTabs" role="tablist">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link active" id="basic-tab" data-bs-toggle="tab" data-bs-target="#basic" type="button" role="tab">Basic Info</button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="details-tab" data-bs-toggle="tab" data-bs-target="#details" type="button" role="tab">Details</button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="reminders-tab" data-bs-toggle="tab" data-bs-target="#reminders" type="button" role="tab">Reminders</button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="supervision-tab" data-bs-toggle="tab" data-bs-target="#supervision" type="button" role="tab">Supervision</button>
                        </li>
                    </ul>
                    <div class="tab-content" id="taskTabsContent">
                        <!-- Basic Info Tab -->
                        <div class="tab-pane fade show active" id="basic" role="tabpanel">
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="name" class="form-label">Name</label>
                                    <input type="text" class="form-control @error('name') is-invalid @enderror" id="name" name="name" required>
                                    @error('name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="duration" class="form-label">Duration (hours)</label>
                                    <input type="number" class="form-control @error('duration') is-invalid @enderror" id="duration" name="duration" required>
                                    @error('duration')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="mb-3">
                                <label for="description" class="form-label">Description</label>
                                <textarea class="form-control @error('description') is-invalid @enderror" id="description" name="description" rows="3" required></textarea>
                                @error('description')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="task_status_id" class="form-label">Status</label>
                                    <select class="form-select @error('task_status_id') is-invalid @enderror" id="task_status_id" name="task_status_id" required>
                                        @foreach($taskStatuses as $status)
                                            <option value="{{ $status->id }}">{{ $status->name }}</option>
                                        @endforeach
                                    </select>
                                    @error('task_status_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="task_type_id" class="form-label">Task Type</label>
                                    <select class="form-select @error('task_type_id') is-invalid @enderror" id="task_type_id" name="task_type_id" required>
                                        @foreach($taskTypes as $type)
                                            <option value="{{ $type->id }}">{{ $type->name }}</option>
                                        @endforeach
                                    </select>
                                    @error('task_type_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="mb-3">
                                <label for="start_date" class="form-label">Start Date</label>
                                <input type="date" class="form-control @error('start_date') is-invalid @enderror" id="start_date" name="start_date" required>
                                @error('start_date')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="mb-3">
                                <label for="parent_task_id" class="form-label">Parent Task</label>
                                <input type="text" class="form-control mb-2" id="parentTaskSearch" placeholder="Search parent tasks...">
                                <select class="form-select @error('parent_task_id') is-invalid @enderror" id="parent_task_id" name="parent_task_id">
                                    <option value="">None</option>
                                    @foreach($tasks as $task)
                                        <option value="{{ $task->id }}">{{ $task->name }}</option>
                                    @endforeach
                                </select>
                                @error('parent_task_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <!-- Details Tab -->
                        <div class="tab-pane fade" id="details" role="tabpanel">
                            <div class="mb-3">
                                <label for="user_ids" class="form-label">Assigned Users</label>
                                <input type="text" class="form-control mb-2" id="userSearch" placeholder="Search users...">
                                <select class="form-select @error('user_ids') is-invalid @enderror" id="user_ids" name="user_ids[]" multiple required>
                                    @foreach($users as $user)
                                        <option value="{{ $user->id }}">{{ $user->name }}</option>
                                    @endforeach
                                </select>
                                @error('user_ids')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="mb-3">
                                <label for="organisation_ids" class="form-label">Organizations</label>
                                <input type="text" class="form-control mb-2" id="organizationSearch" placeholder="Search organizations...">
                                <select class="form-select @error('organisation_ids') is-invalid @enderror" id="organisation_ids" name="organisation_ids[]" multiple required>
                                    @foreach($organisations as $org)
                                        <option value="{{ $org->id }}">{{ $org->name }}</option>
                                    @endforeach
                                </select>
                                @error('organisation_ids')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="mb-3">
                                <label for="attachments" class="form-label">Attachments</label>
                                <input type="file" class="form-control @error('attachments') is-invalid @enderror" id="attachments" name="attachments[]" multiple>
                                @error('attachments')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="row">
                                <div class="col-md-4 mb-3">
                                    <label for="mail_ids" class="form-label">Task Mails</label>
                                    <input type="text" class="form-control mb-2" id="mailSearch" placeholder="Search mails...">
                                    <select class="form-select @error('mail_ids') is-invalid @enderror" id="mail_ids" name="mail_ids[]" multiple>
                                        @foreach($mails as $mail)
                                            <option value="{{ $mail->id }}">{{ $mail->name }}</option>
                                        @endforeach
                                    </select>
                                    @error('mail_ids')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label for="container_ids" class="form-label">Task Containers</label>
                                    <input type="text" class="form-control mb-2" id="containerSearch" placeholder="Search containers...">
                                    <select class="form-select @error('container_ids') is-invalid @enderror" id="container_ids" name="container_ids[]" multiple>
                                        @foreach($containers as $container)
                                            <option value="{{ $container->id }}">{{ $container->name }}</option>
                                        @endforeach
                                    </select>
                                    @error('container_ids')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label for="record_ids" class="form-label">Task Records</label>
                                    <input type="text" class="form-control mb-2" id="recordSearch" placeholder="Search records...">
                                    <select class="form-select @error('record_ids') is-invalid @enderror" id="record_ids" name="record_ids[]" multiple>
                                        @foreach($records as $record)
                                            <option value="{{ $record->id }}">{{ $record->name }}</option>
                                        @endforeach
                                    </select>
                                    @error('record_ids')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <!-- Reminders Tab -->
                        <div class="tab-pane fade" id="reminders" role="tabpanel">
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="remember_date_fix" class="form-label">Remember Date Fix</label>
                                    <input type="date" class="form-control @error('remember_date_fix') is-invalid @enderror" id="remember_date_fix" name="remember_date_fix">
                                    @error('remember_date_fix')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="remember_periode" class="form-label">Remember Period</label>
                                    <select class="form-select @error('remember_periode') is-invalid @enderror" id="remember_periode" name="remember_periode">
                                        <option value="before">Before</option>
                                        <option value="after">After</option>
                                    </select>
                                    @error('remember_periode')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="remember_date_trigger" class="form-label">Remember Date Trigger</label>
                                    <select class="form-select @error('remember_date_trigger') is-invalid @enderror" id="remember_date_trigger" name="remember_date_trigger">
                                        <option value="start">Start</option>
                                        <option value="end">End</option>
                                    </select>
                                    @error('remember_date_trigger')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="remember_limit_number" class="form-label">Remember Limit Number</label>
                                    <input type="number" class="form-control @error('remember_limit_number') is-invalid @enderror" id="remember_limit_number" name="remember_limit_number">
                                    @error('remember_limit_number')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="remember_limit_date" class="form-label">Remember Limit Date</label>
                                    <input type="date" class="form-control @error('remember_limit_date') is-invalid @enderror" id="remember_limit_date" name="remember_limit_date">
                                    @error('remember_limit_date')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="remember_user_id" class="form-label">Remember User</label>
                                    <input type="text" class="form-control mb-2" id="rememberUserSearch" placeholder="Search remember users...">
                                    <select class="form-select @error('remember_user_id') is-invalid @enderror" id="remember_user_id" name="remember_user_id">
                                        @foreach($users as $user)
                                            <option value="{{ $user->id }}">{{ $user->name }}</option>
                                        @endforeach
                                    </select>
                                    @error('remember_user_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="remember_frequence_value" class="form-label">Remember Frequency Value</label>
                                    <input type="number" class="form-control @error('remember_frequence_value') is-invalid @enderror" id="remember_frequence_value" name="remember_frequence_value">
                                    @error('remember_frequence_value')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="remember_frequence_unit" class="form-label">Remember Frequency Unit</label>
                                    <select class="form-select @error('remember_frequence_unit') is-invalid @enderror" id="remember_frequence_unit" name="remember_frequence_unit">
                                        <option value="year">Year</option>
                                        <option value="month">Month</option>
                                        <option value="day">Day</option>
                                        <option value="hour">Hour</option></select>
                                    @error('remember_frequence_unit')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <!-- Supervision Tab -->
                        <div class="tab-pane fade" id="supervision" role="tabpanel">
                            <div class="mb-3">
                                <label for="supervision_user_id" class="form-label">Supervision User</label>
                                <input type="text" class="form-control mb-2" id="supervisionUserSearch" placeholder="Search supervision users...">
                                <select class="form-select @error('supervision_user_id') is-invalid @enderror" id="supervision_user_id" name="supervision_user_id" required>
                                    @foreach($users as $user)
                                        <option value="{{ $user->id }}">{{ $user->name }}</option>
                                    @endforeach
                                </select>
                                @error('supervision_user_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="mb-3">
                                <label for="task_assignation" class="form-label">Task Assignation</label>
                                <select class="form-select @error('task_assignation') is-invalid @enderror" id="task_assignation" name="task_assignation" required>
                                    <option value="1">Yes</option>
                                    <option value="0">No</option>
                                </select>
                                @error('task_assignation')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="mb-3">
                                <label for="task_update" class="form-label">Task Update</label>
                                <select class="form-select @error('task_update') is-invalid @enderror" id="task_update" name="task_update" required>
                                    <option value="1">Yes</option>
                                    <option value="0">No</option>
                                </select>
                                @error('task_update')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="mb-3">
                                <label for="task_parent_update" class="form-label">Task Parent Update</label>
                                <select class="form-select @error('task_parent_update') is-invalid @enderror" id="task_parent_update" name="task_parent_update" required>
                                    <option value="1">Yes</option>
                                    <option value="0">No</option>
                                </select>
                                @error('task_parent_update')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="mb-3">
                                <label for="task_child_update" class="form-label">Task Child Update</label>
                                <select class="form-select @error('task_child_update') is-invalid @enderror" id="task_child_update" name="task_child_update" required>
                                    <option value="1">Yes</option>
                                    <option value="0">No</option>
                                </select>
                                @error('task_child_update')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="mb-3">
                                <label for="task_close" class="form-label">Task Close</label>
                                <select class="form-select @error('task_close') is-invalid @enderror" id="task_close" name="task_close" required>
                                    <option value="1">Yes</option>
                                    <option value="0">No</option>
                                </select>
                                @error('task_close')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>
                    <div class="text-end mt-4">
                        <button type="button" class="btn btn-secondary me-2" id="prevBtn">Previous</button>
                        <button type="button" class="btn btn-primary" id="nextBtn">Next</button>
                        <button type="submit" class="btn btn-success" id="submitBtn">Create Task</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Help Modal -->
    <div class="modal fade" id="helpModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Task Creation Help</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p>Instructions on how to fill out the task form</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

@endsection
