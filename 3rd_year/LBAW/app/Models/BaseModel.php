<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class BaseModel extends Model
{
    public $timestamps = false;
    protected function htmlDate(?Carbon $value): ?string
    {
        return $value?->format('Y-m-d');
    }

    protected function prettyDate(?Carbon $value): ?string
    {
        return $value?->format('d M Y');
    }
}
