<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        User::firstOrCreate(
            ['id_user' => 1], 
            [
                'firstName' => 'Gema Elizabeth',
                'lastName' => 'Loperena',
                'middleName' => 'Gutiérrez',
                'nivel' => 'O23',
                'unidad' => '142',
                'active' => 1,
                'idType' => 1,
                'email' => 'gema.loperena@sspc.gob.mx',
                'password' => Hash::make('password'),
            ]
        );
    }
}
