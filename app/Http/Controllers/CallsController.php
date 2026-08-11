<?php

namespace App\Http\Controllers;

use App\Models\Call;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;


class CallsController extends Controller
{
    public function index()
    {
        $calls = Call::select([
                'idcall',
                'yearcall', 
                'namecall',
                'dateinitialcall',
                'datefinalcall',
                'activo'
            ])
            ->orderBy('idcall', 'desc')
            ->get();

        return response()->json([
            'status' => 'success',
            'data'   => $calls
        ], 200);
    }

    public function store(Request $request)
    {
        $request->validate([
            'namecall'        => 'required|string|max:255',
            'dateinitialcall' => 'required|date',
            'datefinalcall'   => 'required|date|after_or_equal:dateinitialcall',
        ]);

        DB::beginTransaction();
        try {
            $dateInitial = Carbon::parse($request->input('dateinitialcall'))->format('Y-m-d H:i:s');
            $dateFinal   = Carbon::parse($request->input('datefinalcall'))->format('Y-m-d H:i:s');

            Call::query()->update(['activo' => 0]);

            $call = new Call();
            $call->namecall        = $request->input('namecall');
            $call->yearcall        = date('Y');
            $call->dateinitialcall = $dateInitial;
            $call->datefinalcall   = $dateFinal;
            $call->activo          = 1;
            $call->save();

            DB::commit();

            return response()->json([
                'status'  => 'success',
                'message' => 'Convocatoria registrada con éxito.',
                'data'    => $call
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'status'  => 'error',
                'message' => 'Error de Oracle/Laravel: ' . $e->getMessage()
            ], 500);
        }
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'namecall'        => 'required|string|max:255',
            'dateinitialcall' => 'required|date',
            'datefinalcall'   => 'required|date|after_or_equal:dateinitialcall',
            'activo'          => 'required|in:0,1',
        ]);

        try {
            $call = Call::findOrFail($id);

            if ((int)$request->input('activo') === 1) {
                Call::where('idcall', '!=', $id)->update(['activo' => 0]);
            }

            $call->namecall        = $request->input('namecall');
            $call->dateinitialcall = Carbon::parse($request->input('dateinitialcall'))->format('Y-m-d H:i:s');
            $call->datefinalcall   = Carbon::parse($request->input('datefinalcall'))->format('Y-m-d H:i:s');
            $call->activo          = $request->input('activo');
            $call->save();

            return response()->json([
                'status'  => 'success',
                'message' => 'Convocatoria actualizada correctamente.',
                'data'    => $call
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Error al actualizar: ' . $e->getMessage()
            ], 500);
        }
    }

    public function toggleStatus(Request $request, $id)
    {
        try {
            $call = Call::findOrFail($id);
            $newStatus = (int)$call->activo === 1 ? 0 : 1;

            if ($newStatus === 1) {
                Call::where('idcall', '!=', $id)->update(['activo' => 0]);
            }

            $call->activo = $newStatus;
            $call->save();

            return response()->json([
                'status'  => 'success',
                'message' => $newStatus === 0 ? 'Convocatoria inactivada.' : 'Convocatoria activada.',
                'data'    => $call
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Error al cambiar estatus: ' . $e->getMessage()
            ], 500);
        }
    }

    public function getActiva()
    {
        try {
            $call = Call::where('activo', 1)->first();

            if (!$call) {
                return response()->json([
                    'success' => false,
                    'message' => 'No hay ninguna convocatoria activa actualmente.'
                ], 404);
            }

            return response()->json([
                'success' => true,
                'data' => [
                    'id' => $call->idcall,
                    'titulo' => $call->namecall,
                    'year' => $call->yearcall,
                    'activo' => $call->activo,
                ]
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al consultar la base de datos: ' . $e->getMessage()
            ], 500);
        }
    }
}
