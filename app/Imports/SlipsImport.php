<?php

namespace App\Imports;

use App\Models\Slip;
use Maatwebsite\Excel\Concerns\ToModel;

class SlipsImport implements ToModel
{
    public function model(array $row)
    {
        return new Slip([
            'code' => $row[0],
            'name' => $row[1],
            // ... autres champs ...
        ]);
    }
}
