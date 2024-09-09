<?php

namespace App\Imports;

use App\Models\Record;
use App\Models\RecordLevel;
use App\Models\RecordStatus;
use App\Models\RecordSupport;
use App\Models\Activity;
use App\Models\Author;
use App\Models\Term;
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
            'date_start' => $row['start_date'],
            'date_end' => $row['end_date'],
            'content' => $row['content'],
            'location_original' => $row['original_location'],
            'level_id' => RecordLevel::firstOrCreate(['name' => $row['level']])->id,
            'status_id' => RecordStatus::firstOrCreate(['name' => $row['status']])->id,
            'support_id' => RecordSupport::firstOrCreate(['name' => $row['support']])->id,
            'activity_id' => Activity::firstOrCreate(['name' => $row['activity']])->id,
        ]);

        // Handle authors
        $authors = explode(',', $row['authors']);
        foreach ($authors as $authorName) {
            $author = Author::firstOrCreate(['name' => trim($authorName)]);
            $record->authors()->attach($author->id);
        }

        // Handle terms
        $terms = explode(',', $row['terms']);
        foreach ($terms as $termName) {
            $term = Term::firstOrCreate(['name' => trim($termName)]);
            $record->terms()->attach($term->id);
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
