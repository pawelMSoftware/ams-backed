<?php

namespace App\Rules;

use App\Models\Group;
use Illuminate\Contracts\Validation\Rule;

class AMSGroupUserRule implements Rule
{
    /**
     * Create a new rule instance.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Determine if the validation rule passes.
     *
     * @param  string  $attribute
     * @param  mixed  $value
     * @return bool
     */
    public function passes(string $attribute, $value): bool
    {
        if (! is_array($value)) {
            return false;
        }
        $groups = Group::all()->pluck('group_id')->toArray();
        if (! empty(array_intersect($value, $groups))) {
            return true;
        }

        return false;
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message(): string
    {
        return __('validation.users.no_group');
    }
}
