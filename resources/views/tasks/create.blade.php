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
            nextBtn.style.display = currentTab === tabs.length - 1 ? 'none' : 'inline-block';
            submitBtn.style.display = currentTab === tabs.length - 1 ? 'inline-block' : 'inline-block';
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

        form.addEventListener('submit', (e) => {
            e.preventDefault();
            if (validateTab(currentTab)) {
                form.submit();
            }
        });

        // Initialiser l'affichage du premier onglet
        showTab(currentTab);

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
                <form action="{{ route('workflows.tasks.store') }}" method="POST" enctype="multipart/form-data" id="taskForm">
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
                                    <label for="name" class="form-label"><i class="bi bi-pencil-square me-2"></i>Name</label>
                                    <input type="text" class="form-control @error('name') is-invalid @enderror" id="name" name="name" required>
                                    @error('name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="duration" class="form-label"><i class="bi bi-clock me-2"></i>Duration (hours)</label>
                                    <input type="number" class="form-control @error('duration') is-invalid @enderror" id="duration" name="duration" required>
                                    @error('duration')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="mb-3">
                                <label for="description" class="form-label"><i class="bi bi-journal-text me-2"></i>Description</label>
                                <textarea class="form-control @error('description') is-invalid @enderror" id="description" name="description" rows="3" required></textarea>
                                @error('description')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="status" class="form-label"><i class="bi bi-exclamation-triangle me-2"></i>Status</label>
                                    <select class="form-select @error('status') is-invalid @enderror" id="status" name="status" required>
                                        @foreach($taskStatuses as $status)
                                            <option value="{{ $status->value }}">{{ $status->label() }}</option>
                                        @endforeach
                                    </select>
                                    @error('status')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="task_type_id" class="form-label"><i class="bi bi-list-task me-2"></i>Task Type</label>
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
                                <label for="start_date" class="form-label"><i class="bi bi-calendar me-2"></i>Start Date</label>
                                <input type="date" class="form-control @error('start_date') is-invalid @enderror" id="start_date" name="start_date" required>
                                @error('start_date')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="mb-3">
                                <label for="parent_task_id" class="form-label"><i class="bi bi-folder me-2"></i>Parent Task</label>
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
                                <label for="user_ids" class="form-label"><i class="bi bi-people me-2"></i>Assigned Users</label>
                                <select class="form-select select2-multiple @error('user_ids') is-invalid @enderror" id="user_ids" name="user_ids[]" multiple required>
                                    <!-- Options will be dynamically populated -->
                                </select>
                                @error('user_ids')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="mb-3">
                                <label for="organisation_ids" class="form-label"><i class="bi bi-building me-2"></i>Organizations</label>
                                <select class="form-select select2-multiple @error('organisation_ids') is-invalid @enderror" id="organisation_ids" name="organisation_ids[]" multiple required>
                                    <!-- Options will be dynamically populated -->
                                </select>
                                @error('organisation_ids')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="mb-3">
                                <label for="attachments" class="form-label"><i class="bi bi-paperclip me-2"></i>Attachments</label>
                                <input type="file" class="form-control @error('attachments') is-invalid @enderror" id="attachments" name="attachments[]" multiple>
                                @error('attachments')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="row">
                                <div class="mb-3">
                                    <label for="mail_ids" class="form-label"><i class="bi bi-envelope me-2"></i>Task Mails</label>
                                    <select class="form-select select2-multiple @error('mail_ids') is-invalid @enderror" id="mail_ids" name="mail_ids[]" multiple>
                                        <!-- Options will be dynamically populated -->
                                    </select>
                                    @error('mail_ids')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="mb-3">
                                    <label for="container_ids" class="form-label"><i class="bi bi-archive me-2"></i>Task Containers</label>
                                    <select class="form-select select2-multiple @error('container_ids') is-invalid @enderror" id="container_ids" name="container_ids[]" multiple>
                                        <!-- Options will be dynamically populated -->
                                    </select>
                                    @error('container_ids')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="mb-3">
                                    <label for="record_ids" class="form-label"><i class="bi bi-file-earmark-text me-2"></i>Task Records</label>
                                    <select class="form-select select2-multiple @error('record_ids') is-invalid @enderror" id="record_ids" name="record_ids[]" multiple>
                                        <!-- Options will be dynamically populated -->
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
                                    <label for="remember_date_fix" class="form-label"><i class="bi bi-calendar-check me-2"></i>Remember Date Fix</label>
                                    <input type="date" class="form-control @error('remember_date_fix') is-invalid @enderror" id="remember_date_fix" name="remember_date_fix">
                                    @error('remember_date_fix')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="remember_periode" class="form-label"><i class="bi bi-clock me-2"></i>Remember Period</label>
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
                                    <label for="remember_date_trigger" class="form-label"><i class="bi bi-calendar-event me-2"></i>Remember Date Trigger</label>
                                    <select class="form-select @error('remember_date_trigger') is-invalid @enderror" id="remember_date_trigger" name="remember_date_trigger">
                                        <option value="start">Start</option>
                                        <option value="end">End</option>
                                    </select>
                                    @error('remember_date_trigger')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="remember_limit_number" class="form-label"><i class="bi bi-hash me-2"></i>Remember Limit Number</label>
                                    <input type="number" class="form-control @error('remember_limit_number') is-invalid @enderror" id="remember_limit_number" name="remember_limit_number">
                                    @error('remember_limit_number')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="remember_limit_date" class="form-label"><i class="bi bi-calendar-date me-2"></i>Remember Limit Date</label>
                                    <input type="date" class="form-control @error('remember_limit_date') is-invalid @enderror" id="remember_limit_date" name="remember_limit_date">
                                    @error('remember_limit_date')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="mb-3">
                                    <label for="remember_user_id" class="form-label"><i class="bi bi-person me-2"></i>Remember User</label>
                                    <select class="form-select @error('remember_user_id') is-invalid @enderror" id="remember_user_id" name="remember_user_id">
                                        <!-- Options will be dynamically populated -->
                                    </select>
                                    @error('remember_user_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="remember_frequence_value" class="form-label"><i class="bi bi-clock-history me-2"></i>Remember Frequency Value</label>
                                    <input type="number" class="form-control @error('remember_frequence_value') is-invalid @enderror" id="remember_frequence_value" name="remember_frequence_value">
                                    @error('remember_frequence_value')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="remember_frequence_unit" class="form-label"><i class="bi bi-clock me-2"></i>Remember Frequency Unit</label>
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
                                <label for="supervision_user_id" class="form-label"><i class="bi bi-person-check me-2"></i>Supervision User</label>
                                <select class="form-select @error('supervision_user_id') is-invalid @enderror" id="supervision_user_id" name="supervision_user_id" required>
                                    <!-- Options will be dynamically populated -->
                                </select>
                                @error('supervision_user_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="mb-3">
                                <label for="task_assignation" class="form-label"><i class="bi bi-person-plus me-2"></i>Task Assignation</label>
                                <select class="form-select @error('task_assignation') is-invalid @enderror" id="task_assignation" name="task_assignation" required>
                                    <option value="1">Yes</option>
                                    <option value="0">No</option>
                                </select>
                                @error('task_assignation')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="mb-3">
                                <label for="task_update" class="form-label"><i class="bi bi-arrow-repeat me-2"></i>Task Update</label>
                                <select class="form-select @error('task_update') is-invalid @enderror" id="task_update" name="task_update" required>
                                    <option value="1">Yes</option>
                                    <option value="0">No</option>
                                </select>
                                @error('task_update')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="mb-3">
                                <label for="task_parent_update" class="form-label"><i class="bi bi-arrow-up me-2"></i>Task Parent Update</label>
                                <select class="form-select @error('task_parent_update') is-invalid @enderror" id="task_parent_update" name="task_parent_update" required>
                                    <option value="1">Yes</option>
                                    <option value="0">No</option>
                                </select>
                                @error('task_parent_update')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="mb-3">
                                <label for="task_child_update" class="form-label"><i class="bi bi-arrow-down me-2"></i>Task Child Update</label>
                                <select class="form-select @error('task_child_update') is-invalid @enderror" id="task_child_update" name="task_child_update" required>
                                    <option value="1">Yes</option>
                                    <option value="0">No</option>
                                </select>
                                @error('task_child_update')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="mb-3">
                                <label for="task_close" class="form-label"><i class="bi bi-x-circle me-2"></i>Task Close</label>
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

    <!-- Multi-Select Modal -->
    <div class="modal fade" id="multiSelectModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Select Items</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <input type="text" class="form-control mb-3" id="modalSearch" placeholder="Search items...">
                    <div id="itemList" class="list-group">
                        <!-- Items will be dynamically populated here -->
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="button" class="btn btn-primary" id="saveSelection">Save selection</button>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const multiSelectModal = new bootstrap.Modal(document.getElementById('multiSelectModal'));
            const modalSearch = document.getElementById('modalSearch');
            const itemList = document.getElementById('itemList');
            const saveSelectionBtn = document.getElementById('saveSelection');

            const multiSelects = [
                { id: 'user_ids', data: @json($users), multiple: true },
                { id: 'organisation_ids', data: @json($organisations), multiple: true },
                { id: 'mail_ids', data: @json($mails), multiple: true },
                { id: 'container_ids', data: @json($containers), multiple: true },
                { id: 'record_ids', data: @json($records), multiple: true },
                { id: 'remember_user_id', data: @json($users), multiple: false },
                { id: 'supervision_user_id', data: @json($users), multiple: false }
            ];

            let currentSelectId = '';
            let isMultiple = true;

            function populateModal(items, multiple) {
                itemList.innerHTML = items.map(item => `
            <label class="list-group-item">
                <input class="form-check-input me-1" type="${multiple ? 'checkbox' : 'radio'}" name="modalSelection" value="${item.id}">
                ${item.name}
            </label>
        `).join('');
            }

            function filterItems() {
                const searchTerm = modalSearch.value.toLowerCase();
                Array.from(itemList.children).forEach(item => {
                    const itemName = item.textContent.trim().toLowerCase();
                    item.style.display = itemName.includes(searchTerm) ? '' : 'none';
                });
            }

            function saveSelection() {
                const selectedItems = Array.from(itemList.querySelectorAll('input:checked')).map(input => ({
                    value: input.value,
                    text: input.parentNode.textContent.trim()
                }));

                const select = document.getElementById(currentSelectId);
                select.innerHTML = '';
                selectedItems.forEach(item => {
                    const option = new Option(item.text, item.value, true, true);
                    select.add(option);
                });

                select.dispatchEvent(new Event('change'));
                multiSelectModal.hide();
            }

            multiSelects.forEach(({ id, data, multiple }) => {
                const select = document.getElementById(id);
                select.addEventListener('click', function(e) {
                    e.preventDefault();
                    currentSelectId = id;
                    isMultiple = multiple;
                    populateModal(data, multiple);
                    multiSelectModal.show();
                });
            });

            modalSearch.addEventListener('input', filterItems);
            saveSelectionBtn.addEventListener('click', saveSelection);

            // Ajout d'un gestionnaire d'événements pour le bouton de fermeture du modal
            document.querySelector('#multiSelectModal .btn-close').addEventListener('click', () => multiSelectModal.hide());
            document.querySelector('#multiSelectModal .modal-footer .btn-secondary').addEventListener('click', () => multiSelectModal.hide());
        });
    </script>
@endsection
