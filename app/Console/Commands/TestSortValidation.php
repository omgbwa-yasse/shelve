<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Sort;
use Exception;

class TestSortValidation extends Command
{
    protected $signature = 'test:sort-validation';
    protected $description = 'Test Sort model validation';

    public function handle()
    {
        $this->info('Testing Sort validation...');

        // Test 1: Valid codes
        $validCodes = ['E', 'T', 'C'];
        foreach ($validCodes as $code) {
            try {
                $sort = new Sort();
                $sort->code = $code;
                $sort->name = "Test $code";
                $sort->description = "Test description for $code";
                $sort->save();
                $this->info("✓ Code '$code' accepté");
                $sort->delete(); // Nettoyer
            } catch (Exception $e) {
                $this->error("✗ Code '$code' rejeté: " . $e->getMessage());
            }
        }

        // Test 2: Invalid codes
        $invalidCodes = ['X', 'Y', 'Z', 'A'];
        foreach ($invalidCodes as $code) {
            try {
                $sort = new Sort();
                $sort->code = $code;
                $sort->name = "Test $code";
                $sort->description = "Test description for $code";
                $sort->save();
                $this->error("✗ Code invalide '$code' a été accepté (problème!)");
                $sort->delete(); // Nettoyer au cas où
            } catch (Exception $e) {
                $this->info("✓ Code invalide '$code' correctement rejeté: " . $e->getMessage());
            }
        }

        $this->info('Test terminé!');
    }
}
