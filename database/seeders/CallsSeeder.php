<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Call;

class CallsSeeder extends Seeder
{
    public function run(): void
    {
        Call::firstOrCreate(
            ['idCall' => 1], 
            [
                'nameCall'      => 'Convocatoria Registro de Familiares ' . date('Y'),
                'yearCall'      => date('Y'),
                'dateInitialCall' => now(),
                'dateFinalCall'    => now()->addMonths(3),
                'activo'      => true,
            ]
        );
    }
}