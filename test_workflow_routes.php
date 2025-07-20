<?php

// Test simple pour vérifier si les contrôleurs peuvent être chargés
require_once __DIR__ . '/vendor/autoload.php';

use App\Http\Controllers\MailWorkflowController;
use App\Http\Controllers\MailTaskController;

echo "Testing MailWorkflowController instantiation...\n";
try {
    $workflowController = new MailWorkflowController();
    echo "✓ MailWorkflowController created successfully\n";
} catch (Exception $e) {
    echo "✗ Error creating MailWorkflowController: " . $e->getMessage() . "\n";
}

echo "Testing MailTaskController instantiation...\n";
try {
    $taskController = new MailTaskController();
    echo "✓ MailTaskController created successfully\n";
} catch (Exception $e) {
    echo "✗ Error creating MailTaskController: " . $e->getMessage() . "\n";
}

echo "All tests completed!\n";
