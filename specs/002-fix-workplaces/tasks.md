# Tasks: Fix Workplaces Module

## Phase 1: Setup & Authorization
- [x] Create `app/Policies/WorkplacePolicy.php` with abilities: view, update, delete, manageMembers, manageContent <!-- id: 1 -->
- [x] Apply `WorkplacePolicy` to `WorkplaceController` <!-- id: 2, deps: 1 -->
- [x] Apply `WorkplacePolicy` to `WorkplaceMemberController` <!-- id: 3, deps: 1 -->

## Phase 2: Invitation System
- [x] Create `app/Mail/WorkplaceInvitationMail.php` <!-- id: 4 -->
- [x] Update `WorkplaceMemberController::store` to send `WorkplaceInvitationMail` <!-- id: 5, deps: 4 -->
- [x] Create `app/Http/Controllers/WorkplaceInvitationController.php` with `accept` method <!-- id: 6 -->
- [x] Add routes for invitation acceptance in `routes/web.php` <!-- id: 7, deps: 6 -->

## Phase 3: Bookmarks
- [x] Create `app/Http/Controllers/WorkplaceBookmarkController.php` with `index` and `store` methods <!-- id: 8 -->
- [x] Add routes for bookmarks in `routes/web.php` <!-- id: 9, deps: 8 -->

## Phase 4: Templates
- [x] Create `app/Http/Controllers/WorkplaceTemplateController.php` with CRUD methods <!-- id: 10 -->
- [x] Update `WorkplaceController::store` to support creating from template <!-- id: 11, deps: 10 -->
- [x] Add routes for templates in `routes/web.php` <!-- id: 12, deps: 10 -->

## Phase 5: Activities
- [x] Create `app/Http/Controllers/WorkplaceActivityController.php` with `index` method <!-- id: 13 -->
- [x] Add routes for activities in `routes/web.php` <!-- id: 14, deps: 13 -->

## Phase 6: Testing
- [x] Create `tests/Feature/WorkplaceTest.php` <!-- id: 15 -->
- [x] Run tests and verify all features <!-- id: 16, deps: 15 -->
