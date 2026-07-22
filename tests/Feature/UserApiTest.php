<?php

namespace Tests\Feature;

use App\AMS\Enums\GroupType;
use App\Models\AMSUser;
use App\Models\Group;
use App\Models\GroupsUsers;
use Illuminate\Auth\AuthenticationException;
use Symfony\Component\HttpFoundation\Response;
use Tests\AMSTestCase;

class UserApiTest extends AMSTestCase
{
    public function test_users_list(): void
    {
        $url = route('user.index', [], false);

        $userNotAllowed = $this->getUserNotAllowed();

        $response = $this->runApi('', $url);
        $response->assertStatus(Response::HTTP_UNAUTHORIZED);

        $response = $this->runApi($userNotAllowed, $url);
        $response->assertStatus(Response::HTTP_FORBIDDEN);
        $this->assertEquals('Access forbidden', $response->getContent());

        $response = $this->runApi($userNotAllowed, $url);
        $response->assertStatus(Response::HTTP_FORBIDDEN);
        $this->assertEquals('Access forbidden', $response->getContent());

        $response = $this->runApi('admin', $url);
        $response->assertStatus(Response::HTTP_OK);
        $data = $response->json();
        $this->assertCount(AMSUser::count(), $data);
        $randomUser = reset($data);
        $this->assertArrayHasKey('user_id', $randomUser);
        $this->assertArrayHasKey('login', $randomUser);
        $this->assertArrayHasKey('email', $randomUser);
        $this->assertArrayHasKey('groups', $randomUser);
    }

    public function test_user_update_without_access(): void
    {
        $url = route('user.update', ['user' => 99999999999], false);
        $response = $this->runApi('admin', $url, 'put');
        $response->assertStatus(Response::HTTP_NOT_FOUND);

        $response = $this->runApi('admin', $url, 'post');
        $response->assertStatus(Response::HTTP_METHOD_NOT_ALLOWED);

        $user = AMSUser::inRandomOrder()->take(1)->get()->first();
        $url = route('user.update', ['user' => $user->user_id], false);

        $response = $this->runApi($this->getUserNotAllowed(), $url, 'put');
        $response->assertStatus(Response::HTTP_FORBIDDEN);
    }

    /**
     * @dataProvider userUpdateDataProvider
     *
     * @param  $userId
     *
     * @throws AuthenticationException
     */
    public function test_user_update($data, $expected): void
    {
        $url = route('user.update', ['user' => $data['userId']], false);
        $this->_testUserUpdateAndCreate($url, 'put', $data, $expected);
    }

    public function userUpdateDataProvider(): array
    {
        $this->createApplication();
        $user = AMSUser::where('login', '<>', 'admin')->inRandomOrder()->take(1)->get()->first();
        GroupsUsers::where('user_id', $user->user_id)->delete();
        GroupsUsers::create(['group_id' => GroupType::ADMINS_CATEGORY->value, 'user_id' => $user->user_id]);
        $user2 = AMSUser::where('user_id', '<>', $user->user_id)->inRandomOrder()->take(1)->get()->first();
        GroupsUsers::where('user_id', $user2->user_id)->delete();
        GroupsUsers::create(['group_id' => GroupType::NOWA_ERA->value, 'user_id' => $user2->user_id]);
        GroupsUsers::create(['group_id' => GroupType::NE_PROJECT->value, 'user_id' => $user2->user_id]);
        $user3 = AMSUser::where('user_id', '<>', $user->user_id)
            ->where('user_id', '<>', $user2->user_id)
            ->inRandomOrder()->take(1)->get()->first();
        GroupsUsers::where('user_id', $user3->user_id)->delete();
        GroupsUsers::create(['group_id' => GroupType::NOWA_ERA->value, 'user_id' => $user3->user_id]);
        GroupsUsers::create(['group_id' => GroupType::NE_PROJECT->value, 'user_id' => $user3->user_id]);

        return [
            [
                'data' => [
                    'userId' => $user->user_id,
                    'email' => '',
                    'password' => '',
                    'groups' => '',
                ],
                'expected' => [
                    'status' => Response::HTTP_UNPROCESSABLE_ENTITY,
                    'errorsCount' => 2,
                ],
            ],
            [
                'data' => [
                    'userId' => $user->user_id,
                    'email' => '',
                    'password' => '',
                    'groups' => [GroupType::NOWA_ERA->value],
                ],
                'expected' => [
                    'status' => Response::HTTP_UNPROCESSABLE_ENTITY,
                    'errorsCount' => 1,
                ],
            ],
            [
                'data' => [
                    'userId' => $user->user_id,
                    'email' => 'notvalid@email',
                    'password' => '',
                    'groups' => [GroupType::NOWA_ERA->value],
                ],
                'expected' => [
                    'status' => Response::HTTP_UNPROCESSABLE_ENTITY,
                    'errorsCount' => 1,
                    'errors' => [
                        'email' => [__('validation.email', ['attribute' => 'email'])],
                    ],
                ],
            ],
            // #4
            [
                'data' => [
                    'userId' => $user->user_id,
                    'email' => 'user@email.com',
                    'password' => '123',
                    'groups' => [GroupType::NOWA_ERA->value],
                ],
                'expected' => [
                    'status' => Response::HTTP_UNPROCESSABLE_ENTITY,
                    'errorsCount' => 1,
                    'errors' => [
                        'password' => [
                            __('validation.min.string', ['attribute' => 'password', 'min' => 8]),
                            __('validation.password.mixed', ['attribute' => 'password']),
                            __('validation.password.symbols', ['attribute' => 'password']),
                        ],
                    ],
                ],
            ],
            // #5
            [
                'data' => [
                    'userId' => $user->user_id,
                    'email' => 'user@email.com',
                    'password' => 'Abcd123!',
                    'groups' => 999,
                ],
                'expected' => [
                    'status' => Response::HTTP_UNPROCESSABLE_ENTITY,
                    'errorsCount' => 1,
                    'errors' => [
                        'groups' => [
                            __('validation.users.no_group'),
                        ],
                    ],
                ],
            ],
            [
                'data' => [
                    'userId' => $user->user_id,
                    'email' => 'notvalid@email',
                    'password' => 'Abcd1231',
                    'groups' => 999,
                ],
                'expected' => [
                    'status' => Response::HTTP_UNPROCESSABLE_ENTITY,
                    'errorsCount' => 3,
                    'errors' => [
                        'email' => [__('validation.email', ['attribute' => 'email'])],
                        'password' => [__('validation.password.symbols', ['attribute' => 'password'])],
                        'groups' => [__('validation.users.no_group')],
                    ],
                ],
            ],
            // all ok - adding new group
            [
                'data' => [
                    'userId' => $user->user_id,
                    'email' => 'valid@email.com',
                    'password' => 'Abcd123!',
                    'groups' => [GroupType::ADMINS_CATEGORY->value, GroupType::NOWA_ERA->value],
                ],
                'expected' => [
                    'status' => Response::HTTP_OK,
                    'errorsCount' => 0,
                    'groupsCount' => 2,
                ],
            ],
            // all ok - removing a group
            [
                'data' => [
                    'userId' => $user2->user_id,
                    'email' => 'valid@email.com',
                    'password' => 'Abcd123!',
                    'groups' => [GroupType::NOWA_ERA->value],
                ],
                'expected' => [
                    'status' => Response::HTTP_OK,
                    'errorsCount' => 0,
                    'groupsCount' => 1,
                ],
            ],
            // all ok - removing and adding a group
            [
                'data' => [
                    'userId' => $user3->user_id,
                    'email' => 'valid@email.com',
                    'password' => 'Abcd123!',
                    'groups' => [GroupType::COPYRIGHT_DEPARTMENT->value],
                ],
                'expected' => [
                    'status' => Response::HTTP_OK,
                    'errorsCount' => 0,
                    'groupsCount' => 1,
                ],
            ],
        ];
    }

    /**
     * @throws AuthenticationException
     */
    public function test_user_delete(): void
    {
        $user = AMSUser::create([
            'login' => 'user.'.rand(111111, 999999),
            'email' => 'email'.rand(111111, 999999).'@email.com',
            'password' => 'pass123',
            'hashed' => 1,
        ]);
        $this->assertNotEmpty($user);
        $this->assertDatabaseHas('users', ['user_id' => $user->user_id]);

        $userAdmin = AMSUser::where('login', 'admin')->get()->first();
        $url = route('user.destroy', ['user' => $userAdmin->user_id], false);
        $response = $this->runApi('admin', $url, 'delete');
        $response->assertStatus(Response::HTTP_BAD_REQUEST);

        $url = route('user.destroy', ['user' => $user->user_id], false);

        $response = $this->runApi($this->getUserNotAllowed(), $url, 'delete');
        $response->assertStatus(Response::HTTP_UNAUTHORIZED);

        $response = $this->runApi('admin', $url, 'delete');
        $response->assertStatus(Response::HTTP_NO_CONTENT);
        $this->assertDatabaseMissing('users', ['user_id' => $user->user_id]);
    }

    public function test_user_create_without_access(): void
    {
        $userData = [
            'login' => 'login.123',
            'email' => 'email@email.com',
            'groups' => GroupType::NOWA_ERA->value,
        ];
        $url = route('user.store', $userData, false);

        $response = $this->runApi('admin', $url, 'put');
        $response->assertStatus(Response::HTTP_METHOD_NOT_ALLOWED);

        $user = AMSUser::inRandomOrder()->take(1)->get()->first();
        $url = route('user.store', $userData, false);

        $response = $this->runApi($this->getUserNotAllowed(), $url, 'post');
        $response->assertStatus(Response::HTTP_FORBIDDEN);
    }

    /**
     * @dataProvider testUserCreateDataProvider
     *
     * @throws AuthenticationException
     */
    public function test_user_create($data, $expected): void
    {
        $url = route('user.store', [], false);
        $this->_testUserUpdateAndCreate($url, 'post', $data, $expected);
    }

    public function test_user_create_data_provider(): void
    {
        return [
            // #1
            [
                'data' => [
                    'login' => '',
                    'email' => '',
                    'password' => '',
                    'groups' => [],
                ],
                'expected' => [
                    'status' => Response::HTTP_UNPROCESSABLE_ENTITY,
                    'errorsCount' => 4,
                    'errors' => [
                        'login' => [__('validation.required', ['attribute' => 'login'])],
                        'email' => [__('validation.required', ['attribute' => 'email'])],
                        'password' => [__('validation.required', ['attribute' => 'password'])],
                        'groups' => [__('validation.required', ['attribute' => 'groups'])],
                    ],
                ],
            ],
            // #2
            [
                'data' => [
                    'login' => 'azr12_',
                    'email' => 'notvalid@email',
                    'password' => 'Abcd1231',
                    'groups' => 999,
                ],
                'expected' => [
                    'status' => Response::HTTP_UNPROCESSABLE_ENTITY,
                    'errorsCount' => 4,
                    'errors' => [
                        'email' => [__('validation.email', ['attribute' => 'email'])],
                        'groups' => [__('validation.users.no_group')],
                        'password' => [__('validation.password.symbols', ['attribute' => 'password'])],
                        'login' => [__('validation.login', ['attribute' => 'login'])],
                    ],
                ],
            ],
            // #3
            [
                'data' => [
                    'login' => fake()->userName(),
                    'email' => fake()->email(),
                    'password' => 'Abcd!123',
                    'groups' => [GroupType::NE_PROJECT->value, GroupType::SANOMA_UT->value],
                ],
                'expected' => [
                    'status' => Response::HTTP_CREATED,
                    'errorsCount' => 0,
                    'errors' => [
                    ],
                ],
            ],
            /*
                    'email' => 'notvalid@email',
                    'password' => 'Abcd1231',
                    'groups' => 999,
                ],
                'expected' => [
                    'status' => Response::HTTP_UNPROCESSABLE_ENTITY,
                    'errorsCount' => 3,
                    'errors' => [
                        'email' => [__('validation.email', ['attribute' => 'email'])],
                        'password' => [__('validation.password.symbols', ['attribute' => 'password'])],
                        'groups' => [__('validation.users.no_group')],

 */
        ];
    }

    /**
     * @return mixed
     */
    private function getUserNotAllowed()
    {
        $notAdminsGroupIds = Group::where('name', '<>', 'Admins')->where('name', '<>', 'AdminsGroup')->get()->pluck('group_id');
        $userNotAllowed = AMSUser::whereHas('groups', function ($query) use ($notAdminsGroupIds) {
            return $query->whereIn('groups_users.group_id', $notAdminsGroupIds);
        })->inRandomOrder()->take(1)->get()->first();

        return $userNotAllowed;
    }

    /**
     * @param  $userId
     *
     * @throws AuthenticationException
     */
    private function _testUserUpdateAndCreate(string $url, string $method, array $data, array $expected): void
    {
        $response = $this->runApi('admin', $url, $method, $data);
        $response->assertStatus($expected['status']);

        if ($expected['status'] === Response::HTTP_OK) {
            $user = AMSUser::find($data['userId']);
            $responseJson = $response->json();
            foreach ($data as $field => $value) {
                if ($field === 'password') {
                    $this->assertTrue($user->isPasswordOk($value));

                    continue;
                }
                if (! isset($responseJson[$field])) {
                    continue;
                }
                $responseFieldValue = $responseJson[$field];
                if ($field === 'groups') {
                    $userGroupsIds = array_keys($user->groupsById()->toArray());
                    $user->groups = $userGroupsIds;
                    sort($userGroupsIds);
                    $responseFieldValue = collect($responseFieldValue)->pluck('group_id')->toArray();
                }
                $this->assertEquals($value, $responseFieldValue);
                $this->assertEquals($value, $user->$field);
            }
            if (! empty($expected['groupsCount'])) {
                $this->assertCount($expected['groupsCount'], $user->groups);
            }

        } elseif ($expected['status'] === Response::HTTP_UNPROCESSABLE_ENTITY) {
            $responseJson = $response->json();
            $this->assertArrayHasKey('message', $responseJson);
            $this->assertArrayHasKey('errors', $responseJson);
            $this->assertCount($expected['errorsCount'], $responseJson['errors']);
            if (! empty($expected['errors'])) {
                $this->assertEquals($expected['errors'], $responseJson['errors']);
            }
        }
    }
}
