<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\CallsController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\CandidatosController;

// Route::get('/user', function (Request $request) {
//     return $request->user();
// })->middleware('auth:sanctum');


Route::get('/candidato', [CandidatosController::class, 'dataServ'])->name('candidato');
Route::post('/candidato', [CandidatosController::class, 'saveRegister'])->name('candidato.save');
Route::get('/calls/activa', [CallsController::class, 'getActiva']);


Route::post('/login', [AuthController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/user', function (Request $request) {
        return $request->user();
    });
    Route::get('/calls', [CallsController::class, 'index']);
    Route::post('/calls', [CallsController::class, 'store']);
    Route::put('/calls/{id}', [CallsController::class, 'update']);
    Route::patch('/calls/{id}/toggle-status', [CallsController::class, 'toggleStatus']);
});
