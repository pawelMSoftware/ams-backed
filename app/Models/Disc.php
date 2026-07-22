<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Disc extends Model
{
    use HasFactory;

    public $table = 'discs';

    public $fillable = ['disc_id', 'files_path', 'files_alias', 'active'];

    public $timestamps = false;

    public $primaryKey = 'disc_id';
}
