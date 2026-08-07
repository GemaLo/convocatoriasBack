<?php

namespace App\Http\Controllers;

use App\Models\Candidato;
use App\Models\Register;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;

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
        $candidato = Candidato::where('numempleado', $request->numEmpleado)->firstOrFail();

        foreach ($request->menores as $index => $menorData) {
            $fileCurp = $request->file("pdfCurp_{$index}");
            $fileActa = $request->file("pdfActa_{$index}");

            $pathCurp = $fileCurp->store('expedientes/curps', 'public');
            $pathActa = $fileActa ? $fileActa->store('expedientes/actas', 'public') : null;

            $register = new Register();
            $register->IDCALL      = $request->idCall;
            $register->IDCANDIDATO = $candidato->idcandidato;
            $register->CURPMENOR   = $menorData['curpMenor'];
            $register->EDAD        = $menorData['edad'];
            $register->CURPPDF     = $pathCurp;
            $register->ACTAPDF     = $pathActa;
            $register->save();
        }

        return response()->json([
            'success' => true,
            'constancia' => [
                'folio'       => 'REG-' . time(),
                'candidato'   => $candidato->NOMBRE,
                'numEmpleado' => $request->numEmpleado,
                'fecha'       => now()->format('Y-m-d H:i:s'),
            ]
        ]);
    }
}
