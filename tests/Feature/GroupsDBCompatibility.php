<?php

namespace Tests\Feature;

use App\Models\Group;
use Tests\AMSTestCase;

class GroupsDBCompatibility extends AMSTestCase
{
    /**
     * A basic feature test example.
     */
    public function test_mapping_compatibility_with_db(): void
    {
        $groups = Group::all()->toArray();
        foreach ($groups as $group) {
            $groupTypeMapped = Group::$db2constMapping[$group['name']];
            $this->assertEquals($groupTypeMapped->value, $group['group_id']);
        }
    }
}
