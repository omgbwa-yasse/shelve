<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use App\Models\Record;
use App\Models\Role;
use Illuminate\Support\Facades\Gate;

class TestPolicies extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'test:policies';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test que les policies fonctionnent correctement';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Test des policies...');

        // Test 1: Vérifier qu'un utilisateur avec les bonnes permissions peut voir les records
        $user = User::first();
        if (!$user) {
            $this->warn('Aucun utilisateur trouvé pour le test');
            return Command::FAILURE;
        }

        $this->info("Test utilisateur: {$user->name} (ID: {$user->id})");

        // Test des policies principales
        $this->testPolicy($user, Record::class, 'viewAny', 'record_viewAny');
        $this->testPolicy($user, Role::class, 'viewAny', 'role_viewAny');
        $this->testPolicy($user, User::class, 'viewAny', 'user_viewAny');

        // Test avec un model spécifique si possible
        $record = Record::first();
        if ($record) {
            $this->testPolicyWithModel($user, $record, 'view', 'record_view');
            $this->testPolicyWithModel($user, $record, 'update', 'record_update');
        }

        $this->info('Tests des policies terminés!');
        return Command::SUCCESS;
    }

    /**
     * Test une policy sans modèle spécifique
     */
    private function testPolicy(User $user, string $modelClass, string $ability, string $permission)
    {
        try {
            $result = Gate::forUser($user)->allows($ability, $modelClass);
            $hasPermission = $user->hasPermissionTo($permission);

            $status = $result ? '✅' : '❌';
            $permStatus = $hasPermission ? '✅' : '❌';

            $this->line("{$status} {$modelClass}::{$ability} | Permission {$permission}: {$permStatus}");
        } catch (\Exception $e) {
            $this->error("❌ Erreur lors du test {$modelClass}::{$ability}: " . $e->getMessage());
        }
    }

    /**
     * Test une policy avec un modèle spécifique
     */
    private function testPolicyWithModel(User $user, $model, string $ability, string $permission)
    {
        try {
            $result = Gate::forUser($user)->allows($ability, $model);
            $hasPermission = $user->hasPermissionTo($permission);

            $status = $result ? '✅' : '❌';
            $permStatus = $hasPermission ? '✅' : '❌';
            $modelClass = get_class($model);

            $this->line("{$status} {$modelClass}(#{$model->id})::{$ability} | Permission {$permission}: {$permStatus}");
        } catch (\Exception $e) {
            $modelClass = get_class($model);
            $this->error("❌ Erreur lors du test {$modelClass}::{$ability}: " . $e->getMessage());
        }
    }
}
