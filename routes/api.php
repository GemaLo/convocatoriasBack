<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\CandidatosController;

// Route::get('/user', function (Request $request) {
//     return $request->user();
// })->middleware('auth:sanctum');


Route::get('/candidato', [CandidatosController::class, 'dataServ'])->name('candidato');
Route::post('/candidato', [CandidatosController::class, 'saveRegister'])->name('candidato.save');