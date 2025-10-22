<?php

namespace Database\Seeders;

use App\Models\Admin\AdminUser;
use Illuminate\Database\Seeder;
use Log;

class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        $password = \Str::random(14);
        echo "admin password:$password\n";
        Log::error("[adminPassword][$password]");
        $user = (new AdminUser())->forceFill([
            'name' => 'admin',
            'email' => 'admin@robot.com',
            'password' => \Hash::make($password),
        ]);
        $user->save();
    }
}
