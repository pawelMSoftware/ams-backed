<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ResourceRelation extends Model
{
    use HasFactory;

    protected $table = 'resources_relations';

    public $timestamps = false;

    public $primaryKey = 'rel_id';

    public function definitions()
    {
        return $this->hasMany(RelationDefinition::class, 'id', 'relation_id');
    }
}
