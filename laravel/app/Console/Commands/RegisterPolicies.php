<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class RegisterPolicies extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'policies:register';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Register all policies in AuthServiceProvider';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Registering policies in AuthServiceProvider...');

        $policies = $this->generatePoliciesArray();
        $this->updateAuthServiceProvider($policies);

        $this->info('Policies registered successfully!');
        return Command::SUCCESS;
    }

    /**
     * Generate the policies array
     *
     * @return array
     */
    private function generatePoliciesArray(): array
    {
        $modules = [
            // User Management
            'User', 'Role', 'Organisation', 'Activity', 'Author', 'Language', 'Term',

            // Content Management
            'Record', 'Mail', 'Slip', 'SlipRecord', 'Tool', 'Transferring', 'Task',
            'Deposit', 'Dolly', 'Container', 'Retention', 'Law', 'Communicability',
            'Reservation', 'Report', 'Event', 'Log', 'Backup',

            // Communication
            'Communication', 'BulletinBoard', 'Batch',

            // Location
            'Building', 'Floor', 'Room', 'Shelf',

            // System & Technical
            'Setting', 'PublicPortal', 'Post', 'Ai', 'Barcode'
        ];

        $policies = [];
        foreach ($modules as $module) {
            $policies[] = "        \\App\\Models\\{$module}::class => \\App\\Policies\\{$module}Policy::class,";
        }

        return $policies;
    }

    /**
     * Update the AuthServiceProvider with new policies
     *
     * @param array $policies
     */
    private function updateAuthServiceProvider(array $policies): void
    {
        $path = app_path('Providers/AuthServiceProvider.php');
        $content = File::get($path);

        // Generate the new policies array
        $newPoliciesArray = "    protected \$policies = [\n" .
                           "        // Existing policies\n" .
                           "        \\App\\Models\\PublicDocumentRequest::class => \\App\\Policies\\PublicDocumentRequestPolicy::class,\n" .
                           "        \\App\\Models\\PublicEvent::class => \\App\\Policies\\PublicEventPolicy::class,\n\n" .
                           "        // Auto-generated policies\n" .
                           implode("\n", $policies) . "\n" .
                           "    ];";

        // Replace the existing policies array
        $pattern = '/protected \$policies = \[[^]]+\];/s';
        $newContent = preg_replace($pattern, $newPoliciesArray, $content);

        File::put($path, $newContent);
    }
}
