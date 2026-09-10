<?php

namespace Tests;

use App\AMS\Enums\GroupType;
use App\Models\AMSUser;
use App\Models\AMSUser as User;
use App\Models\GroupsUsers;
use Database\Seeders\AMSTestingSeeder;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Redis;
use Illuminate\Testing\TestResponse;
use Laravel\Sanctum\Sanctum;

class AMSTestCase extends TestCase
{
    use CreatesApplication;
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();
        Redis::connection('tests');
        $this->seed(AMSTestingSeeder::class);
        $this->artisan('db:populate');
        $this->checkAdminPrivileges();
    }

    protected function runApi(AMSUser|string $asUser, $url, $method = 'get', $data = []): TestResponse
    {
        if (! empty($asUser)) {
            if (get_debug_type($asUser) === \App\Models\AMSUser::class) {
                $user = $asUser;
            } else {
                $user = User::where(['login' => $asUser])->first();
            }
            if (is_null($user)) {
                throw new AuthenticationException('there is no such user in db');
            }
            Sanctum::actingAs($user, ['*']);
        }
        $headers = [
            'Accept' => 'application/json',
            'X-Requested-With' => 'XMLHttpRequest',
        ];

        return $this->$method($url, $data, $headers);
    }

    protected function tearDown(): void
    {
        parent::tearDown();
    }

    private function checkAdminPrivileges(): void
    {
        $adminUser = AMSUser::where('login', 'admin')->get()->first();
        GroupsUsers::updateOrCreate([
            'user_id' => $adminUser->user_id,
            'group_id' => GroupType::ADMINS_GROUP->value,
        ]);
    }
}
