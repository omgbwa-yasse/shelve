# Tasks: Organisation Scoping Across All Modules

**Input**: Design documents from `/specs/004-organisation-scoping-modules/`
**Prerequisites**: plan.md (required), spec.md (required for user stories), research.md, data-model.md, contracts/

**Tests**: Test tasks are included in the Polish phase as a cross-module integration test, per plan.md Phase 7.

**Organization**: Tasks are grouped by module (mapped to user stories) to enable independent implementation and testing of each module's scoping.

## Format: `[ID] [P?] [Story] Description`

- **[P]**: Can run in parallel (different files, no dependencies)
- **[Story]**: Which user story this task belongs to (US1=Workflow, US2=Communications, US3=Slips, US4=Records, US5=Mails, US6=WorkPlaces)
- Include exact file paths in descriptions

## User Story Mapping

| Story | Module | Priority | Severity |
|-------|--------|----------|----------|
| US1 | Workflow | P1 | CRITICAL — Zero scoping, no org column, no policy |
| US2 | Communications | P2 | CRITICAL — No read scoping, policy unused |
| US3 | Transfers/Slips | P3 | CRITICAL — Bug + no read scoping |
| US4 | Records | P4 | HIGH — Write scoped, read not |
| US5 | Mails | P5 | MEDIUM — Mostly scoped, search gap |
| US6 | WorkPlaces | P6 | LOW — Bug fix + trait standardization |

---

## Phase 1: Setup

**Purpose**: Verify feature branch and codebase readiness

- [X] T001 Verify `004-organisation-scoping-modules` branch is active and up-to-date with main

---

## Phase 2: Foundational (Traits + Migrations)

**Purpose**: Create the reusable traits and database schema changes that ALL user stories depend on

**⚠️ CRITICAL**: No user story work can begin until this phase is complete

- [X] T002 Create `BelongsToOrganisation` trait with `scopeByOrganisation()`, `organisation()` relationship, and auto-assign boot in app/Traits/BelongsToOrganisation.php
- [X] T003 [P] Create `HasDualOrganisation` trait with configurable `$emitterOrgField`/`$beneficiaryOrgField`, `scopeForOrganisation()` (OR clause), and dual relationships in app/Traits/HasDualOrganisation.php
- [X] T004 [P] Create migration to add `organisation_id` (BIGINT UNSIGNED NOT NULL, FK to organisations) to `workflow_definitions` and `workflow_instances` tables, with data backfill from `created_by` user's org in database/migrations/
- [X] T005 [P] Create migration to add indexes on `operator_organisation_id`/`user_organisation_id` (communications), `officer_organisation_id`/`user_organisation_id` (slips), `sender_organisation_id`/`recipient_organisation_id` (mails) in database/migrations/
- [X] T006 Run migrations and verify schema changes applied correctly

**Checkpoint**: Foundation ready — traits exist, workflow tables have `organisation_id`, all org columns indexed. User story implementation can now begin.

---

## Phase 3: User Story 1 — Workflow Module (Priority: P1 — CRITICAL) 🎯 MVP

**Goal**: Add complete organisation scoping to the Workflow module which currently has ZERO scoping — no `organisation_id` column (now added in T004), no policy, no filtering.

**Independent Test**: Create a workflow definition as Org A user → switch to Org B → verify the definition is NOT visible in index and returns 403 on direct access.

### Implementation for User Story 1

- [X] T007 [P] [US1] Add `BelongsToOrganisation` trait and add `organisation_id` to `$fillable` array in app/Models/WorkflowDefinition.php
- [X] T008 [P] [US1] Add `BelongsToOrganisation` trait and add `organisation_id` to `$fillable` array in app/Models/WorkflowInstance.php
- [X] T009 [P] [US1] Create `WorkflowDefinitionPolicy` extending `BasePolicy` with `viewAny`, `view`, `create`, `update`, `delete` methods using `checkOrganisationAccess()` in app/Policies/WorkflowDefinitionPolicy.php
- [X] T010 [US1] Add org filter (`byOrganisation()` with SuperAdmin bypass) to `index()`, add `$this->authorize()` to `show/store/update/destroy`, auto-assign `organisation_id` in `store()` in app/Http/Controllers/WorkflowDefinitionController.php
- [X] T011 [US1] Add org filter (`byOrganisation()` with SuperAdmin bypass) to `index()`, add `$this->authorize()` to `show/store/update/destroy`, auto-assign `organisation_id` in `store()` in app/Http/Controllers/WorkflowInstanceController.php

**Checkpoint**: Workflow module is fully scoped. Definitions and instances are filtered by org. SuperAdmin can see all. Policy authorization blocks cross-org access.

---

## Phase 4: User Story 2 — Communications Module (Priority: P2 — CRITICAL)

**Goal**: Add organisation scoping to Communications. Currently `index()` returns ALL communications with no org filter, and `CommunicationPolicy` exists but is not wired in the controller.

**Independent Test**: Create a communication as Org A operator → switch to Org B → verify the communication is NOT visible in index and returns 403 on show.

### Implementation for User Story 2

- [X] T012 [US2] Add `HasDualOrganisation` trait with `$emitterOrgField = 'operator_organisation_id'` and `$beneficiaryOrgField = 'user_organisation_id'` in app/Models/Communication.php
- [X] T013 [US2] Add org filter (`forOrganisation()` with SuperAdmin bypass) to `index()` query in app/Http/Controllers/CommunicationController.php
- [X] T014 [US2] Wire `CommunicationPolicy` via `$this->authorize()` in `show`, `edit`, `update`, `destroy` methods in app/Http/Controllers/CommunicationController.php

**Checkpoint**: Communications module is scoped. Index shows only communications where user's org is operator OR user. Policy blocks cross-org direct access.

---

## Phase 5: User Story 3 — Transfers/Slips Module (Priority: P3 — CRITICAL)

**Goal**: Fix the `organisation_id` vs `current_organisation_id` bug in `store()`, add org filtering to index/sort, and wire the existing `SlipPolicy`.

**Independent Test**: Create a slip as Org A → verify `officer_organisation_id` equals `current_organisation_id` (not legacy `organisation_id`) → switch to Org B → verify slip not visible.

### Implementation for User Story 3

- [X] T015 [US3] Add `HasDualOrganisation` trait with `$emitterOrgField = 'officer_organisation_id'` and `$beneficiaryOrgField = 'user_organisation_id'` in app/Models/Slip.php
- [X] T016 [US3] Fix `store()` bug: change `Auth::user()->organisation_id` to `Auth::user()->current_organisation_id` for officer org assignment in app/Http/Controllers/SlipController.php
- [X] T017 [US3] Add org filter (`forOrganisation()` with SuperAdmin bypass) to `index()` and `sort()` methods in app/Http/Controllers/SlipController.php
- [X] T018 [US3] Wire `SlipPolicy` via `$this->authorize()` in `show`, `edit`, `update`, `destroy` methods in app/Http/Controllers/SlipController.php

**Checkpoint**: Slips module is scoped. Bug fixed. Index and sort filter by org. Policy blocks cross-org access.

---

## Phase 6: User Story 4 — Records Module (Priority: P4 — HIGH)

**Goal**: Add org filtering to the Records index query (currently shows all records) and standardize all record models to use the `BelongsToOrganisation` trait. Records already have `organisation_id` set correctly on creation.

**Independent Test**: Create a record in Org A → switch to Org B → verify record not visible in physical, folder, and document listings.

### Implementation for User Story 4

- [X] T019 [P] [US4] Add `BelongsToOrganisation` trait to Record model in app/Models/Record.php
- [X] T020 [P] [US4] Add `BelongsToOrganisation` trait to RecordPhysical model in app/Models/RecordPhysical.php
- [X] T021 [P] [US4] Add `BelongsToOrganisation` trait to RecordDigitalDocument model in app/Models/RecordDigitalDocument.php
- [X] T022 [P] [US4] Replace inline `scopeByOrganisation` method with `BelongsToOrganisation` trait in app/Models/RecordDigitalFolder.php
- [X] T023 [US4] Add org filter (`byOrganisation()` with SuperAdmin bypass) to `index()` for physical records, digital folders, and digital documents queries in app/Http/Controllers/RecordController.php
- [X] T024 [US4] Add `$this->authorize('view', $record)` to `show()` method for model-level policy check in app/Http/Controllers/RecordController.php

**Checkpoint**: Records module fully scoped. All 4 record models use the standardized trait. Index queries filter by org. Show has policy authorization.

---

## Phase 7: User Story 5 — Mails Module (Priority: P5 — MEDIUM)

**Goal**: Fix the search scoping gap in `SearchMailController::advanced()`, standardize Mail model with `HasDualOrganisation` trait, and wire `MailPolicy` to replace the custom `canAccessMail()` method.

**Independent Test**: Perform an advanced mail search as Org A → verify results only include mails where Org A is sender or recipient. Access a mail from Org B directly → verify 403.

### Implementation for User Story 5

- [X] T025 [US5] Add `HasDualOrganisation` trait with `$emitterOrgField = 'sender_organisation_id'` and `$beneficiaryOrgField = 'recipient_organisation_id'` in app/Models/Mail.php
- [X] T026 [US5] Add org filter (`forOrganisation()` with SuperAdmin bypass) to `advanced()` search results in app/Http/Controllers/SearchMailController.php
- [X] T027 [US5] Wire `MailPolicy` via `$this->authorize()` in `show`, `edit`, `update`, `destroy` and replace custom `canAccessMail()` with policy check in app/Http/Controllers/MailController.php

**Checkpoint**: Mails module fully scoped. Advanced search filtered. Policy replaces custom auth method.

---

## Phase 8: User Story 6 — WorkPlaces Module (Priority: P6 — LOW)

**Goal**: Fix the field name bug in `WorkplacePolicy` and standardize the Workplace model to use the shared `BelongsToOrganisation` trait instead of its inline scope.

**Independent Test**: Access a workplace from Org B as Org A user → verify 403 uses `current_organisation_id` (not legacy `organisation_id`).

### Implementation for User Story 6

- [X] T028 [P] [US6] Fix `WorkplacePolicy::view()` — change `$user->organisation_id` to `$user->current_organisation_id` in app/Policies/WorkplacePolicy.php
- [X] T029 [P] [US6] Replace inline `scopeByOrganisation` method with `BelongsToOrganisation` trait (keeping same behavior) in app/Models/Workplace.php

**Checkpoint**: WorkPlaces module policy bug fixed. Model uses shared trait for consistency.

---

## Phase 9: Polish & Cross-Cutting Concerns

**Purpose**: Validate all modules work together with no regressions

- [X] T030 Create `OrganisationScopingTest` with multi-org user testing all 7 modules: index filtering, show authorization, store auto-assignment, SuperAdmin bypass in tests/Feature/OrganisationScopingTest.php
- [X] T031 Run full existing test suite (`php artisan test`) — verify no regressions across all modules
- [X] T032 Run quickstart.md verification checklist — confirm all 6 items pass (index filter, store assign, show/edit/update/delete auth, workflow columns, SuperAdmin bypass, no broken tests)

---

## Dependencies & Execution Order

### Phase Dependencies

- **Setup (Phase 1)**: No dependencies — can start immediately
- **Foundational (Phase 2)**: Depends on Phase 1 — **BLOCKS all user stories**
- **US1 Workflow (Phase 3)**: Depends on Phase 2 (needs traits + workflow migration)
- **US2 Communications (Phase 4)**: Depends on Phase 2 (needs `HasDualOrganisation` trait)
- **US3 Slips (Phase 5)**: Depends on Phase 2 (needs `HasDualOrganisation` trait)
- **US4 Records (Phase 6)**: Depends on Phase 2 (needs `BelongsToOrganisation` trait)
- **US5 Mails (Phase 7)**: Depends on Phase 2 (needs `HasDualOrganisation` trait)
- **US6 WorkPlaces (Phase 8)**: Depends on Phase 2 (needs `BelongsToOrganisation` trait)
- **Polish (Phase 9)**: Depends on ALL user stories being complete

### User Story Independence

- **US1 (Workflow)**: Independent — can start after Phase 2
- **US2 (Communications)**: Independent — can start after Phase 2, no dependency on US1
- **US3 (Slips)**: Independent — can start after Phase 2, no dependency on US1/US2
- **US4 (Records)**: Independent — can start after Phase 2, no dependency on US1-US3
- **US5 (Mails)**: Independent — can start after Phase 2, no dependency on US1-US4
- **US6 (WorkPlaces)**: Independent — can start after Phase 2, no dependency on US1-US5

> **All 6 user stories can run in parallel after Phase 2 completes.**

### Within Each User Story

1. Model changes first (add traits)
2. Policy creation/wiring second (if applicable)
3. Controller changes last (depends on model + policy)

---

## Parallel Execution Examples

### Phase 2 — Foundational (3 parallel tracks)

```
Track A: T002 (BelongsToOrganisation trait)
Track B: T003 (HasDualOrganisation trait) + T004 (workflow migration) + T005 (indexes migration)
         ↓
         T006 (run migrations — depends on T004, T005)
```

### Phase 3 — Workflow (2 parallel tracks then sequential)

```
Track A: T007 (WorkflowDefinition model)  ──→ T010 (WorkflowDefinitionController)
Track B: T008 (WorkflowInstance model)     ──→ T011 (WorkflowInstanceController)
Track C: T009 (WorkflowDefinitionPolicy)  ──→ feeds into T010 + T011
```

### Phase 6 — Records (parallel models then sequential controller)

```
Parallel: T019 (Record) + T020 (RecordPhysical) + T021 (RecordDigitalDocument) + T022 (RecordDigitalFolder)
          ↓
Sequential: T023 (RecordController index) → T024 (RecordController show)
```

### All User Stories — After Phase 2

```
Phase 2 complete
    ├──→ Phase 3 (US1 Workflow)      ──→ ┐
    ├──→ Phase 4 (US2 Communications) ──→ │
    ├──→ Phase 5 (US3 Slips)         ──→ ├──→ Phase 9 (Polish)
    ├──→ Phase 6 (US4 Records)       ──→ │
    ├──→ Phase 7 (US5 Mails)         ──→ │
    └──→ Phase 8 (US6 WorkPlaces)    ──→ ┘
```

---

## Implementation Strategy

### MVP First (US1 Workflow Only)

1. Complete Phase 1: Setup
2. Complete Phase 2: Foundational (traits + migrations)
3. Complete Phase 3: US1 Workflow
4. **STOP and VALIDATE**: Test Workflow scoping independently
5. The most critical module (zero scoping → full scoping) is secured

### Incremental Delivery (Recommended)

1. Setup + Foundational → Foundation ready
2. US1 Workflow (CRITICAL) → Test → **MVP secured**
3. US2 Communications (CRITICAL) → Test → 2 modules secured
4. US3 Slips (CRITICAL — bug fix) → Test → 3 modules secured + bug fixed
5. US4 Records (HIGH) → Test → 4 modules secured
6. US5 Mails (MEDIUM) → Test → 5 modules secured + search gap closed
7. US6 WorkPlaces (LOW) → Test → All modules secured + bug fixed
8. Polish → Full validation → Deploy

### Each story adds security without breaking previous stories.

---

## Notes

- [P] tasks = different files, no dependencies on incomplete tasks
- [Story] label maps task to specific module's user story
- All user stories are independent after Phase 2
- SuperAdmin bypass must be tested in every module's index filter
- The `HasDualOrganisation` trait uses OR logic: show resource if user's org matches EITHER emitter or beneficiary field
- Commit after each phase completion for rollback safety
- Reference pattern: `WorkplaceController` + `Workplace` model (already working correctly)
