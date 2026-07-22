<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ResourceVersion extends Model
{
    use HasFactory;

    protected $table = 'resources_versions';

    protected $guarded = ['id'];

    public $timestamps = false;

    protected $dates = ['date'];

    protected static function boot()
    {
        parent::boot();
        static::creating(function (ResourceVersion $resourceVersion) {
            $resourceVersion->date = date("Y-m-d H:i:s");
        });
    }
}
