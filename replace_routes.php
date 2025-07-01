<?php
// Liste des fichiers à traiter
$files = [
    'resources/views/workflow/step-instances/show.blade.php',
    'resources/views/workflow/instances/show.blade.php',
    'resources/views/tasks/supervision/index.blade.php',
    'resources/views/tasks/show.blade.php',
    'resources/views/tasks/my_tasks.blade.php',
    'resources/views/tasks/index.blade.php',
    'resources/views/tasks/edit.blade.php',
    'resources/views/tasks/create.blade.php',
    'app/Http/Controllers/TaskAssignmentController.php',
    'app/Http/Controllers/TaskController.php',
    'app/Http/Controllers/SystemNotificationController.php'
];

foreach ($files as $file) {
    $path = __DIR__ . '/' . $file;
    if (file_exists($path)) {
        $content = file_get_contents($path);
        $updated = str_replace(
            ["route('tasks.", "route('notifications.", "routeIs('tasks.", "routeIs('notifications."],
            ["route('workflows.tasks.", "route('workflows.notifications.", "routeIs('workflows.tasks.", "routeIs('workflows.notifications."],
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
