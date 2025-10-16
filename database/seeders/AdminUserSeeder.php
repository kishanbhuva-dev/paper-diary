<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class AdminUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
   public function run()
    {
        User::create([
            'firstName' => 'Admin',
            'lastName'  => 'User',
            'email'     => 'admin@gmail.com',
            'password'  => Hash::make('123456789'), // Use a secure password in production
            'role'      => 'admin',
            'address'   => '123 Admin Street',
            'address2'  => 'Suite 1',
            'city'      => 'uk',
            'country'   => 'uk',
            'postcode'  => '123456',
            'phone'     => '9999999999',
            'telephone' => '02212345678',
        ]);
    }
}
