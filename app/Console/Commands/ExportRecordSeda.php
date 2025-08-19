<?php

namespace App\Console\Commands;

use App\Exports\SEDAExport;
use App\Models\Record;
use Illuminate\Console\Command;

class ExportRecordSeda extends Command
{
    protected $signature = 'seda:export-record {record : Record ID} {--output= : Output file path}';

    protected $description = 'Export a single record to SEDA 2.1 XML file';

    public function handle(): int
    {
        $recordId = (int) $this->argument('record');
        $output = $this->option('output');

        $record = Record::with(['attachments', 'containers', 'level', 'parent', 'thesaurusConcepts'])->findOrFail($recordId);

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
