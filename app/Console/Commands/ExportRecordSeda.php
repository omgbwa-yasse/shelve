<?php

namespace App\Console\Commands;

use App\Exports\SEDAExport;
use App\Services\SedaZipBuilder;
use App\Models\RecordPhysical;
use Illuminate\Console\Command;

class ExportRecordSeda extends Command
{
    protected $signature = 'seda:export-record {record : RecordPhysical ID} {--output= : Output file path} {--zip : Produce a ZIP named with the manifest hash}';

    protected $description = 'Export a single record to SEDA 2.1 XML file';

    public function handle(): int
    {
        $recordId = (int) $this->argument('record');
        $output = $this->option('output');

        $record = RecordPhysical::with(['attachments', 'containers', 'level', 'parent', 'thesaurusConcepts'])->findOrFail($recordId);

        if ($this->option('zip')) {
            /** @var SedaZipBuilder $zipper */
            $zipper = app(SedaZipBuilder::class);
            [$zipPath] = $zipper->buildForRecord($record);
            $this->info('SEDA ZIP saved to: ' . $zipPath);
            return Command::SUCCESS;
        }

        /** @var SEDAExport $exporter */
        $exporter = app(SEDAExport::class);
        $xml = $exporter->exportRecords(collect([$record]));

        if (!$output) {
            $dir = storage_path('app/exports/seda');
            if (!is_dir($dir)) {
                mkdir($dir, 0775, true);
            }
            $output = $dir . DIRECTORY_SEPARATOR . 'seda_record_' . $record->id . '_' . date('Ymd_His') . '.xml';
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
