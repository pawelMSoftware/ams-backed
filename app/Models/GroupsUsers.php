<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\Pivot;

class GroupsUsers extends Pivot
{
    use HasFactory;

    protected $table = 'groups_users';

    protected $primaryKey = 'user_id';

    public $timestamps = false;
}
