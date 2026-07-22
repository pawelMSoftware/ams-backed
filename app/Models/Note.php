<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Note extends Model
{
    use HasFactory;

    public const NOTE_MIN_LENGTH = 5;
    public const NOTE_MAX_LENGTH = 255;

    protected $table = 'notes';

    public $timestamps = false;

    public $primaryKey = 'note_id';

    protected $fillable = ['res_id', 'author', 'text'];

    protected static function boot()
    {
        parent::boot();
        static::creating(function (Note $note) {
            $note->entered = date("Y-m-d H:i:s");
        });
    }
}
