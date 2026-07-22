<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\Pivot;

class ResourceCategory extends Pivot
{
    use HasFactory;

    protected $table = 'resources_categories';

    public $timestamps = false;

    protected $primaryKey = 'res_id';
}
