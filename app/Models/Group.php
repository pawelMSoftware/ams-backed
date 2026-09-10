<?php

namespace App\Models;

use App\AMS\Enums\GroupType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Group extends Model
{
    use HasFactory;

    public static array $db2constMapping = [
        'EVERYONE' => GroupType::EVERYONE,
        'AdminsCategory' => GroupType::ADMINS_CATEGORY,
        'AdminsGroup' => GroupType::ADMINS_GROUP,
        'CopyrightDepartment' => GroupType::COPYRIGHT_DEPARTMENT,
        'NE Project' => GroupType::NE_PROJECT,
        'NowaEra' => GroupType::NOWA_ERA,
        'Royalty Free Editors' => GroupType::ROYALTY_FREE_EDITORS,
        'SanomaUT' => GroupType::SANOMA_UT,
        'Admins' => GroupType::ADMINS,
    ];

    protected $guarded = ['group_id', 'name', 'build_in'];

    protected $primaryKey = 'group_id';

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'groups_users', 'user_id', 'group_id')
            ->using(GroupsUsers::class);
    }
}
