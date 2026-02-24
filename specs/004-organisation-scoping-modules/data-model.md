# Data Model: Organisation Scoping

**Feature**: 004-organisation-scoping-modules
**Date**: 2026-02-24

## 1. Trait Definitions (New)

### `App\Traits\BelongsToOrganisation`

Applied to models with a single `organisation_id` FK.

```
Trait: BelongsToOrganisation
├── Boot: auto-assign organisation_id on creating event
├── Scope: scopeByOrganisation($query, $organisationId)
│   └── return $query->where('organisation_id', $organisationId)
├── Relationship: organisation() → belongsTo(Organisation::class, 'organisation_id')
└── Accessor: getIsOwnedByCurrentOrgAttribute() → bool
```

**Models using this trait:**
- `WorkflowDefinition` (NEW — requires migration)
- `WorkflowInstance` (NEW — requires migration)
- `RecordPhysical` (EXISTING column, ADD trait)
- `RecordDigitalFolder` (EXISTING column + scope, REPLACE with trait)
- `RecordDigitalDocument` (EXISTING column, ADD trait)
- `Record` (EXISTING column, ADD trait)
- `Workplace` (EXISTING column + scope, REPLACE with trait)

### `App\Traits\HasDualOrganisation`

Applied to models where the current org can be emitter or beneficiary.

```
Trait: HasDualOrganisation
├── Abstract Properties (defined by model):
│   ├── $emitterOrgField (string) — e.g. 'sender_organisation_id'
│   └── $beneficiaryOrgField (string) — e.g. 'recipient_organisation_id'
├── Scope: scopeForOrganisation($query, $organisationId)
│   └── return $query->where(function($q) use ($organisationId) {
│          $q->where($this->emitterOrgField, $organisationId)
│            ->orWhere($this->beneficiaryOrgField, $organisationId);
│       })
├── Relationship: emitterOrganisation() → belongsTo(Organisation::class, $emitterOrgField)
├── Relationship: beneficiaryOrganisation() → belongsTo(Organisation::class, $beneficiaryOrgField)
└── Accessor: getInvolvesCurrentOrgAttribute() → bool
```

**Models using this trait:**
- `Mail` — emitter: `sender_organisation_id`, beneficiary: `recipient_organisation_id`
- `Communication` — emitter: `operator_organisation_id`, beneficiary: `user_organisation_id`
- `Slip` — emitter: `officer_organisation_id`, beneficiary: `user_organisation_id`

## 2. Schema Changes

### New Migration: `add_organisation_id_to_workflow_tables`

```
workflow_definitions
├── ADD: organisation_id BIGINT UNSIGNED NOT NULL
├── ADD: FOREIGN KEY (organisation_id) → organisations(id)
└── ADD: INDEX idx_workflow_def_org (organisation_id)

workflow_instances
├── ADD: organisation_id BIGINT UNSIGNED NOT NULL
├── ADD: FOREIGN KEY (organisation_id) → organisations(id)
└── ADD: INDEX idx_workflow_inst_org (organisation_id)
```

### New Migration: `add_organisation_indexes`

```
communications
├── ADD INDEX: idx_comm_operator_org (operator_organisation_id)
└── ADD INDEX: idx_comm_user_org (user_organisation_id)

slips
├── ADD INDEX: idx_slip_officer_org (officer_organisation_id)
└── ADD INDEX: idx_slip_user_org (user_organisation_id)

mails
├── ADD INDEX: idx_mail_sender_org (sender_organisation_id)
└── ADD INDEX: idx_mail_recipient_org (recipient_organisation_id)
```

## 3. Model Relationship Map

```
Organisation (id)
├──< WorkflowDefinition.organisation_id      [1:N — NEW]
├──< WorkflowInstance.organisation_id         [1:N — NEW]
├──< Record.organisation_id                   [1:N — EXISTS]
├──< RecordPhysical.organisation_id           [1:N — EXISTS]
├──< RecordDigitalFolder.organisation_id      [1:N — EXISTS]
├──< RecordDigitalDocument.organisation_id    [1:N — EXISTS]
├──< Workplace.organisation_id                [1:N — EXISTS]
├──< Mail.sender_organisation_id              [1:N — EXISTS, emitter]
├──< Mail.recipient_organisation_id           [1:N — EXISTS, beneficiary]
├──< Communication.operator_organisation_id   [1:N — EXISTS, emitter]
├──< Communication.user_organisation_id       [1:N — EXISTS, beneficiary]
├──< Slip.officer_organisation_id             [1:N — EXISTS, emitter]
└──< Slip.user_organisation_id                [1:N — EXISTS, beneficiary]
```

## 4. Controller Query Patterns

### Pattern A — Single Organisation (BelongsToOrganisation)
```php
// index()
$query = Model::query();
if (!Auth::user()->isSuperAdmin()) {
    $query->byOrganisation(Auth::user()->current_organisation_id);
}
$results = $query->paginate(20);

// store()
$data['organisation_id'] = Auth::user()->current_organisation_id;

// show/edit/update/delete()
$this->authorize('view', $model);  // Policy checks org via BasePolicy
```

### Pattern B — Dual Organisation (HasDualOrganisation)
```php
// index()
$query = Model::query();
if (!Auth::user()->isSuperAdmin()) {
    $query->forOrganisation(Auth::user()->current_organisation_id);
}
$results = $query->paginate(20);

// store()
$data[$this->emitterOrgField] = Auth::user()->current_organisation_id;

// show/edit/update/delete()
$this->authorize('view', $model);  // Policy + custom canAccessMail-like logic
```

## 5. Policy Wiring Summary

| Controller | Policy | Status |
|-----------|--------|--------|
| `WorkflowDefinitionController` | `WorkflowDefinitionPolicy` (NEW) | CREATE policy + wire |
| `WorkflowInstanceController` | `WorkflowDefinitionPolicy` (shared) | Wire via definition |
| `CommunicationController` | `CommunicationPolicy` (EXISTS) | Wire `$this->authorize()` |
| `SlipController` | `SlipPolicy` (EXISTS) | Wire `$this->authorize()` |
| `RecordController` | `RecordPolicy` (EXISTS) | Add model-level `$this->authorize()` |
| `MailController` | `MailPolicy` (EXISTS) | Replace custom `canAccessMail` with policy |
| `WorkplaceController` | `WorkplacePolicy` (EXISTS) | Fix `organisation_id` → `current_organisation_id` |
