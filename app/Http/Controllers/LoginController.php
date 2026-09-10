<?php

namespace App\Http\Controllers;

use App\Models\AMSUser;
use App\Models\AMSUser as User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class LoginController extends Controller
{
    public function login(Request $request): JsonResponse|AMSUser
    {
        if ($request->user()) {
            return $this->getUserWithData($request->user());
        }
        $credentials = ['login' => $request['login'], 'password' => $request['password']];
        $user = User::where('login', $credentials['login'])->first();

        /**
         * @todo refactor this and move checking to AMSUser model
         * USE User::isPasswordOk after creating login-checking tests
         */
        $isUserAuthenticated = $user && $user->password === crypt($request['password'], AMSUser::PWD_SALT);

        if ($isUserAuthenticated) {
            Auth::login($user);

            return $this->getUserWithData(Auth::user());
        }

        return response()->json('Unauthorized', Response::HTTP_OK);
    }

    private function getUserWithData(User $user): AMSUser
    {
        return $user;
    }

    public function logout(Request $request): JsonResponse
    {
        // Auth::logout();
        Auth::guard('web')->logout();

        return response()->json('', 205);
    }

    public function check(Request $request): JsonResponse|AMSUser
    {
        if ($request->user()) {
            return $this->getUserWithData($request->user());
        }

        return response()->json('Empty', Response::HTTP_NOT_FOUND);
    }
}
