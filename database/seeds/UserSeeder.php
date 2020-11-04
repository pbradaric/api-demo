<?php

use App\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        User::truncate();

        $password = Hash::make('admin');
        User::create([
            'name' => 'admin',
            'email' => 'admin@localhost',
            'password' => $password,
            'api_token' => 'EO9fsHiGWxZPKyhHpMcr8sm1iW9omUs3O1BMrIisnQZ6qaGMjQ7zXvFAmbnc',
        ]);
    }
}
