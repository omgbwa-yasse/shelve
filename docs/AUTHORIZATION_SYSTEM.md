# Digital Records Authorization System

## Overview
This document describes the authorization system implemented for the Phase 3 multi-type record architecture, which handles Physical Records, Digital Folders, and Digital Documents.

## Architecture

### Policy Pattern
The system uses Laravel's native Policy-based authorization with a role-based permission system. Each record type has its own policy class:

- **RecordPolicy** - Manages authorization for `RecordPhysical` (physical records)
- **RecordDigitalFolderPolicy** - Manages authorization for `RecordDigitalFolder` (digital folders)
- **RecordDigitalDocumentPolicy** - Manages authorization for `RecordDigitalDocument` (digital documents)

### Authorization Strategy
All policies implement a dual-authorization pattern:
```php
public function create(User $user): bool
{
    return $user->hasRole('superadmin') || 
           $user->can('digital_folders_create');
}
```

This grants access if the user either:
1. Has the `superadmin` role (full access to all features), OR
2. Has the specific permission for that action

## Permissions

### Digital Folders Permissions

| Permission Name | Description | Policy Method |
|----------------|-------------|---------------|
| `digital_folders_view` | View digital folders | `viewAny()`, `view()` |
| `digital_folders_create` | Create new digital folders | `create()` |
| `digital_folders_edit` | Edit existing digital folders | `update()` |
| `digital_folders_delete` | Soft delete digital folders | `delete()` |
| `digital_folders_restore` | Restore soft-deleted folders | `restore()` |
| `digital_folders_force_delete` | Permanently delete folders | `forceDelete()` |

### Digital Documents Permissions

| Permission Name | Description | Policy Method |
|----------------|-------------|---------------|
| `digital_documents_view` | View digital documents | `viewAny()`, `view()` |
| `digital_documents_create` | Create new digital documents | `create()` |
| `digital_documents_edit` | Edit existing digital documents | `update()` |
| `digital_documents_delete` | Soft delete digital documents | `delete()` |
| `digital_documents_restore` | Restore soft-deleted documents | `restore()` |
| `digital_documents_force_delete` | Permanently delete documents | `forceDelete()` |

### Physical Records Permissions

Physical records use the existing `RecordPolicy` with its own set of permissions (implementation varies based on existing codebase).

## Database Schema

### Tables
- **permissions** - Stores all permission definitions
- **roles** - Stores role definitions (includes `superadmin`)
- **role_permissions** - Native pivot table linking roles to permissions
- **role_has_permissions** - Spatie Permission package pivot table (maintained for compatibility)
- **users** - User accounts with role associations

### Dual Permission System
The system maintains compatibility with both:
1. **Native Laravel permission system** (`role_permissions` table)
2. **Spatie Laravel Permission package** (`role_has_permissions` table)

Both tables are updated when permissions are assigned to ensure compatibility across the codebase.

## Policy Registration

Policies are registered in `app/Providers/AuthServiceProvider.php`:

```php
protected $policies = [
    \App\Models\RecordPhysical::class => \App\Policies\RecordPolicy::class,
    \App\Models\RecordDigitalFolder::class => \App\Policies\RecordDigitalFolderPolicy::class,
    \App\Models\RecordDigitalDocument::class => \App\Policies\RecordDigitalDocumentPolicy::class,
];
```

## User Model Integration

The `User` model implements the `hasRole()` method:

```php
public function hasRole(string $roleName): bool
{
    return $this->role()->where('name', $roleName)->exists();
}
```

This enables role-based checks in policies without requiring external packages.

## UI Integration

### Blade Templates
Authorization checks are used throughout the UI to control visibility:

**repositories.blade.php** (submenu):
```blade
@can('create', App\Models\RecordPhysical::class)
    <a href="{{ route('records.create') }}">
        <i class="bi bi-plus-square"></i> {{ __('new') }} {{ __('(Physical)') }}
    </a>
@endcan

@can('create', App\Models\RecordDigitalFolder::class)
    <a href="{{ route('folders.create') }}">
        <i class="bi bi-folder-plus"></i> {{ __('Folder (Digital)') }}
    </a>
@endcan

@can('create', App\Models\RecordDigitalDocument::class)
    <a href="{{ route('documents.create') }}">
        <i class="bi bi-file-earmark-plus"></i> {{ __('Document (Digital)') }}
    </a>
@endcan
```

This ensures users only see UI elements for features they're authorized to access.

### Controller Integration
Controllers use policy authorization:

```php
// Implicit authorization
public function create()
{
    $this->authorize('create', RecordDigitalFolder::class);
    // ...
}

// Manual checks
public function index()
{
    if (!auth()->user()->can('viewAny', RecordDigitalFolder::class)) {
        abort(403);
    }
    // ...
}
```

## Seeding

### DigitalRecordPermissionsSeeder
Located at: `database/seeders/DigitalRecordPermissionsSeeder.php`

This seeder:
1. Creates all 12 digital record permissions (6 for folders, 6 for documents)
2. Assigns all permissions to the `superadmin` role
3. Maintains idempotency (safe to run multiple times)

**Run the seeder:**
```bash
php artisan db:seed --class=DigitalRecordPermissionsSeeder
```

## SuperAdmin Role

The `superadmin` role:
- Has ID `1` in the database
- Bypasses all permission checks via policy methods
- Has full access to all features (294 total permissions as of last check)
- Is automatically granted all new permissions when created

## Implementation Files

### Policy Classes
- `app/Policies/RecordPolicy.php` - Physical records (existing)
- `app/Policies/RecordDigitalFolderPolicy.php` - Digital folders (new)
- `app/Policies/RecordDigitalDocumentPolicy.php` - Digital documents (new)

### Seeders
- `database/seeders/DigitalRecordPermissionsSeeder.php` - Permission seeder

### Providers
- `app/Providers/AuthServiceProvider.php` - Policy registration

### Views
- `resources/views/submenu/repositories.blade.php` - UI authorization examples

## Testing Authorization

### Verify Permissions Exist
```bash
php artisan tinker --execute="DB::table('permissions')->where('name', 'LIKE', 'digital_%')->get(['name', 'description'])"
```

### Verify SuperAdmin Has Permissions
```bash
php artisan tinker --execute="DB::table('role_permissions')->where('role_id', 1)->count()"
```

### Test User Authorization
```php
// In Tinker
$user = User::find(1); // Get superadmin user
$user->hasRole('superadmin'); // Should return true
$user->can('digital_folders_create'); // Should return true
Gate::allows('create', RecordDigitalFolder::class); // Should return true
```

## Permission Naming Convention

All digital record permissions follow the pattern:
```
{model}_{action}
```

Examples:
- `digital_folders_view`
- `digital_documents_create`
- `digital_folders_force_delete`

This convention:
- Makes permissions self-documenting
- Enables easy filtering/searching
- Maintains consistency with existing permissions

## Future Enhancements

### Recommended Additions
1. **Role-based seeder** - Create additional roles (archivist, viewer, etc.) with specific permission sets
2. **Permission groups** - Group related permissions for easier bulk assignment
3. **Activity logging** - Log permission checks for audit trails
4. **UI permission manager** - Admin interface for managing role-permission assignments
5. **API authorization** - Extend policies to API endpoints with token-based auth

### Additional Policies Needed
- `RecordTypePolicy` - Manage record type configurations
- `KeywordPolicy` - Control keyword management
- `ThesaurusConceptPolicy` - Control thesaurus access

## Troubleshooting

### User Can't Access Digital Features
1. Verify user has `superadmin` role OR specific permissions
2. Check policy is registered in `AuthServiceProvider`
3. Verify permissions exist in database
4. Check role-permission pivot table entries

### Permissions Not Working
1. Clear application cache: `php artisan cache:clear`
2. Re-run permission seeder
3. Verify policy class exists and is autoloaded
4. Check for typos in permission names

### Database Issues
1. Verify both `role_permissions` and `role_has_permissions` tables exist
2. Check foreign key constraints are correct
3. Ensure permissions table has unique constraint on `name` column

## Security Considerations

### Best Practices
- Always use `@can()` directives in views before displaying sensitive UI
- Use `$this->authorize()` in controllers before performing sensitive actions
- Never bypass policies with direct database queries
- Keep permission names descriptive and consistent
- Grant minimum permissions needed for each role
- Regularly audit permission assignments

### SuperAdmin Usage
- Limit superadmin role to trusted administrators only
- Consider separate "admin" role with fewer permissions for daily operations
- Log all superadmin actions for audit trail
- Use superadmin only for system configuration, not regular operations

## Related Documentation
- [Phase 3 Implementation Plan](implementation-plan-speckit.md)
- [Record Physical Migration](refonte_records.md)
- [API User Guide](API_USER_GUIDE.md)
