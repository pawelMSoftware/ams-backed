<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RelationDefinition extends Model
{
    use HasFactory;

    protected $table = 'relation_definition';

    public $timestamps = false;
}
