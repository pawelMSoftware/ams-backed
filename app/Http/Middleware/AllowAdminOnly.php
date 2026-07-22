<?php

namespace App\Http\Middleware;

use App\Models\AMSUser;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Log;

class AllowAdminOnly
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {
        $user = $request->user();
        if (is_null($user) || !$this->isAdmin($user)) {
            return $this->returnForbidden();
        }

        return $next($request);
    }

    private function isAdmin(AMSUser $user): bool
    {
        $groups = $user->toArray()['groups'];
        if (empty($groups)) {
            return false;
        }
        foreach ($groups as $userGroup) {
            if (in_array($userGroup['name'], AMSUser::ADMIN_GROUPS)) {
                return true;
            }
        }
        return false;
    }

    /**
     * @return \Illuminate\Http\Response
     */
    private function returnForbidden(): \Illuminate\Http\Response
    {
        return response('Access forbidden', Response::HTTP_FORBIDDEN);
    }
}
