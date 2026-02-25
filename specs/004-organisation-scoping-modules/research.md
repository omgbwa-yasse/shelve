# Research: Organisation Scoping Across All Modules

**Feature**: 004-organisation-scoping-modules
**Date**: 2026-02-24

## R-01: Scoping Architecture Pattern

### Decision
Use a **shared trait pattern** + **model scope** approach, matching the existing `Workplace` model reference implementation.

### Rationale
- `Workplace` already implements `scopeByOrganisation()` at [app/Models/Workplace.php](app/Models/Workplace.php#L118-L121) and it works in production
- The `BasePolicy` + `access-in-organisation` Gate at [app/Services/PolicyService.php](app/Services/PolicyService.php#L89-L112) already supports `organisation_id` FK checks
- No global scope approach to avoid hidden side-effects (e.g., artisan commands, jobs, migrations)
- Explicit `byOrganisation()` scope in controllers is debuggable and transparent

### Alternatives Considered
| Alternative | Rejected Because |
|-------------|-----------------|
| Global scope (auto-applied WHERE) | Hidden SQL modifications break artisan commands, queued jobs, and test factories. Hard to bypass cleanly. |
| Middleware-based filtering | Cannot intercept Eloquent queries. Would need to modify every route handler anyway. |
| Repository pattern | Over-engineering for the current codebase size. Would require major refactoring. |

---

## R-02: Single vs Dual Organisation Models

### Decision
Two distinct traits:
1. **`BelongsToOrganisation`** — for models with a single `organisation_id` FK (Records, Workflow, Workplaces)
2. **`HasDualOrganisation`** — for models where the current org can be either emitter or beneficiary (Mails, Communications, Slips)

### Rationale
Mails have `sender_organisation_id` and `recipient_organisation_id`. Communications have `operator_organisation_id` and `user_organisation_id`. These cannot be scoped with a single `where('organisation_id', $x)`. They need `where(emitter = $x OR beneficiary = $x)`.

### Field Mapping
| Model | Emitter Field | Beneficiary Field |
|-------|--------------|-------------------|
| `Mail` | `sender_organisation_id` | `recipient_organisation_id` |
| `Communication` | `operator_organisation_id` | `user_organisation_id` |
| `Slip` | `officer_organisation_id` | `user_organisation_id` |

---

## R-03: Workflow Module — Missing Schema

### Decision
Add `organisation_id` BIGINT UNSIGNED column to `workflow_definitions` and `workflow_instances` tables via new migration. Set `NOT NULL` with FK constraint to `organisations.id`.

### Rationale
- These are the ONLY tables among the 7 modules missing an `organisation_id` column entirely
- The November 2025 migration at [database/migrations/2025_11_08_224048_create_workflow_table.php](database/migrations/2025_11_08_224048_create_workflow_table.php#L15-L33) created a clean schema without org scoping
- Workflow instances inherit org from the definition they belong to, but having it on both tables enables direct filtering without JOINs

### Existing Data Migration Strategy
- For existing `workflow_definitions`: Set `organisation_id` to the `current_organisation_id` of the `created_by` user
- For existing `workflow_instances`: Set `organisation_id` to the `organisation_id` of the linked `workflow_definition`
- If the user has no `current_organisation_id`, set to the first organisation from the user's `user_organisation_role` pivot

---

## R-04: Policy vs Gate Authorization

### Decision  
Use **Policy authorization** (`$this->authorize()`) in controllers, delegating to existing `BasePolicy::checkOrganisationAccess()` which calls the `access-in-organisation` Gate.

### Rationale
- `BasePolicy` at [app/Policies/BasePolicy.php](app/Policies/BasePolicy.php#L59-L63) already provides `canView`, `canUpdate`, `canDelete` helpers that combine permission checks AND org access checks
- The `access-in-organisation` Gate at [app/Services/PolicyService.php](app/Services/PolicyService.php#L89-L112) supports both `organisation_id` FK and `organisations()` M2M
- Controllers that already use `Gate::authorize('records_view')` (RecordController) only check permissions, NOT org access — they should be supplemented with model-level policy checks

### Alternatives Considered
| Alternative | Rejected Because |
|-------------|-----------------|
| Direct Gate checks in controllers | Duplicates policy logic. Policies exist for most modules but aren't wired. |
| Middleware-based authorization | Can't verify org ownership of specific model instances. Only works for route-level access. |

---

## R-05: `organisation_id` vs `current_organisation_id` Bug

### Decision
Standardize all code to use `Auth::user()->current_organisation_id` (NOT `Auth::user()->organisation_id`).

### Rationale
- `SlipController::store()` at [app/Http/Controllers/SlipController.php](app/Http/Controllers/SlipController.php#L110) uses `Auth::user()->organisation_id` which may refer to a default/legacy org field, not the actively selected one
- `WorkplacePolicy::view()` at [app/Policies/WorkplacePolicy.php](app/Policies/WorkplacePolicy.php#L30) checks `$user->organisation_id` instead of `$user->current_organisation_id`
- The User model's `organisation()` relationship at [app/Models/User.php](app/Models/User.php#L41-L44) maps to `current_organisation_id`, so `$user->organisation->id` IS correct, but `$user->organisation_id` is AMBIGUOUS and must not be used

---

## R-06: SuperAdmin Bypass

### Decision
SuperAdmin bypass is already handled at two levels — no code changes needed:
1. `BasePolicy::before()` at [app/Policies/BasePolicy.php](app/Policies/BasePolicy.php#L26-L28): Returns `true` for superadmin
2. `access-in-organisation` Gate at [app/Services/PolicyService.php](app/Services/PolicyService.php#L90): Returns `true` for superadmin

For query-level scoping in `index()` methods, add explicit bypass:
```php
if (!Auth::user()->isSuperAdmin()) {
    $query->byOrganisation(Auth::user()->current_organisation_id);
}
```

### Rationale
SuperAdmins must see all data for support/management purposes. The existing `isSuperAdmin()` method at [app/Models/User.php](app/Models/User.php#L131-L136) uses cached role checks.

---

## R-07: SearchMailController Gap

### Decision
Add organisation filtering to `SearchMailController::advanced()` at [app/Http/Controllers/SearchMailController.php](app/Http/Controllers/SearchMailController.php#L32-L145).

### Rationale
The main `MailController::index()` correctly filters by org, but the advanced search at `SearchMailController` returns results across ALL organisations. This is a data leak.

### Implementation
Add dual-org scope: `WHERE (sender_organisation_id = $orgId OR recipient_organisation_id = $orgId)` before returning search results.

---

## R-08: Index Performance

### Decision
Add MySQL indexes on all `organisation_id` columns used for filtering.

### Rationale
Adding `WHERE organisation_id = X` to every index query without an index would cause full table scans. Most of these columns exist but may not be indexed.

### Columns Requiring Indexes
| Table | Column | Exists? |
|-------|--------|---------|
| `workflow_definitions` | `organisation_id` | NEW |
| `workflow_instances` | `organisation_id` | NEW |
| `communications` | `operator_organisation_id` | CHECK |
| `communications` | `user_organisation_id` | CHECK |
| `slips` | `officer_organisation_id` | CHECK |
| `slips` | `user_organisation_id` | CHECK |
| `mails` | `sender_organisation_id` | CHECK |
| `mails` | `recipient_organisation_id` | CHECK |
