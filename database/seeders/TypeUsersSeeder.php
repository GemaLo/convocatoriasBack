<?php

namespace Database\Seeders;

use App\Models\TypeUser;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class TypeUsersSeeder extends Seeder
{
    public function run(): void
    {
        TypeUser::firstOrCreate(
            ['idType' => 1], 
            [
                'type' => 'ADMINISTRADOR',
            ]
        );
    }
}
