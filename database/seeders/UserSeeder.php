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
    public function run()
    {
        User::create(
            [
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
            ],
        );
        User::create(
            [
                'firstName' => 'Owner',
                'lastName'  => 'User',
                'email'     => 'owner@owner.com',
                'password'  => Hash::make('123456789'),
                'role'      => 'owner',
                'address'   => '456 Owner Avenue',
                'address2'  => 'Block 2',
                'city'      => 'uk',
                'country'   => 'uk',
                'postcode'  => '654321',
                'phone'     => '8888888888',
                'telephone' => '02298765432',
            ],
        );

        User::create(
            [
                'firstName' => 'Demo',
                'lastName'  => 'User',
                'email'     => 'user@demo.com',
                'password'  => Hash::make('123456789'),
                'role'      => 'user',
                'address'   => '789 User Boulevard',
                'address2'  => 'Apt 3',
                'city'      => 'uk',
                'country'   => 'uk',
                'postcode'  => '987654',
                'phone'     => '7777777777',
                'telephone' => '02211223344',
            ],
        );
    }
}
