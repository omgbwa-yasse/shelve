# API Contract: Organisation Scoping — No New Endpoints

**Feature**: 004-organisation-scoping-modules
**Date**: 2026-02-24

## Overview

This feature does NOT introduce new API endpoints. Instead, it modifies the **behavior** of existing endpoints by adding organisation-based data filtering.

## Behavioral Changes to Existing Endpoints

### 1. Workflow Endpoints (routes/web.php)

| Method | URI | Change |
|--------|-----|--------|
| `GET` | `/workflows` | Add `WHERE organisation_id = current_org` |
| `POST` | `/workflows` | Auto-assign `organisation_id` from auth user |
| `GET` | `/workflows/{id}` | Add policy authorization (org check) |
| `PUT` | `/workflows/{id}` | Add policy authorization (org check) |
| `DELETE` | `/workflows/{id}` | Add policy authorization (org check) |
| `GET` | `/workflows/instances` | Add `WHERE organisation_id = current_org` |
| `POST` | `/workflows/instances` | Auto-assign `organisation_id` from auth user |

### 2. Communications Endpoints

| Method | URI | Change |
|--------|-----|--------|
| `GET` | `/communications` | Add `WHERE operator_org = X OR user_org = X` |
| `GET` | `/communications/{id}` | Add `$this->authorize('view', $comm)` |
| `PUT` | `/communications/{id}` | Add `$this->authorize('update', $comm)` |
| `DELETE` | `/communications/{id}` | Add `$this->authorize('delete', $comm)` |

### 3. Slips/Transfers Endpoints

| Method | URI | Change |
|--------|-----|--------|
| `GET` | `/slips` | Add `WHERE officer_org = X OR user_org = X` |
| `POST` | `/slips` | Fix: use `current_organisation_id` instead of `organisation_id` |
| `GET` | `/slips/{id}` | Add `$this->authorize('view', $slip)` |
| `GET` | `/slips/sort/{status}` | Add org filter to sorted listings |

### 4. Records Endpoints

| Method | URI | Change |
|--------|-----|--------|
| `GET` | `/records` | Add `WHERE organisation_id = current_org` to physical, folder, and document queries |
| `GET` | `/records/{id}` | Add `$this->authorize('view', $record)` |

### 5. Mails Endpoints

| Method | URI | Change |
|--------|-----|--------|
| `GET` | `/mails/search/advanced` | Add `WHERE sender_org = X OR recipient_org = X` |
| All | `/mails/*` | Wire `MailPolicy` instead of custom `canAccessMail()` |

### 6. Workplaces Endpoints

| Method | URI | Change |
|--------|-----|--------|
| All | `/workplaces/*` | Fix policy to use `current_organisation_id` |

## Response Format

No changes to response format. The same JSON/HTML responses are returned — only the dataset is filtered.

## Error Responses

| Code | When |
|------|------|
| `403 Forbidden` | User's current org doesn't match resource's org (via Policy) |
| `404 Not Found` | Resource doesn't exist or is hidden for obfuscation (via `denyAsNotFound`) |
