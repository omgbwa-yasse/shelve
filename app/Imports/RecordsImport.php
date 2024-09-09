<?php

namespace App\Imports;

use App\Models\Container;
use App\Models\Record;
use App\Models\RecordLevel;
use App\Models\RecordStatus;
use App\Models\RecordSupport;
use App\Models\Activity;
use App\Models\Author;
use App\Models\Term;
use App\Models\User;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;

class RecordsImport implements ToModel, WithHeadingRow, WithValidation
{
    public function model(array $row)
    {
        $record = Record::create([
            'code' => $row['code'],
            'name' => $row['name'],
            'date_format' => $row['date_format'],
            'date_start' => $row['start_date'],
            'date_end' => $row['end_date'],
            'date_exact' => $row['exact_date'],
            'level_id' => RecordLevel::firstOrCreate(['name' => $row['level']])->id,
            'width' => $row['width'],
            'width_description' => $row['width_description'],
            'biographical_history' => $row['biographical_history'],
            'archival_history' => $row['archival_history'],
            'acquisition_source' => $row['acquisition_source'],
            'content' => $row['content'],
            'appraisal' => $row['appraisal'],
            'accrual' => $row['accrual'],
            'arrangement' => $row['arrangement'],
            'access_conditions' => $row['access_conditions'],
            'reproduction_conditions' => $row['reproduction_conditions'],
            'language_material' => $row['language_material'],
            'characteristic' => $row['characteristic'],
            'finding_aids' => $row['finding_aids'],
            'location_original' => $row['original_location'],
            'location_copy' => $row['copy_location'],
            'related_unit' => $row['related_unit'],
            'publication_note' => $row['publication_note'],
            'note' => $row['note'],
            'archivist_note' => $row['archivist_note'],
            'rule_convention' => $row['rule_convention'],
            'status_id' => RecordStatus::firstOrCreate(['name' => $row['status']])->id,
            'support_id' => RecordSupport::firstOrCreate(['name' => $row['support']])->id,
            'activity_id' => Activity::firstOrCreate(['name' => $row['activity']])->id,
            'parent_id' => Record::firstOrCreate(['name' => $row['parent']])->id,
            'container_id' => Container::firstOrCreate(['name' => $row['container']])->id,
            'user_id' => User::firstOrCreate(['name' => $row['user']])->id,
        ]);

        // Handle authors
        if (isset($row['authors'])) {
            $authors = explode(',', $row['authors']);
            foreach ($authors as $authorName) {
                $author = Author::firstOrCreate(['name' => trim($authorName)]);
                $record->authors()->attach($author->id);
            }
        }

        // Handle terms
        if (isset($row['terms'])) {
            $terms = explode(',', $row['terms']);
            foreach ($terms as $termName) {
                $term = Term::firstOrCreate(['name' => trim($termName)]);
                $record->terms()->attach($term->id);
            }
        }

        return $record;
    }

    public function rules(): array
    {
        return [
            'code' => 'required',
            'name' => 'required',
            'level' => 'required',
            'status' => 'required',
            'support' => 'required',
            'activity' => 'required',
        ];
    }
}
