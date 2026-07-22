<?php

namespace App\Http\Controllers;

use App\Models\AMSUser;
use App\Models\GroupsUsers;
use App\Rules\AMSGroupUserRule;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rules\Password;
use Symfony\Component\HttpFoundation\Response;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $users = AMSUser::get()->all();
        return response($users, Response::HTTP_OK);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        DOKOŃCZYĆ TESTY I IMPLEMENTACJĘ DODAWANIA USERA
        $this->validateUserData($request, true, true);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  AMSUser  $user
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, AMSUser $user)
    {
        // login cannot be changed
        $shouldChangePassword = !empty($request['password']);
        $this->validateUserData($request, $shouldChangePassword);

        if ($shouldChangePassword) {
            $user->password = trim($request['password']);
        }

        $userGroupsIds = array_keys($user->groupsById()->toArray());
        $user->groups = $userGroupsIds;
        sort($userGroupsIds);

        $user->email = $request['email'];
        if (!$user->save()) {
            return response('Problems with saving user data', Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        $groupsToAdd = array_diff($request['groups'], $userGroupsIds);
        $areGroupsToAdd = !empty($groupsToAdd);
        $groupsToRemove = array_diff($userGroupsIds, $request['groups']);
        $areGroupsToRemove = !empty($groupsToRemove);
        // add or remove only a difference between the state before and after
        if ($areGroupsToAdd || $areGroupsToRemove) {
            if ($areGroupsToRemove) {
                GroupsUsers::where('user_id', $user->user_id)->whereIn('group_id', $groupsToRemove)->delete();
            }
            if ($areGroupsToAdd) {
                foreach ($groupsToAdd as $groupId) {
                    GroupsUsers::create([
                        'group_id' => $groupId,
                        'user_id' => $user->user_id,
                    ]);
                }
            }
            $user = AMSUser::find($user->user_id);
        }

        return response($user);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  AMSUser  $user
     * @return \Illuminate\Http\Response
     */
    public function destroy(AMSUser $user)
    {
        if ($user->login === 'admin') {
            return response('cannot remove admin user', Response::HTTP_BAD_REQUEST);
        }
        $user->delete();
        return response('', Response::HTTP_NO_CONTENT);
    }

    /**
     * @param Request $request
     * @param bool $shouldValidatePassword
     * @param bool $isAddingNewUser
     * @return void
     * @throws \Illuminate\Validation\ValidationException
     */
    private function validateUserData(Request $request, bool $shouldValidatePassword = false, bool $isAddingNewUser = false): void
    {
        $validationDefinition = [
            // login cannot be changed during update
            'email' => ['required', 'email:rfc,filter'],
            'groups' => ['required', new AMSGroupUserRule()],
        ];
        if ($shouldValidatePassword) {
            $validationDefinition['password'] = ['required', 'max:16', Password::min(8)->mixedCase()->numbers()->symbols()];
        }
        $messages = [];
        if ($isAddingNewUser) {
            // 'login' => ['required', 'min:3', 'max:20'], // login cannot be changed
            $validationDefinition['login'] = ['required', 'unique:users', 'regex:#^([a-zA-Z0-9]+)([.\-_]?)([a-zA-Z0-9]+)$#', 'min:6', 'max:20'];
            $messages = [
                'login.regex' => __('validation.login'),
            ];
        }
        $this->validate(
            $request,
            $validationDefinition,
            $messages
        );
    }
}
