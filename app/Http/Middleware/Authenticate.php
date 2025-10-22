<?php

namespace App\Http\Middleware;

use App\Models\Admin\UserOperation;
use Illuminate\Auth\Middleware\Authenticate as Middleware;
use Illuminate\Http\Request;
use Auth;

class Authenticate extends Middleware
{

    /**
     * Get the path the user should be redirected to when they are not authenticated.
     */
    protected function redirectTo(Request $request): ?string
    {
        return $request->expectsJson() ? null : route('login');
    }


    protected function authenticate($request, array $guards): void
    {
        parent::authenticate($request, $guards);
        $user = Auth::user();
        if ($request->method() === 'POST') {
            (new UserOperation())->forceFill([
                'user_id' => $user->id,
                'path' => $request->path(),
                'data' => json_encode($request->input()),
            ])->save();
        }
    }
}
