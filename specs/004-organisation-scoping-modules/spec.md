# Specification: Organisation Scoping Across All Modules

**Feature Branch**: `004-organisation-scoping-modules`
**Created**: 2026-02-24
**Status**: Draft
**Input**: Cross-module analysis — enforce `User->current_organisation_id` data isolation

## 1. Overview

Shelve is a multi-tenant archive management platform where users belong to one or more organisations and select a "current" organisation at runtime (`current_organisation_id`). All module data must be scoped to this current organisation so that users see only the resources that belong to, or involve, their active organisation.

The Records module partially implements this pattern. The Workplaces module has the best reference implementation. Other modules (Mails, Workflow, Communications, Transfers/Slips, Deposits) either lack scoping entirely or have gaps.

## 2. User Story

**As a** user belonging to multiple organisations,
**I want** each module to only show data related to my currently selected organisation,
**So that** data from other organisations is never leaked in listings, searches, or detail views.

## 3. Scope — Modules Affected

| Module | Current State | Required Work |
|--------|--------------|---------------|
| **WorkPlaces** | REFERENCE — `scopeByOrganisation` + policy + controller = ✅ | Fix `WorkplacePolicy` using wrong field (`organisation_id` vs `current_organisation_id`) |
| **Mails** | Index scoped ✅, show scoped (custom) ✅, search NOT scoped ❌, no policy authorization | Add org filter to `SearchMailController`, wire `MailPolicy` |
| **Records** | Store scoped ✅, index NOT scoped ❌, only `RecordDigitalFolder` has `scopeByOrganisation` | Add scope to all record models, filter index |
| **Communications** | Store scoped ✅ (operator side), index NOT scoped ❌, show NOT scoped ❌ | Add org filter, wire `CommunicationPolicy` |
| **Transfers/Slips** | Store buggy (`organisation_id` ≠ `current_organisation_id`), index NOT scoped ❌ | Fix store bug, add org filter, wire `SlipPolicy` |
| **Workflow** | ZERO scoping — no `organisation_id` column, no policy, no filtering | Migration + model + controller + policy (full implementation) |
| **Deposits** | Maps to Slips module | Same fixes as Slips |

## 4. Dual-Organisation Pattern

Some modules have resources that involve TWO organisations (emitter vs beneficiary):

| Module | Emitter Field | Beneficiary Field | Scoping Rule |
|--------|--------------|-------------------|--------------|
| **Mails** | `sender_organisation_id` | `recipient_organisation_id` | Show if user's org is sender OR recipient |
| **Communications** | `operator_organisation_id` | `user_organisation_id` | Show if user's org is operator OR user |
| **Slips/Transfers** | `officer_organisation_id` | `user_organisation_id` | Show if user's org is officer OR user |
| **Records** | `organisation_id` (single owner) | — | Show only if org matches |
| **Workflow** | `organisation_id` (single owner) | — | Show only if org matches |
| **WorkPlaces** | `organisation_id` (single owner) | — | Show only if org matches |

## 5. Acceptance Criteria

### AC-1: Index Listings
- **Given** a user with `current_organisation_id = X`
- **When** they access any module's index page
- **Then** they see ONLY resources where `organisation_id = X` (single-owner modules) or where `emitter_org_id = X OR beneficiary_org_id = X` (dual-org modules)

### AC-2: Single Resource Access (show/edit/delete)
- **Given** a resource belonging to organisation Y
- **When** a user with `current_organisation_id = X` (X ≠ Y) tries to view/edit/delete it
- **Then** they receive a 403 Forbidden (or 404 for obfuscation)

### AC-3: Resource Creation
- **Given** a user creating a new resource
- **When** they submit the creation form
- **Then** `organisation_id` (or the emitter org field) is automatically set to `Auth::user()->current_organisation_id`

### AC-4: Search Scoping
- **Given** a search operation on any module
- **When** the user performs it
- **Then** results are filtered by their current organisation

### AC-5: SuperAdmin Bypass
- **Given** a user with the `superadmin` role
- **When** they access any resource
- **Then** they can see ALL resources across all organisations (bypass scoping)

## 6. Technical Requirements

### TR-1: Shared Trait
Create `App\Traits\BelongsToOrganisation` providing:
- `scopeByOrganisation($query, $organisationId)`
- `organisation()` BelongsTo relationship
- Boot method for auto-assignment from `Auth::user()->current_organisation_id` on creating

### TR-2: Dual-Organisation Scope Trait
Create `App\Traits\HasDualOrganisation` for modules with emitter/beneficiary pattern:
- `scopeForOrganisation($query, $organisationId)` — filters by `emitter_org OR beneficiary_org`
- Configurable field names via model properties

### TR-3: Database Migration
Add `organisation_id` column to:
- `workflow_definitions`
- `workflow_instances`

### TR-4: Policy Authorization
Wire `$this->authorize()` or `Gate::authorize()` in ALL controllers:
- `CommunicationController`
- `SlipController`
- `WorkflowDefinitionController`
- `WorkflowInstanceController`
- `RecordController` (enhance existing Gate calls)
- `MailController` (replace custom `canAccessMail` with policy)

### TR-5: Missing Policy
Create `WorkflowDefinitionPolicy` (extending `BasePolicy`).

## 7. Out of Scope
- Frontend changes (Vue.js components) — this plan covers backend only
- Public portal / OPAC — separate auth system
- Notification scoping
- Organisation switching UX

## 8. Risks
| Risk | Mitigation |
|------|-----------|
| Existing data has NULL `organisation_id` (Workflow) | Migration must set default org for existing rows |
| Performance impact of added WHERE clauses | Add database indexes on `organisation_id` columns |
| Breaking existing tests | Run full test suite after each module change |
