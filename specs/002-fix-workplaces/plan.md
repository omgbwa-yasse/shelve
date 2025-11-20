# Plan: Fix Workplaces Module

## 1. Technical Stack
- **Framework:** Laravel 12
- **Language:** PHP 8.2+
- **Database:** MySQL
- **Frontend:** Blade / Vue.js (if applicable, but focusing on backend logic first)

## 2. Architecture
- **Controllers:**
    - `WorkplaceInvitationController`: Handle invitation acceptance.
    - `WorkplaceBookmarkController`: Manage bookmarks.
    - `WorkplaceTemplateController`: Manage templates.
    - `WorkplaceActivityController`: List activities.
- **Mail:**
    - `WorkplaceInvitationMail`: Email notification.
- **Policies:**
    - `WorkplacePolicy`: Authorization logic.

## 3. File Structure
```
app/
    Http/
        Controllers/
            WorkplaceInvitationController.php
            WorkplaceBookmarkController.php
            WorkplaceTemplateController.php
            WorkplaceActivityController.php
    Mail/
        WorkplaceInvitationMail.php
    Policies/
        WorkplacePolicy.php
tests/
    Feature/
        WorkplaceTest.php
```

## 4. Implementation Steps
1.  **Setup & Authorization:**
    - Create `WorkplacePolicy`.
    - Register policy (if not auto-discovered).
    - Apply policy to `WorkplaceController` and `WorkplaceMemberController`.

2.  **Invitation System:**
    - Create `WorkplaceInvitationMail`.
    - Update `WorkplaceMemberController` to send email.
    - Create `WorkplaceInvitationController` with `accept` method.
    - Add routes.

3.  **Bookmarks:**
    - Create `WorkplaceBookmarkController`.
    - Implement `store` (toggle) and `index`.
    - Add routes.

4.  **Templates:**
    - Create `WorkplaceTemplateController`.
    - Implement CRUD.
    - Update `WorkplaceController` to use templates.
    - Add routes.

5.  **Activities:**
    - Create `WorkplaceActivityController`.
    - Implement `index`.
    - Add routes.

6.  **Testing:**
    - Write and run `WorkplaceTest`.
