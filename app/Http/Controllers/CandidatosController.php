<?php

namespace App\Http\Controllers;

use App\Models\Candidato;
use App\Models\Register;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class CandidatosController extends Controller
{
    public function dataServ(Request $request)
    {
        $request->validate([
            'numero_empleado' => 'required'
        ]);

        $numeroEmpleado = $request->input('numero_empleado');

        $candidato = DB::connection('gn')
            ->table('empleados_prestacionesgn')
            ->where('num_empleado', $numeroEmpleado)
            ->first();

        if (!$candidato) {
            return response()->json([
                'success' => false,
                'message' => 'No se encontró información para el número de empleado ingresado.'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $candidato
        ], 200);
    }

    public function saveRegister(Request $request)
    {
        $numEmpleado = $request->input('numEmpleado') ?? $request->input('numeroEmpleado');

        $correoUsuario = $request->input('correoC');
        $servidor = $request->input('servidor', '@gmail.com');

        $emailFinal = !empty($correoUsuario)
            ? $correoUsuario . $servidor
            : "sin_correo_{$numEmpleado}@dominio.com"; // O un valor por defecto valido

        $candidato = Candidato::firstOrCreate(
            ['NUMEMPLEADO' => $numEmpleado],
            [
                'FIRSTNAME' => $request->input('nomPersona', 'N/A'),
                'LASTNAME'  => $request->input('appPersona', 'N/A'),
                'EMAIL'     => $emailFinal,
                'PHONE'     => $request->input('telefono', '0000000000'),
                'ACTIVO'     => 1,
                'PSW'       => Hash::make('password'),
            ]
        );

        if ($request->has('menores')) {
            foreach ($request->menores as $index => $menorData) {
                $fileCurp = $request->file("pdfCurp_{$index}");
                $fileActa = $request->file("pdfActa_{$index}");

                $pathCurp = $fileCurp ? $fileCurp->store('expedientes/curps', 'public') : null;
                $pathActa = $fileActa ? $fileActa->store('expedientes/actas', 'public') : null;

                $register = new Register();
                $register->IDCALL      = $request->input('idCall');
                $register->IDCANDIDATO = $candidato->IDCANDIDATO ?? $candidato->idcandidato;
                $register->CURPMENOR   = $menorData['curpMenor'] ?? null;
                $register->EDAD        = $menorData['edad'] ?? null;
                $register->CURPPDF     = $pathCurp;
                $register->ACTAPDF     = $pathActa;
                $register->save();
            }
        }

        return response()->json([
            'success' => true,
            'constancia' => [
                'folio'       => 'REG-' . time(),
                'candidato'   => trim(($candidato->FIRSTNAME ?? '') . ' ' . ($candidato->LASTNAME ?? '')),
                'numEmpleado' => $numEmpleado,
                'fecha'       => now()->format('Y-m-d H:i:s'),
            ]
        ]);
    }
}
