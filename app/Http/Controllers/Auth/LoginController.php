<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Admin\UserLogin;
use App\Providers\RouteServiceProvider;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Http\Request;
use Auth;
use Session;

class LoginController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Login Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles authenticating users for the application and
    | redirecting them to your home screen. The controller uses a trait
    | to conveniently provide its functionality to your applications.
    |
    */

    use AuthenticatesUsers;

    /**
     * Where to redirect users after login.
     *
     * @var string
     */
    protected string $redirectTo = RouteServiceProvider::HOME;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('guest')->except('logout');
    }

    /**
     * Show the application's login form.
     *
     * @return \Illuminate\View\View
     */
    public function showLoginForm()
    {
        return view('auth.login');
    }

    public function showRe()
    {

    }

    protected function authenticated(Request $request, $user)
    {
        $new_session_id = Session::getId();
        (new UserLogin)->forceFill([
            'user_id' => $user->id,
            'ip' => $request->ip(),
            'session_id' => $new_session_id,
        ])->save();
        $previous_session = $user->session_id;
        if ($previous_session) {
            Session::getHandler()->destroy($previous_session);
        }
        Auth::user()->session_id = $new_session_id;
        Auth::user()->save();
    }
}
