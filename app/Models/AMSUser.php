<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsToMany;

/**
 * @property int $user_id
 * @property string $login
 * @property string $password
 * @property string $email
 */
class AMSUser extends User
{
    /**
     * @todo in better world salt should be dynamic for every record
     * for now it is left due to BC
     */
    public const PWD_SALT = 'gR';

    // @todo change below names into GroupType when upgrading to php 8.2
    public const ADMIN_GROUPS = ['AdminsGroup', 'Admins'];

    protected $table = 'users';

    protected $guarded = ['user_id'];

    protected $hidden = ['password', 'sso_id', 'hashed', 'remember_token'];

    protected $primaryKey = 'user_id';

    protected $fillable = ['login', 'email', 'password'];

    protected $with = ['groups'];

    public array $groups = [];

    public BelongsToMany $groupsCache;

    protected $attributes = [
        'hashed' => 1,
    ];

    public function getId(): int
    {
        return $this->attributes['user_id'];
    }

    public function setId($value): void
    {
        $this->attributes['user_id'] = $value;
    }

    public function getAuthIdentifierName(): string
    {
        return 'user_id';
    }

    public function getAuthPassword(): string
    {
        return $this->password;
    }

    public function setPasswordAttribute(string $value): void
    {
        $this->attributes['password'] = crypt($value, self::PWD_SALT);
    }

    public function isPasswordOk(string $password): bool
    {
        if ($this->password === crypt($password, self::PWD_SALT)) {
            return true;
        }

        return false;
    }

    public function groups(): BelongsToMany
    {
        if (empty($this->groupsCache)) {
            $this->groupsCache = $this->belongsToMany(Group::class, 'groups_users', 'user_id', 'group_id')
                ->using(GroupsUsers::class);
        }

        return $this->groupsCache;
    }

    public function groupsById()
    {
        return collect($this->groups()->get()->all())->groupBy('group_id');
    }

    /*
     * do not use - it's less efficient
     * @return BelongsToMany
     */
    /*
    public function groupsNotAdmins(): BelongsToMany
    {
        return $this->belongsToMany(Group::class, 'groups_users', 'user_id', 'group_id')
            ->using(GroupsUsers::class)->wherePivotNotIn('group_id', [5, 208]);
    }
    */

    public function save(array $options = []): bool
    {
        $user = parent::save($options);
        /**
         * @todo save group data
         */
        if (! empty($this->groups)) {
        }

        return $user;
    }
}
