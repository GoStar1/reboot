<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Admin\AdminUser;
use App\Models\Admin\UserLogin;
use App\Models\Admin\UserOperation;

class UserController extends Controller
{
    public function history()
    {
        $list = UserLogin::where('user_id', \Auth::user()->id)
            ->orderByDesc('created_at')
            ->paginate()
            ->toArray();
        foreach ($list['data'] as &$item) {
            $item['user'] = AdminUser::find($item['user_id'])->name;
        }
        unset($item);
        return view('auth.user.history', [
            'active_menu' => 'user.userLogin',
            'title' => 'User Login',
            'list' => $list,
        ]);
    }

    public function operation()
    {
        $list = UserOperation::where('user_id', \Auth::user()->id)
            ->orderByDesc('created_at')
            ->paginate()
            ->toArray();
        foreach ($list['data'] as &$item) {
            $item['user'] = AdminUser::find($item['user_id'])->name;
        }
        unset($item);
        return view('auth.user.operations', [
            'active_menu' => 'user.operations',
            'title' => 'User Operation',
            'list' => $list,
        ]);
    }
}
