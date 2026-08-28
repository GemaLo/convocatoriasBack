<?php

namespace App\Http\Controllers;

use App\Models\Call;
use App\Models\Register;
use App\Models\Candidato;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Cache;

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
            'data'    => $candidato
        ], 200);
    }

    public function saveRegister(Request $request)
    {
        $idCall = $request->input('idCall');

        $convocatoria = Call::where('idCall', $idCall)
            ->where('activo', 1)
            ->first();

        if (!$convocatoria) {
            return response()->json([
                'success' => false,
                'message' => 'No hay una convocatoria activa válida para registrar.'
            ], 422);
        }

        $numEmpleado   = $request->input('numEmpleado') ?? $request->input('numeroEmpleado');
        $correoUsuario = $request->input('email');
        $servidor      = $request->input('servidor', '@gmail.com');

        $emailFinal = !empty($correoUsuario)
            ? $correoUsuario . $servidor
            : "sin_correo_{$numEmpleado}@dominio.com";

        if ($request->has('menores') && is_array($request->menores)) {

            $curpsInRequest = array_filter(array_column($request->menores, 'curpMenor'));

            if (count($curpsInRequest) !== count(array_unique($curpsInRequest))) {
                return response()->json([
                    'success' => false,
                    'message' => 'Se enviaron CURPs duplicadas dentro de la misma solicitud.'
                ], 422);
            }

            foreach ($curpsInRequest as $curp) {
                $existeEnEstaConvocatoria = Register::where('curpMenor', trim($curp))
                    ->where('idCall', $idCall)
                    ->exists();

                if ($existeEnEstaConvocatoria) {
                    return response()->json([
                        'success' => false,
                        'message' => "El menor con CURP {$curp} ya fue registrado en la convocatoria actual."
                    ], 422);
                }
            }
        }

        try {
            $resultado = DB::transaction(function () use ($request, $idCall, $numEmpleado, $emailFinal) {

                $currentYear = date('Y');
                $lockKey     = "lock:folio_candidato_year_{$currentYear}";

                return Cache::lock($lockKey, 10)->block(5, function () use ($request, $idCall, $numEmpleado, $emailFinal, $currentYear) {

                    $candidato = Candidato::firstOrNew(['numEmpleado' => $numEmpleado]);

                    $candidato->firstName  = $request->input('firstName', 'N/A');
                    $candidato->lastName   = $request->input('lastName', 'N/A');
                    $candidato->middleName = $request->input('middleName', 'N/A');

                    $candidato->CURP = $request->input('curp');
                    $candidato->RFC  = $request->input('rfc');

                    $candidato->no_unidad  = 'H00';
                    $candidato->unidad  = 'GUARDIA NACIONAL';

                    $candidato->email  = $emailFinal;
                    $candidato->phone  = $request->input('phone', '0000000000');
                    $candidato->activo = "1";

                    if (!$candidato->exists || empty($candidato->folio)) {
                        $ultimoFolio = Candidato::where('year', $currentYear)->max('folio');
                        $nextFolio   = $ultimoFolio ? ((int)$ultimoFolio + 1) : 1;

                        $candidato->folio = (string)$nextFolio;
                        $candidato->year  = (string)$currentYear;
                        $candidato->psw   = Hash::make('password');
                    }

                    $candidato->save();

                    if (!$candidato->idCandidato && !$candidato->getKey()) {
                        $idCandidato = DB::connection('oracle_primary')
                            ->table('candidatos')
                            ->where('numEmpleado', $numEmpleado)
                            ->value('idCandidato');
                    } else {
                        $idCandidato = $candidato->idCandidato ?? $candidato->getKey();
                    }

                    if ($request->has('menores')) {
                        foreach ($request->menores as $index => $menorData) {

                            $fileCurp = $request->file("pdfCurp_{$index}") ?? $request->file("menores.{$index}.pdfCurp");
                            $fileActa = $request->file("pdfActa_{$index}") ?? $request->file("menores.{$index}.pdfActa");

                            $pathCurp = $fileCurp ? $fileCurp->store('expedientes/curps', 'public') : '';
                            $pathActa = $fileActa ? $fileActa->store('expedientes/actas', 'public') : '';

                            Register::create([
                                'idCandidato' => $idCandidato,
                                'idCall'      => $idCall,
                                'curpMenor'   => $menorData['curpMenor'] ?? null,
                                'edad'        => (int)($menorData['edad'] ?? 0),
                                'curpPdf'     => $pathCurp,
                                'actaPdf'     => $pathActa,
                            ]);
                        }
                    }

                    return [
                        'candidato' => $candidato,
                        'folio'     => $candidato->folio,
                        'year'      => $candidato->year
                    ];
                });
            });

            return response()->json([
                'success' => true,
                'constancia' => [
                    'folio'       => 'REG-' . $resultado['year'] . '-' . str_pad($resultado['folio'], 5, '0', STR_PAD_LEFT),
                    'candidato'   => trim(($resultado['candidato']->firstName ?? '') . ' ' . ($resultado['candidato']->lastName ?? '')),
                    'numEmpleado' => $numEmpleado,
                    'fecha'       => now()->format('Y-m-d H:i:s'),
                ]
            ]);
        } catch (\Illuminate\Contracts\Cache\LockTimeoutException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Servidor ocupado procesando solicitudes. Por favor reintenta.'
            ], 422);
        } catch (\Exception $e) {
            Log::error("Error en saveRegister: " . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Ocurrió un error al procesar el registro: ' . $e->getMessage()
            ], 500);
        }
    }

    public function indexRegisters()
    {
        $candidatos = Candidato::has('registers')
            ->with(['registers.call'])
            ->get();

        $data = $candidatos->map(function ($cand) {
            $nombreCompleto = trim(implode(' ', array_filter([
                $cand->firstName ?? $cand->firstname ?? '',
                $cand->middleName ?? $cand->middlename ?? '',
                $cand->lastName ?? $cand->lastname ?? ''
            ])));

            $ninos = $cand->registers->map(function ($reg) {
                return [
                    'idRegister'       => $reg->idRegister ?? $reg->idregister,
                    'curpMenor'        => $reg->curpMenor ?? $reg->curpmenor,
                    'edad'             => $reg->edad,
                    'estatus'          => $reg->admit ?? 'Pendiente',
                    'convocatoria'     => $reg->call->namecall ?? $reg->call->nameCall ?? 'N/A',
                    'curpPdf'          => $reg->curpPdf ?? $reg->curppdf,
                    'actaPdf'          => $reg->actaPdf ?? $reg->actapdf,
                ];
            });

            return [
                'idCandidato'           => $cand->idCandidato ?? $cand->idcandidato,
                'nombre_trabajador'     => $nombreCompleto,
                'num_empleado'          => $cand->numEmpleado ?? $cand->numempleado ?? 'N/A',
                'curp'                   => $cand->curp ?? 'N/A',
                'rfc'                   => $cand->rfc ?? 'N/A',
                'folio_registro'        => $cand->folio ?? 'N/A',
                'total_hijos'           => $ninos->count(),
                'ninos'                 => $ninos
            ];
        });

        return response()->json([
            'status' => 'success',
            'data'   => $data
        ], 200);
    }
}
