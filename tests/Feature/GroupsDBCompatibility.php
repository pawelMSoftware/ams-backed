<?php

namespace Tests\Feature;

use App\AMS\Enums\GroupType;
use App\Models\Group;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\AMSTestCase;
use Tests\TestCase;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class GroupsDBCompatibility extends AMSTestCase
{
    /**
     * A basic feature test example.
     *
     * @return void
     */
    public function testMappingCompatibilityWithDb(): void
    {
        $groups = Group::all()->toArray();
        foreach ($groups as $group) {
            $groupTypeMapped = Group::$db2constMapping[$group['name']];
            $this->assertEquals($groupTypeMapped->value, $group['group_id']);
        }
    }
}
