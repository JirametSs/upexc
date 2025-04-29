<?php

namespace App\Imports;

use Maatwebsite\Excel\Concerns\ToCollection;
use Illuminate\Support\Collection;

class EmployeeImport implements ToCollection
{
    public Collection $rows;

    public function collection(Collection $collection)
    {
        $this->rows = $collection;
    }
}
