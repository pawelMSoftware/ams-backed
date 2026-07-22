<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Facades\Auth;

class Resource extends Model
{
    use HasFactory;

    protected $table = 'resources';

    public $timestamps = false;

    public $with = ['categories', 'files', 'versions', 'relations', 'relations_inverse', 'resourceKeywords', 'notes', 'discs'];

    public function categories(): BelongsToMany
    {
        return $this->belongsToMany(Category::class, 'resources_categories', 'res_id', 'cat_id')
            ->using(ResourceCategory::class);
    }

    public function files(): HasMany
    {
        return $this->hasMany(ResourceFile::class, 'res_id');
    }

    public function versions(): HasMany
    {
        return $this->hasMany(ResourceVersion::class, 'res_id');
    }

    public function relations(): HasMany
    {
        return $this->hasMany(ResourceRelation::class, 'source_id');
    }

    public function relations_inverse(): HasMany
    {
        return $this->hasMany(ResourceRelation::class, 'dest_id');
    }

    /**
     * there is a property name `keywords` in resources table
     * so resourceKeywords() method is used instead of keywords()
     */
    public function resourceKeywords(): BelongsToMany
    {
        return $this->belongsToMany(Keyword::class, 'resources_keywords', 'res_id', 'key_id')
            ->using(ResourceKeyword::class);
    }

    public function notes(): HasMany
    {
        if (Auth::user()?->user_id) {
            return $this->hasMany(Note::class, 'res_id')->where('author', Auth::user()->login);
        }

        return $this->hasMany(Note::class, 'res_id');
    }

    public function discs(): HasOne
    {
        return $this->hasOne(Disc::class, 'disc_id', 'disc_id');
    }
}
