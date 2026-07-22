<?php

namespace Tests\Feature;

use App\Models\AMSUser as User;
use Symfony\Component\HttpFoundation\Response;
use Tests\AMSTestCase;

class UserTest extends AMSTestCase
{
    /**
     * A basic feature test example.
     */
    public function test_user_login(): void
    {
        $user = User::with('groups')->get()->random(1)->first();
        $data = [
            'login' => $user->login,
            'password' => $user->password,
        ];
        $response = $this->runApi($user->login, route('user.login'), 'post', $data);
        $response->assertStatus(Response::HTTP_OK);

        $responseUser = $response->json();
        $this->assertEquals($user->user_id, $responseUser['user_id']);
        $this->assertEquals($user->login, $responseUser['login']);
        $this->assertEquals($user->email, $responseUser['email']);
        $this->assertNotEmpty($responseUser['groups']);
        $this->assertEquals($user->groups()->first()->group_id, $responseUser['groups'][0]['group_id']);
    }

    public function test_user_logout(): void
    {
        $user = User::with('groups')->get()->random(1)->first();
        $response = $this->runApi($user->login, route('user.logout'), 'post');
        $response->assertStatus(Response::HTTP_RESET_CONTENT);
    }

    public function test_user_password_change(): void
    {
        $this->markTestSkipped('do it later');
    }
}
