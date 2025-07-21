<?php

namespace Database\Seeders;

use App\Models\Notification;
use App\Models\User;
use App\Models\Organisation;
use App\Enums\NotificationModule;
use App\Enums\NotificationPriority;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class NotificationSeeder extends Seeder
{
    /**
     * Run the database seeder.
     */
    public function run(): void
    {
        $users = User::take(3)->get();
        $organisations = Organisation::take(2)->get();

        // Notifications pour Bulletin Boards
        foreach ($users as $user) {
            Notification::createForUser(
                $user->id,
                NotificationModule::BULLETIN_BOARDS,
                'bulletin_published',
                'Nouveau bulletin publié',
                'Un nouveau bulletin d\'information a été publié dans votre organisation.',
                NotificationPriority::MEDIUM,
                ['bulletin_id' => 1, 'action' => 'published']
            );

            Notification::createForUser(
                $user->id,
                NotificationModule::BULLETIN_BOARDS,
                'comment_added',
                'Commentaire ajouté',
                'Un nouveau commentaire a été ajouté à votre bulletin.',
                NotificationPriority::LOW
            );
        }

        // Notifications pour d'autres modules
        if ($users->isNotEmpty()) {
            $user = $users->first();

            // Notifications MAILS
            Notification::createForUser(
                $user->id,
                NotificationModule::MAILS,
                'mail_received',
                'Nouveau courrier reçu',
                'Vous avez reçu un nouveau courrier à traiter.',
                NotificationPriority::HIGH
            );

            // Notifications RECORDS
            Notification::createForUser(
                $user->id,
                NotificationModule::RECORDS,
                'archive_scheduled',
                'Archivage programmé',
                'Des documents sont programmés pour archivage demain.',
                NotificationPriority::MEDIUM
            );

            // Notifications WORKFLOWS
            Notification::createForUser(
                $user->id,
                NotificationModule::WORKFLOWS,
                'workflow_pending',
                'Workflow en attente',
                'Un workflow nécessite votre validation.',
                NotificationPriority::HIGH
            );
        }

        // Notifications d'organisation pour Bulletin Boards
        foreach ($organisations as $organisation) {
            Notification::createForOrganisation(
                $organisation->id,
                NotificationModule::BULLETIN_BOARDS,
                'maintenance_scheduled',
                'Maintenance programmée',
                'Une maintenance du système de bulletins est programmée ce week-end.',
                NotificationPriority::HIGH,
                ['maintenance_date' => '2025-07-26']
            );

            Notification::createForOrganisation(
                $organisation->id,
                NotificationModule::BULLETIN_BOARDS,
                'feature_released',
                'Nouvelle fonctionnalité',
                'Une nouvelle fonctionnalité de notification est maintenant disponible.',
                NotificationPriority::MEDIUM
            );
        }
    }
}
