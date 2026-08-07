<?php

namespace App\Console\Commands;

use App\Exports\SEDAExport;
use App\Services\SedaZipBuilder;
use App\Models\Slip;
use Illuminate\Console\Command;

class ExportSlipSeda extends Command
{
    protected $signature = 'seda:export-slip {slip : Slip ID} {--output= : Output file path} {--zip : Produce a ZIP named with the manifest hash}';

    protected $description = 'Export a single slip and its records to SEDA 2.1 XML file';

    public function handle(): int
    {
        $slipId = (int) $this->argument('slip');
        $output = $this->option('output');

        $slip = Slip::with(['records.attachments', 'records.containers', 'records.level', 'records.parent', 'records.thesaurusConcepts'])->findOrFail($slipId);

        if ($this->option('zip')) {
            /** @var SedaZipBuilder $zipper */
            $zipper = app(SedaZipBuilder::class);
            [$zipPath] = $zipper->buildForSlip($slip);
            $this->info('SEDA ZIP saved to: ' . $zipPath);
            return Command::SUCCESS;
        }

        /** @var SEDAExport $exporter */
        $exporter = app(SEDAExport::class);
        $xml = $exporter->export(collect([$slip]));

        if (!$output) {
            $dir = storage_path('app/exports/seda');
            if (!is_dir($dir)) {
                mkdir($dir, 0775, true);
            }
            $output = $dir . DIRECTORY_SEPARATOR . 'seda_slip_' . $slip->id . '_' . date('Ymd_His') . '.xml';
        } else {
            $dir = dirname($output);
            if (!is_dir($dir)) {
                mkdir($dir, 0775, true);
            }
        }

        file_put_contents($output, $xml);
        $this->info('SEDA export saved to: ' . $output);

        return Command::SUCCESS;
    }
}
