<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\Pivot;

class ResourceKeyword extends Pivot
{
    use HasFactory;

    protected $table = 'resources_keywords';

    public $timestamps = false;

    public $primaryKey = 'key_id';
}
