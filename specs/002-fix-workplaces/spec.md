# Specification: Fix Workplaces Module

## 1. Overview
The Workplaces module allows users to create collaborative spaces. The current implementation has several gaps between the database schema and the actual application logic. This specification outlines the necessary fixes and additions to complete the module.

## 2. Identified Gaps (Manquements)

### 2.1 Invitation System
- **Current State:** `WorkplaceMemberController::store` creates an invitation record but does not send an email (commented out `TODO`). There is no mechanism for a user to accept an invitation via a link/token.
- **Requirement:**
    - Implement `WorkplaceInvitationMail`.
    - Send email upon invitation creation.
    - Create a route and controller method to handle invitation acceptance (verify token, add user to workplace, delete invitation).

### 2.2 Bookmarks
- **Current State:** `workplace_bookmarks` table exists. No logic to add/remove bookmarks.
- **Requirement:**
    - Create `WorkplaceBookmarkController`.
    - Implement `store` (toggle bookmark) and `index` (list user's bookmarks) methods.
    - Add routes.

### 2.3 Templates
- **Current State:** `workplace_templates` table exists. No logic to create workplaces from templates or manage templates.
- **Requirement:**
    - Create `WorkplaceTemplateController`.
    - Implement CRUD for templates.
    - Update `WorkplaceController::store` to optionally accept a `template_id` to initialize the workplace structure.

### 2.4 Activities
- **Current State:** Activities are logged but only the last 5 are shown in the dashboard.
- **Requirement:**
    - Create `WorkplaceActivityController`.
    - Implement `index` with filtering (by type, user, date).

### 2.5 Authorization
- **Current State:** Basic checks in controllers.
- **Requirement:**
    - Create `WorkplacePolicy`.
    - Define abilities: `view`, `update`, `delete`, `manageMembers`, `manageContent`.
    - Apply policy in controllers.

## 3. Technical Plan

### 3.1 Files to Create
- `app/Mail/WorkplaceInvitationMail.php`
- `app/Http/Controllers/WorkplaceInvitationController.php`
- `app/Http/Controllers/WorkplaceBookmarkController.php`
- `app/Http/Controllers/WorkplaceTemplateController.php`
- `app/Http/Controllers/WorkplaceActivityController.php`
- `app/Policies/WorkplacePolicy.php`
- `tests/Feature/WorkplaceTest.php`

### 3.2 Files to Modify
- `app/Http/Controllers/WorkplaceController.php` (Use Policy, Support Templates)
- `app/Http/Controllers/WorkplaceMemberController.php` (Send Email, Use Policy)
- `routes/web.php` (Add new routes)

## 4. Testing Strategy
- Create a feature test `WorkplaceTest.php` covering:
    - Workplace creation (standard & from template).
    - Member invitation flow (send & accept).
    - Bookmarking.
    - Activity logging.
    - Authorization checks.
