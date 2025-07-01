<?php
// Liste des fichiers à traiter
$files = [
    'app/Http/Controllers/WorkflowInstanceController.php',
    'app/Http/Controllers/WorkflowStepController.php',
    'app/Http/Controllers/WorkflowStepInstanceController.php',
    'app/Http/Controllers/WorkflowTemplateController.php',
    'resources/views/submenu/workflow.blade.php',
    'resources/views/workflow/dashboard.blade.php',
    'resources/views/workflow/instances/index.blade.php',
    'resources/views/workflow/instances/show.blade.php',
    'resources/views/workflow/instances/create.blade.php',
    'resources/views/workflow/templates/index.blade.php',
    'resources/views/workflow/templates/show.blade.php',
    'resources/views/workflow/templates/create.blade.php',
    'resources/views/workflow/templates/edit.blade.php',
    'resources/views/workflow/steps/index.blade.php',
    'resources/views/workflow/steps/show.blade.php',
    'resources/views/workflow/steps/create.blade.php',
    'resources/views/workflow/steps/edit.blade.php',
    'resources/views/workflow/step-instances/show.blade.php',
];

foreach ($files as $file) {
    $path = __DIR__ . '/' . $file;
    if (file_exists($path)) {
        $content = file_get_contents($path);
        $updated = str_replace(
            ["route('workflow.", "routeIs('workflow."],
            ["route('workflows.", "routeIs('workflows."],
            $content
        );

        if ($content !== $updated) {
            file_put_contents($path, $updated);
            echo "Updated: $file\n";
        } else {
            echo "No changes: $file\n";
        }
    } else {
        echo "File not found: $file\n";
    }
}

echo "Done!\n";
