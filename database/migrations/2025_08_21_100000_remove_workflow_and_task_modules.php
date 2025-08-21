<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Suppression des tables liées aux tâches
        Schema::dropIfExists('task_users');
        Schema::dropIfExists('task_remember');
        Schema::dropIfExists('task_assignment_history');
        Schema::dropIfExists('task_assignments');
        Schema::dropIfExists('task_attachments');
        Schema::dropIfExists('task_comments');
        Schema::dropIfExists('task_containers');
        Schema::dropIfExists('task_mails');
        Schema::dropIfExists('task_organisations');
        Schema::dropIfExists('task_records');
        Schema::dropIfExists('task_supervisions');
        Schema::dropIfExists('tasks');
        Schema::dropIfExists('task_types');
        Schema::dropIfExists('task_statuses');
        Schema::dropIfExists('task_categories');

        // Suppression des tables liées aux workflows
        Schema::dropIfExists('workflow_step_assignments');
        Schema::dropIfExists('workflow_step_instances');
        Schema::dropIfExists('workflow_steps');
        Schema::dropIfExists('workflow_instances');
        Schema::dropIfExists('workflow_templates');

        // Supprimer les entrées liées au workflow dans la table des permissions
        if (Schema::hasTable('permissions')) {
            DB::table('permissions')->where('name', 'LIKE', '%workflow%')->delete();
            DB::table('permissions')->where('name', 'LIKE', '%task%')->delete();
        }

        // Supprimer les entrées workflow des enums de catégories de permissions si nécessaire
        try {
            DB::statement("ALTER TABLE permissions DROP CONSTRAINT IF EXISTS permissions_category_check");

            // Récupérer les valeurs actuelles de l'enum
            $result = DB::select("SHOW COLUMNS FROM permissions LIKE 'category'");
            if (!empty($result)) {
                $enumValues = [];

                if (preg_match('/^enum\((.*)\)$/', $result[0]->Type, $matches)) {
                    $enumString = $matches[1];
                    $enumValues = array_map(
                        function($value) {
                            return trim($value, "'");
                        },
                        explode(',', $enumString)
                    );

                    // Filtrer les valeurs pour retirer 'workflow'
                    $filteredValues = array_filter($enumValues, function($value) {
                        return $value !== 'workflow';
                    });

                    // Reconstruire l'enum sans 'workflow'
                    if (count($filteredValues) > 0) {
                        $newEnumString = "'" . implode("','", $filteredValues) . "'";
                        DB::statement("ALTER TABLE permissions MODIFY COLUMN category ENUM($newEnumString) NOT NULL");
                    }
                }
            }
        } catch (\Exception $e) {
            // Ignorer les erreurs, car l'enum peut ne pas exister
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Cette migration supprime les modules workflow et task.
        // La restauration nécessiterait de recréer toutes les tables et structures,
        // ce qui est géré par les migrations originales. Nous ne le faisons pas ici.
    }
};
