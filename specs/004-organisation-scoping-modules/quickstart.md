# Quickstart: Organisation Scoping Implementation

**Feature**: 004-organisation-scoping-modules
**Date**: 2026-02-24

## Prerequisites
- Laravel 12.x, PHP 8.2+
- MySQL 8.0+ with existing `organisations` table
- Feature branch: `004-organisation-scoping-modules`

## Implementation Order

### Step 1: Create Shared Traits (Foundation)

```bash
# Create the two traits
touch app/Traits/BelongsToOrganisation.php
touch app/Traits/HasDualOrganisation.php
```

**`BelongsToOrganisation`** — Single-owner models:
- Provides `scopeByOrganisation($query, $orgId)`
- Provides `organisation()` belongsTo relationship
- Auto-assigns `organisation_id` on model creation via `creating` event

**`HasDualOrganisation`** — Dual-org models (emitter/beneficiary):
- Provides `scopeForOrganisation($query, $orgId)` — WHERE emitter = X OR beneficiary = X
- Model must define `$emitterOrgField` and `$beneficiaryOrgField` properties

### Step 2: Workflow Migration

```bash
php artisan make:migration add_organisation_id_to_workflow_tables
php artisan make:migration add_organisation_indexes_to_modules
```

### Step 3: Apply Traits to Models (incremental)

Apply one module at a time, run tests after each:

1. **Workplace** — Replace inline `scopeByOrganisation` with trait (refactor, no behavior change)
2. **RecordDigitalFolder** — Replace inline scope with trait
3. **Record, RecordPhysical, RecordDigitalDocument** — Add trait
4. **WorkflowDefinition, WorkflowInstance** — Add trait (after migration)
5. **Mail** — Add `HasDualOrganisation` trait
6. **Communication** — Add `HasDualOrganisation` trait
7. **Slip** — Add `HasDualOrganisation` trait

### Step 4: Wire Controllers (per module)

For each controller:
1. Add org filter to `index()` query
2. Add `$this->authorize()` calls to `show/edit/update/destroy`
3. Verify `store()` uses `Auth::user()->current_organisation_id`

### Step 5: Create WorkflowDefinitionPolicy

```bash
php artisan make:policy WorkflowDefinitionPolicy --model=WorkflowDefinition
```

### Step 6: Fix Bugs

- `SlipController::store()` — change `organisation_id` → `current_organisation_id`
- `WorkplacePolicy::view()` — change `$user->organisation_id` → `$user->current_organisation_id`

### Step 7: Run Tests

```bash
php artisan test --filter=Workplace
php artisan test --filter=Record
php artisan test --filter=Mail
php artisan test --filter=Communication
php artisan test --filter=Slip
php artisan test --filter=Workflow
```

## Verification Checklist

- [X] All `index()` methods filter by org (except superadmin)
- [X] All `store()` methods assign `current_organisation_id`
- [X] All `show/edit/update/destroy` pass through policy authorization
- [X] Workflow tables have `organisation_id` column
- [X] SuperAdmin can see all data
- [X] No existing tests broken (pre-existing: 138 fail → after changes: 126 fail, 0 regressions)
