<?php

namespace App\Imports;

use App\Models\Dolly;
use App\Models\Slip;
use Maatwebsite\Excel\Concerns\ToModel;

class SlipsImport implements ToModel
{
    public function __construct(private readonly Dolly $dolly)
    {
    }

    public function model(array $row)
    {
        $slip = new Slip([
            'code' => $row[0],
            'name' => $row[1],
            // ... autres champs ...
        ]);
        $slip->save();
        $this->dolly->slips()->attach($slip->id);
        return $slip;
    }
}
