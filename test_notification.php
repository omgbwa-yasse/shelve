<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Services\NotificationService;
use App\Enums\NotificationModule;
use App\Enums\NotificationAction;

$service = new NotificationService();

try {
    $notification = $service->createForOrganisation(
        1,
        NotificationModule::BULLETIN_BOARDS,
        'Test Notification',
        NotificationAction::CREATE,
        'Ceci est un test du nouveau système de notifications'
    );

    echo "✅ Notification créée avec succès !\n";
    echo "ID: {$notification->id}\n";
    echo "Module: {$notification->module->value}\n";
    echo "Action: {$notification->action->value}\n";
    echo "Message: {$notification->message}\n";
    echo "Organisation ID: {$notification->organisation_id}\n";
    echo "Créée le: {$notification->created_at}\n";

} catch (Exception $e) {
    echo "❌ Erreur: " . $e->getMessage() . "\n";
    echo "Trace: " . $e->getTraceAsString() . "\n";
}
