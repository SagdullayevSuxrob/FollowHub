<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $users = [
            [
                "username" => "sukhrob",
                "name" => "Sagdullayev Suxrob",
                "email" => "suxrob@gmail.com",
                "type" => "private",
                "password" => Hash::make("qwerty123"),
            ],
            [
                "username" => "sunnat",
                "name" => "Sagdullayev Sunnat",
                "email" => "sunnat@gmail.com",
                "type" => "public",
                "password" => Hash::make("qwerty123"),
            ],
            [
                "username" => "sarvar",
                "name" => "Sagdullayev Sarvar",
                "email" => "sarvar@gmail.com",
                "type" => "public",
                "password" => Hash::make("qwerty123"),
            ],
            [
                "username" => "sardor",
                "name" => "Sagdullayev Sardor",
                "email" => "sardor@gmail.com",
                "type" => "private",
                "password" => Hash::make("qwerty123"),
            ],
            [
                "username" => "sodiq",
                "name" => "Sagdullayev Sodiq",
                "email" => "sodiq@gmail.com",
                "type" => "public",
                "password" => Hash::make("qwerty123"),
            ],
            [
                "username" => "samandar",
                "name" => "Sagdullayev Samandar",
                "email" => "samandar@gmail.com",
                "type" => "private",
                "password" => Hash::make("qwerty123"),
            ],
            [
                "username" => "shahzod",
                "name" => "Sagdullayev Shahzod",
                "email" => "shahzod@gmail.com",
                "type" => "public",
                "password" => Hash::make("qwerty123"),
            ],
            [
                "username" => "shoxrux",
                "name" => "Sagdullayev Shoxrux",
                "email" => "shoxrux@gmail.com",
                "type" => "private",
                "password" => Hash::make("qwerty123"),
            ],
            [
                "username" => "shamshod",
                "name" => "Sagdullayev Shamshod",
                "email" => "shamshod@gmail.com",
                "type" => "private",
                "password" => Hash::make("qwerty123"),
            ],
            [
                "username" => "aziz",
                "name" => "Sagdullayev Azizbek",
                "email" => "aziz@gmail.com",
                "type" => "public",
                "password" => Hash::make("qwerty123"),
            ],
            [
                "username" => "test_user",
                "name" => "Test User",
                "email" => "test@gmail.com",
                "type" => "private",
                "password" => Hash::make("qwerty123"),
            ],
        ];

        foreach ($users as $user) {
            User::create($user);
        }
    }
}
