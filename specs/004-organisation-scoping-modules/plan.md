# Implementation Plan: Organisation Scoping Across All Modules

**Branch**: `004-organisation-scoping-modules` | **Date**: 2026-02-24 | **Spec**: [spec.md](spec.md)
**Input**: Feature specification from `/specs/004-organisation-scoping-modules/spec.md`

## Summary

Enforce `User->current_organisation_id` data isolation across 7 modules (Mails, Workflow, WorkPlaces, Records, Communications, Transfers/Slips, Deposits). Create two shared traits (`BelongsToOrganisation` + `HasDualOrganisation`) to standardize query scoping. Add missing `organisation_id` migration for the Workflow module. Wire existing but unused policies into controllers. Fix field inconsistency bugs.

**Key insight**: Some modules (Mails, Communications, Slips) have a dual-organisation model where the resource involves an emitter org AND a beneficiary org. These must show the resource if the user's current org matches EITHER side.

## Technical Context

**Language/Version**: PHP 8.2+ / Laravel 12.x  
**Primary Dependencies**: Laravel Eloquent ORM, Spatie Permission, Laravel Gate/Policy system  
**Storage**: MySQL 8.0+ — existing `organisations` table, `user_organisation_role` pivot  
**Testing**: PHPUnit (Feature + Unit tests via `php artisan test`)  
**Target Platform**: Linux server (production) / WAMP (local dev)  
**Project Type**: Web application (Laravel MVC + Vue.js 3 frontend)  
**Performance Goals**: Index queries must remain < 200ms with org filter (indexed columns)  
**Constraints**: Zero data leakage between organisations; SuperAdmin bypass required; backward-compatible with existing data  
**Scale/Scope**: 7 modules, ~12 controllers, ~10 models, 2 new migrations, 2 new traits, 1 new policy

## Constitution Check

*GATE: Must pass before Phase 0 research. Re-check after Phase 1 design.*

| Principle | Pre-Design | Post-Design | Notes |
|-----------|-----------|-------------|-------|
| **I. Modern Laravel Stack** | ✅ PASS | ✅ PASS | Uses standard Laravel patterns: traits, scopes, policies |
| **II. AI-Native Integration** | ✅ N/A | ✅ N/A | No AI components affected |
| **III. API-First Design** | ✅ PASS | ✅ PASS | No new endpoints; behavioral changes documented in `contracts/` |
| **IV. Security & Compliance** | ⚠️ VIOLATION | ✅ REMEDIATED | This feature IS the fix — currently 4 controllers have zero authorization. Plan enforces zero-trust via policies. |
| **V. Quality & Reliability** | ✅ PASS | ✅ PASS | Tests required for each module change. Incremental approach. |

## Project Structure

### Documentation (this feature)

```text
specs/004-organisation-scoping-modules/
├── plan.md              # This file
├── research.md          # Phase 0 — 8 research decisions (R-01 to R-08)
├── data-model.md        # Phase 1 — trait definitions, schema changes, query patterns
├── quickstart.md        # Phase 1 — step-by-step implementation guide
├── contracts/
│   └── api-behavioral-changes.md  # Phase 1 — endpoint behavior changes (no new APIs)
└── tasks.md             # Phase 2 (NOT created by /speckit.plan)
```

### Source Code (repository root)

```text
app/
├── Traits/
│   ├── BelongsToOrganisation.php          # NEW — single-owner org scope trait
│   └── HasDualOrganisation.php            # NEW — dual-org (emitter/beneficiary) trait
├── Models/
│   ├── WorkflowDefinition.php             # MODIFY — add trait + organisation_id to fillable
│   ├── WorkflowInstance.php               # MODIFY — add trait + organisation_id to fillable
│   ├── Record.php                         # MODIFY — add BelongsToOrganisation trait
│   ├── RecordPhysical.php                 # MODIFY — add BelongsToOrganisation trait
│   ├── RecordDigitalFolder.php            # MODIFY — replace inline scope with trait
│   ├── RecordDigitalDocument.php          # MODIFY — add BelongsToOrganisation trait
│   ├── Workplace.php                      # MODIFY — replace inline scope with trait
│   ├── Mail.php                           # MODIFY — add HasDualOrganisation trait
│   ├── Communication.php                  # MODIFY — add HasDualOrganisation trait
│   └── Slip.php                           # MODIFY — add HasDualOrganisation trait
├── Http/Controllers/
│   ├── WorkflowDefinitionController.php   # MODIFY — add org filter + $this->authorize()
│   ├── WorkflowInstanceController.php     # MODIFY — add org filter + $this->authorize()
│   ├── RecordController.php               # MODIFY — add org filter to index()
│   ├── CommunicationController.php        # MODIFY — add org filter + $this->authorize()
│   ├── SlipController.php                 # MODIFY — add org filter + fix bug + $this->authorize()
│   ├── MailController.php                 # MODIFY — wire MailPolicy, replace canAccessMail()
│   ├── SearchMailController.php           # MODIFY — add org filter to advanced()
│   └── WorkplaceController.php            # NO CHANGE (already correct)
├── Policies/
│   ├── WorkflowDefinitionPolicy.php       # NEW — extends BasePolicy
│   └── WorkplacePolicy.php                # MODIFY — fix $user->organisation_id field

database/migrations/
├── YYYY_MM_DD_add_organisation_id_to_workflow_tables.php   # NEW
└── YYYY_MM_DD_add_organisation_indexes_to_modules.php      # NEW

tests/Feature/
└── OrganisationScopingTest.php            # NEW — cross-module scoping tests
```

**Structure Decision**: Standard Laravel MVC layout. Two new traits in `app/Traits/`, one new policy in `app/Policies/`, two new migrations. All other changes are modifications to existing files following the Workplace reference pattern established in the codebase.

## Implementation Phases

### Phase 0: Foundation — Traits + Migrations

**Goal**: Create the reusable foundation all modules will use.

| # | Task | Files | Depends On |
|---|------|-------|-----------|
| 0.1 | Create `BelongsToOrganisation` trait | `app/Traits/BelongsToOrganisation.php` | — |
| 0.2 | Create `HasDualOrganisation` trait | `app/Traits/HasDualOrganisation.php` | — |
| 0.3 | Create migration: add `organisation_id` to `workflow_definitions` + `workflow_instances` | `database/migrations/` | — |
| 0.4 | Create migration: add indexes on all org columns across modules | `database/migrations/` | — |
| 0.5 | Run migrations | — | 0.3, 0.4 |

### Phase 1: Workflow Module (Priority: CRITICAL — Zero Scoping)

**Goal**: The most completely un-scoped module gets full treatment.

| # | Task | Files | Depends On |
|---|------|-------|-----------|
| 1.1 | Add `BelongsToOrganisation` trait + `organisation_id` to `WorkflowDefinition` fillable | `app/Models/WorkflowDefinition.php` | 0.1, 0.5 |
| 1.2 | Add `BelongsToOrganisation` trait + `organisation_id` to `WorkflowInstance` fillable | `app/Models/WorkflowInstance.php` | 0.1, 0.5 |
| 1.3 | Create `WorkflowDefinitionPolicy` extending `BasePolicy` | `app/Policies/WorkflowDefinitionPolicy.php` | — |
| 1.4 | Add org filter + `$this->authorize()` to `WorkflowDefinitionController` | `app/Http/Controllers/WorkflowDefinitionController.php` | 1.1, 1.3 |
| 1.5 | Add org filter + `$this->authorize()` to `WorkflowInstanceController` | `app/Http/Controllers/WorkflowInstanceController.php` | 1.2, 1.3 |

### Phase 2: Communications Module (Priority: CRITICAL — No Read Scoping)

**Goal**: Index returns ALL communications; show has no auth check.

| # | Task | Files | Depends On |
|---|------|-------|-----------|
| 2.1 | Add `HasDualOrganisation` trait to `Communication` model | `app/Models/Communication.php` | 0.2 |
| 2.2 | Add org filter to `CommunicationController::index()` | `app/Http/Controllers/CommunicationController.php` | 2.1 |
| 2.3 | Wire `CommunicationPolicy` in show/edit/update/destroy | `app/Http/Controllers/CommunicationController.php` | 2.1 |

### Phase 3: Transfers/Slips Module (Priority: CRITICAL — Bug + No Read Scoping)

**Goal**: Fix `organisation_id` vs `current_organisation_id` bug. Add read scoping.

| # | Task | Files | Depends On |
|---|------|-------|-----------|
| 3.1 | Add `HasDualOrganisation` trait to `Slip` model | `app/Models/Slip.php` | 0.2 |
| 3.2 | Fix `store()`: `organisation_id` → `current_organisation_id` | `app/Http/Controllers/SlipController.php` | — |
| 3.3 | Add org filter to `index()` and `sort()` | `app/Http/Controllers/SlipController.php` | 3.1 |
| 3.4 | Wire `SlipPolicy` in `SlipController` | `app/Http/Controllers/SlipController.php` | 3.1 |

### Phase 4: Records Module (Priority: HIGH — Write Scoped, Read Not)

**Goal**: Index shows all records; add org filtering + standardize with trait.

| # | Task | Files | Depends On |
|---|------|-------|-----------|
| 4.1 | Add `BelongsToOrganisation` trait to `Record`, `RecordPhysical`, `RecordDigitalDocument` | 3 model files | 0.1 |
| 4.2 | Replace inline `scopeByOrganisation` in `RecordDigitalFolder` with trait | `app/Models/RecordDigitalFolder.php` | 0.1 |
| 4.3 | Add org filter to `RecordController::index()` for all 3 record types | `app/Http/Controllers/RecordController.php` | 4.1, 4.2 |
| 4.4 | Add `$this->authorize('view', $record)` to `RecordController::show()` | `app/Http/Controllers/RecordController.php` | — |

### Phase 5: Mails Module (Priority: MEDIUM — Mostly Scoped)

**Goal**: Wire policy, fix search scoping, standardize.

| # | Task | Files | Depends On |
|---|------|-------|-----------|
| 5.1 | Add `HasDualOrganisation` trait to `Mail` model | `app/Models/Mail.php` | 0.2 |
| 5.2 | Add org filter to `SearchMailController::advanced()` | `app/Http/Controllers/SearchMailController.php` | 5.1 |
| 5.3 | Wire `MailPolicy` in `MailController`, replace `canAccessMail()` | `app/Http/Controllers/MailController.php` | 5.1 |

### Phase 6: WorkPlaces Module (Priority: LOW — Bug Fix Only)

**Goal**: Fix field name bug in policy. Replace inline scope with trait.

| # | Task | Files | Depends On |
|---|------|-------|-----------|
| 6.1 | Fix `WorkplacePolicy::view()` — `$user->organisation_id` → `$user->current_organisation_id` | `app/Policies/WorkplacePolicy.php` | — |
| 6.2 | Replace inline `scopeByOrganisation` in Workplace model with `BelongsToOrganisation` trait | `app/Models/Workplace.php` | 0.1 |

### Phase 7: Testing & Validation

| # | Task | Files | Depends On |
|---|------|-------|-----------|
| 7.1 | Create `OrganisationScopingTest` — test all modules with multi-org user | `tests/Feature/OrganisationScopingTest.php` | 1-6 |
| 7.2 | Run existing test suite — verify no regressions | — | 7.1 |
| 7.3 | Manual testing with multi-org user account | — | 7.2 |

## Risk Mitigation

| Risk | Probability | Impact | Mitigation |
|------|------------|--------|-----------|
| Existing workflow data has NULL `organisation_id` | HIGH | MEDIUM | Migration sets default from `created_by` user's org |
| Performance degradation on index queries | LOW | MEDIUM | Add indexes in Phase 0 migration |
| Breaking existing tests | MEDIUM | HIGH | Run tests after each phase; incremental approach |
| SuperAdmin bypass missed somewhere | LOW | HIGH | Explicit `isSuperAdmin()` check in every `index()` filter |
| Dual-org OR clause causes SQL ambiguity | LOW | MEDIUM | Wrap in `$query->where(function($q) { ... })` for proper grouping |

## Complexity Tracking

No Constitution violations to justify. This plan **reduces** complexity by:
- Replacing 5+ ad-hoc inline scoping patterns with 2 standardized traits
- Wiring 4 existing-but-unused policies into their controllers
- Eliminating the custom `canAccessMail()` in favor of the standard policy pattern
