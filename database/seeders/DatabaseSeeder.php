<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
//            IndexTokenSeeder::class
            ChainRpcSeeder::class,
            BlockChainSeeder::class,
            AdminUserSeeder::class,
            TemplateSeeder::class,
        ]);
//        \App\Models\Perp\IndexToken::factory(10)->create();
        // \App\Models\User::factory()->create([
        //     'name' => 'Test User',
        //     'email' => 'test@example.com',
        // ]);
    }
}
