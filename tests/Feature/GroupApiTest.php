<?php

namespace Tests\Feature;

use App\Models\Group;
use Tests\AMSTestCase;

class GroupApiTest extends AMSTestCase
{
    /**
     * A basic feature test example.
     *
     * @return void
     */
    public function testGroupsList()
    {
        $groups = Group::get()->pluck('name', 'group_id')->all();

        $url = route('group.index', [], false);
        $response = $this->runApi('admin', $url);

        $response->assertStatus(200);
        $data = $response->json();
        $this->assertCount(9, $data);

        foreach ($groups as $groupId => $groupName) {
            $this->assertEquals($groupName, $data[$groupId]);
        }
    }
}
