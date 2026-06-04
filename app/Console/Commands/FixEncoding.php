<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Repairs double-encoded ("mojibake") UTF-8 text in the database.
 *
 * Some seed / imported data was stored as UTF-8 bytes that had been
 * mis-interpreted as Windows-1252 and re-encoded to UTF-8, turning
 * "Réception" into "RÃ©ception". This command reverses that transform.
 *
 * Usage:
 *   php artisan data:fix-encoding --dry-run     # report only
 *   php artisan data:fix-encoding               # apply fixes
 *   php artisan data:fix-encoding --table=mails # restrict to one table
 */
class FixEncoding extends Command
{
    protected $signature = 'data:fix-encoding {--dry-run : Show what would change without writing} {--table= : Restrict to a single table}';

    protected $description = 'Repair double-encoded (mojibake) UTF-8 text in seed/imported data';

    /**
     * Tables and the text columns to scan, keyed by table name.
     * Add tables here as needed — unknown tables/columns are skipped safely.
     */
    protected array $targets = [
        'buildings'                 => ['name', 'description', 'address'],
        'floors'                    => ['name', 'description'],
        'rooms'                     => ['name', 'description'],
        'shelves'                   => ['name', 'description', 'observation'],
        'containers'                => ['name', 'description'],
        'mails'                     => ['object', 'subject', 'content', 'description', 'sender', 'recipient'],
        'communications'            => ['object', 'reason', 'description', 'comment'],
        'records'                   => ['name', 'title', 'content', 'description'],
        'slips'                     => ['name', 'description', 'note'],
        'activities'                => ['name', 'description'],
        'organisations'             => ['name', 'description'],
        'external_organizations'    => ['name', 'description', 'address', 'city'],
        'external_contacts'         => ['name', 'first_name', 'last_name', 'address', 'city'],
        'contacts'                  => ['label', 'name', 'value'],
        'agents'                    => ['name', 'description'],
        'retentions'                => ['name', 'description'],
        'communicabilities'         => ['name', 'description'],
        'mail_typologies'           => ['name', 'description'],
        'mail_priorities'           => ['name', 'description'],
        'mail_actions'              => ['name', 'description'],
        'roles'                     => ['name', 'description'],
        'record_statuses'           => ['name', 'description'],
        'record_supports'           => ['name', 'description'],
        'container_statuses'        => ['name', 'description'],
        'container_properties'      => ['name', 'description'],
        'slip_statuses'             => ['name', 'description'],
        'transferring_statuses'     => ['name', 'description'],
        'laws'                      => ['name', 'description'],
        'law_articles'              => ['name', 'description', 'content'],
        'keywords'                  => ['name', 'label'],
        'terms'                     => ['name', 'label', 'description'],
        'languages'                 => ['name'],
        'sorts'                     => ['name', 'description'],
        'thesaurus_schemes'         => ['title', 'description'],
        'thesaurus_labels'          => ['literal_form'],
        'thesaurus_concept_notes'   => ['note', 'content', 'text'],
    ];

    /** Windows-1252 codepoints in the 0x80-0x9F range that don't map 1:1 to bytes. */
    protected array $cp1252 = [
        0x20AC => 0x80, 0x201A => 0x82, 0x0192 => 0x83, 0x201E => 0x84, 0x2026 => 0x85,
        0x2020 => 0x86, 0x2021 => 0x87, 0x02C6 => 0x88, 0x2030 => 0x89, 0x0160 => 0x8A,
        0x2039 => 0x8B, 0x0152 => 0x8C, 0x017D => 0x8E, 0x2018 => 0x91, 0x2019 => 0x92,
        0x201C => 0x93, 0x201D => 0x94, 0x2022 => 0x95, 0x2013 => 0x96, 0x2014 => 0x97,
        0x02DC => 0x98, 0x2122 => 0x99, 0x0161 => 0x9A, 0x203A => 0x9B, 0x0153 => 0x9C,
        0x017E => 0x9E, 0x0178 => 0x9F,
    ];

    public function handle(): int
    {
        $dryRun = (bool) $this->option('dry-run');
        $only   = $this->option('table');
        $totalFixed = 0;

        $this->info($dryRun ? 'DRY RUN — no changes will be written.' : 'Applying encoding fixes...');

        foreach ($this->targets as $table => $columns) {
            if ($only && $only !== $table) {
                continue;
            }
            if (! Schema::hasTable($table)) {
                continue;
            }

            $primaryKey = Schema::hasColumn($table, 'id') ? 'id' : null;
            if (! $primaryKey) {
                continue; // can't update rows without a key
            }

            foreach ($columns as $column) {
                if (! Schema::hasColumn($table, $column)) {
                    continue;
                }

                // Only rows that contain the tell-tale mojibake markers.
                $rows = DB::table($table)
                    ->select($primaryKey, $column)
                    ->where(function ($w) use ($column) {
                        $w->where($column, 'like', '%Ã%')
                          ->orWhere($column, 'like', '%Â%')
                          ->orWhere($column, 'like', '%â€%');
                    })
                    ->get();

                $fixedHere = 0;
                $skipped = 0;
                foreach ($rows as $row) {
                    $original = $row->{$column};
                    if ($original === null || $original === '') {
                        continue;
                    }

                    $fixed = $this->repair($original);
                    if ($fixed !== null && $fixed !== $original) {
                        if (! $dryRun) {
                            try {
                                DB::table($table)->where($primaryKey, $row->{$primaryKey})->update([$column => $fixed]);
                            } catch (\Illuminate\Database\QueryException $e) {
                                // A unique constraint collision (the corrected value already exists
                                // as a separate clean row). Skip — leave the duplicate for manual merge.
                                $skipped++;
                                continue;
                            }
                        }
                        $fixedHere++;
                    }
                }

                if ($fixedHere > 0) {
                    $this->line(sprintf('  %s.%s — %d row(s) %s', $table, $column, $fixedHere, $dryRun ? 'would be fixed' : 'fixed'));
                    $totalFixed += $fixedHere;
                }
                if ($skipped > 0) {
                    $this->warn(sprintf('  %s.%s — %d row(s) skipped (duplicate of an existing value)', $table, $column, $skipped));
                }
            }
        }

        $this->newLine();
        $this->info(sprintf('%s %d field(s).', $dryRun ? 'Would fix' : 'Fixed', $totalFixed));

        return self::SUCCESS;
    }

    /**
     * Reverse the double-encoding. Returns the repaired string, or null when the
     * input is not safe to convert (so callers keep the original).
     */
    protected function repair(string $s): ?string
    {
        if (! mb_check_encoding($s, 'UTF-8')) {
            return null;
        }

        $bytes = '';
        foreach (preg_split('//u', $s, -1, PREG_SPLIT_NO_EMPTY) as $ch) {
            $cp = mb_ord($ch, 'UTF-8');
            if ($cp === false) {
                return null;
            }
            if ($cp <= 0xFF) {
                $bytes .= chr($cp);
            } elseif (isset($this->cp1252[$cp])) {
                $bytes .= chr($this->cp1252[$cp]);
            } else {
                // Character outside Windows-1252 — not our mojibake pattern; leave row untouched.
                return null;
            }
        }

        // Accept only if the recovered byte sequence is itself valid UTF-8.
        if (! mb_check_encoding($bytes, 'UTF-8')) {
            return null;
        }

        return $bytes;
    }
}
