<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Video;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
         User::factory()->create([
             'name' => 'admin',
             'email' => 'admin@mail.com',
             'role' => 'admin',
             'password' => 'secret'
         ]);
         User::factory()->create([
             'name' => 'federation',
             'email' => 'federation@mail.com',
             'role' => 'federation',
             'password' => 'secret'
         ]);
         User::factory()->create([
             'name' => 'client',
             'email' => 'client@mail.com',
             'role' => 'client',
             'password' => 'secret'
         ]);

         Video::factory(222)->create();
    }
}
