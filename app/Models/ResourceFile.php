<?php

namespace App\Models;

use App\Models\Events\SetResourceFileSize;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ResourceFile extends Model
{
    use HasFactory;

    protected $table = 'resources_files';

    public $timestamps = false;

    public $primaryKey = 'v_id';

    public function resource(): BelongsTo
    {
        return $this->belongsTo(Resource::class, 'resources', 'id', 'v_id');
    }

    protected $dispatchesEvents = [
        'retrieved' => SetResourceFileSize::class,
    ];
}
