<?php


namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;

class MainController extends Controller
{
    public function index()
    {
        return view('dashboard.main.index', [
            'active_menu' => 'dashboard.main',
            'title' => 'Dashboard',
        ]);
    }
}
