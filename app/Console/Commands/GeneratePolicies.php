<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class GeneratePolicies extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'make:policies {--force : Overwrite existing policies}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate policies for all modules based on permissions';    /**
     * Execute the console command.
     */
    public function handle()
    {
        $modules = $this->getModulesFromSeeder();
        $force = $this->option('force');

        $this->info('Generating policies for ' . count($modules) . ' modules based on PermissionSeeder...');

        $generated = 0;
        $skipped = 0;

        foreach ($modules as $module => $permissions) {
            $policyPath = app_path("Policies/{$module}Policy.php");

            if (File::exists($policyPath) && !$force) {
                $this->warn("Policy already exists: {$module}Policy");
                $skipped++;
                continue;
            }

            $this->generatePolicyFromPermissions($module, $permissions);
            $this->info("Generated: {$module}Policy");
            $generated++;
        }

        $this->info("Generation complete: {$generated} generated, {$skipped} skipped");
        $this->info('Remember to register policies in AuthServiceProvider!');

        return Command::SUCCESS;
    }

    /**
     * Get modules and their permissions from the PermissionSeeder
     *
     * @return array
     */
    private function getModulesFromSeeder(): array
    {
        $seederPath = database_path('seeders/PermissionSeeder.php');
        $seederContent = File::get($seederPath);

        $modules = [];

        // Extract permission patterns from seeder
        preg_match_all("/'name' => '([^_]+)_([^']+)'/", $seederContent, $matches, PREG_SET_ORDER);

        foreach ($matches as $match) {
            $moduleName = ucfirst($match[1]); // user -> User
            $action = $match[2]; // viewAny, view, create, etc.

            // Handle special cases
            $moduleName = $this->normalizeModuleName($moduleName);

            if (!isset($modules[$moduleName])) {
                $modules[$moduleName] = [];
            }

            $modules[$moduleName][] = $action;
        }

        return $modules;
    }

    /**
     * Normalize module names for special cases
     *
     * @param string $moduleName
     * @return string
     */
    private function normalizeModuleName(string $moduleName): string
    {
        $specialCases = [
            'User' => 'User',
            'Role' => 'Role',
            'Organisation' => 'Organisation',
            'Activity' => 'Activity',
            'Author' => 'Author',
            'Language' => 'Language',
            'Term' => 'Term',
            'Record' => 'Record',
            'Mail' => 'Mail',
            'Slip' => 'Slip',
            'Task' => 'Task',
            'Dolly' => 'Dolly',
            'Communication' => 'Communication',
            'Reservation' => 'Reservation',
            'Batch' => 'Batch',
            'Building' => 'Building',
            'Floor' => 'Floor',
            'Room' => 'Room',
            'Shelf' => 'Shelf',
            'Container' => 'Container',
            'Backup' => 'Backup',
            'Setting' => 'Setting',
            'Event' => 'Event',
            'Post' => 'Post',
            'Ai' => 'Ai',
            'Log' => 'Log',
            'Report' => 'Report',
            'Retention' => 'Retention',
            'Law' => 'Law',
            'Communicability' => 'Communicability',
            'Public' => 'PublicPortal', // public_portal -> PublicPortal
            'Bulletin' => 'BulletinBoard' // bulletin_board -> BulletinBoard
        ];

        return $specialCases[$moduleName] ?? $moduleName;
    }

    /**
     * Generate policy from extracted permissions
     *
     * @param string $module
     * @param array $permissions
     * @return void
     */
    private function generatePolicyFromPermissions(string $module, array $permissions): void
    {
        $moduleLower = strtolower($module);

        // Handle special cases for permission prefixes
        if ($module === 'PublicPortal') {
            $moduleLower = 'public_portal';
        } elseif ($module === 'BulletinBoard') {
            $moduleLower = 'bulletin_board';
        } elseif ($module === 'SlipRecord') {
            $moduleLower = 'slip_record';
        }

        $stub = $this->getPolicyStubFromPermissions($module, $moduleLower, $permissions);

        $policyPath = app_path("Policies/{$module}Policy.php");
        File::put($policyPath, $stub);
    }

    /**
     * Get the policy stub content based on actual permissions
     *
     * @param string $moduleClass
     * @param string $moduleLower
     * @param array $permissions
     * @return string
     */
    private function getPolicyStubFromPermissions(string $moduleClass, string $moduleLower, array $permissions): string
    {
        $methods = '';

        // Generate methods based on actual permissions found in seeder
        if (in_array('viewAny', $permissions)) {
            $methods .= "    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User \$user): bool
    {
        return \$user->hasPermissionTo('{$moduleLower}_viewAny', \$user->currentOrganisation);
    }

";
        }

        if (in_array('view', $permissions)) {
            $variableName = strtolower($moduleClass);
            $methods .= "    /**
     * Determine whether the user can view the model.
     */
    public function view(User \$user, {$moduleClass} \${$variableName}): bool
    {
        return \$user->hasPermissionTo('{$moduleLower}_view', \$user->currentOrganisation) &&
            \$this->checkOrganisationAccess(\$user, \${$variableName});
    }

";
        }

        if (in_array('create', $permissions)) {
            $methods .= "    /**
     * Determine whether the user can create models.
     */
    public function create(User \$user): bool
    {
        return \$user->hasPermissionTo('{$moduleLower}_create', \$user->currentOrganisation);
    }

";
        }

        if (in_array('update', $permissions)) {
            $variableName = strtolower($moduleClass);
            $methods .= "    /**
     * Determine whether the user can update the model.
     */
    public function update(User \$user, {$moduleClass} \${$variableName}): bool
    {
        return \$user->hasPermissionTo('{$moduleLower}_update', \$user->currentOrganisation) &&
            \$this->checkOrganisationAccess(\$user, \${$variableName});
    }

";
        }

        if (in_array('delete', $permissions)) {
            $variableName = strtolower($moduleClass);
            $methods .= "    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User \$user, {$moduleClass} \${$variableName}): bool
    {
        return \$user->hasPermissionTo('{$moduleLower}_delete', \$user->currentOrganisation) &&
            \$this->checkOrganisationAccess(\$user, \${$variableName});
    }

";
        }

        // Only add restore if there's no force_delete (Laravel convention)
        if (!in_array('force_delete', $permissions) && (in_array('delete', $permissions) || in_array('update', $permissions))) {
            $variableName = strtolower($moduleClass);
            $methods .= "    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User \$user, {$moduleClass} \${$variableName}): bool
    {
        return \$user->hasPermissionTo('{$moduleLower}_update', \$user->currentOrganisation) &&
            \$this->checkOrganisationAccess(\$user, \${$variableName});
    }

";
        }

        if (in_array('force_delete', $permissions)) {
            $variableName = strtolower($moduleClass);
            $methods .= "    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User \$user, {$moduleClass} \${$variableName}): bool
    {
        return \$user->hasPermissionTo('{$moduleLower}_force_delete', \$user->currentOrganisation) &&
            \$this->checkOrganisationAccess(\$user, \${$variableName});
    }

";
        }

        // Add the organization access check method
        $variableName = strtolower($moduleClass);
        $methods .= "    /**
     * Check if the user has access to the model within their current organisation.
     */
    private function checkOrganisationAccess(User \$user, {$moduleClass} \${$variableName}): bool
    {
        \$cacheKey = \"{$moduleLower}_org_access:{\$user->id}:{\${$variableName}->id}:{\$user->current_organisation_id}\";

        return Cache::remember(\$cacheKey, now()->addMinutes(10), function() use (\$user, \${$variableName}) {
            // For models directly linked to organisations
            if (method_exists(\${$variableName}, 'organisations')) {
                foreach(\${$variableName}->organisations as \$organisation) {
                    if (\$organisation->id == \$user->current_organisation_id) {
                        return true;
                    }
                }
            }

            // For models with organisation_id column
            if (isset(\${$variableName}->organisation_id)) {
                return \${$variableName}->organisation_id == \$user->current_organisation_id;
            }

            // For models linked through activity (like Record)
            if (method_exists(\${$variableName}, 'activity') && \${$variableName}->activity) {
                foreach(\${$variableName}->activity->organisations as \$organisation) {
                    if (\$organisation->id == \$user->current_organisation_id) {
                        return true;
                    }
                }
            }

            // Default: allow access if no specific organisation restriction
            return true;
        });
    }";

        return "<?php

namespace App\Policies;

use App\Models\\{$moduleClass};
use App\Models\User;
use Illuminate\Auth\Access\Response;
use Illuminate\Support\Facades\Cache;

class {$moduleClass}Policy
{
{$methods}
}
";
    }
}
