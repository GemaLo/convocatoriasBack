<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('registers', function (Blueprint $table) {
            $table->id('idRegister');
            
            // Llaves foráneas
            $table->unsignedBigInteger('idCandidato');
            $table->foreign('idCandidato')->references('idCandidato')->on('candidatos')->onDelete('cascade');
            
            $table->unsignedBigInteger('idCall');
            $table->foreign('idCall')->references('idCall')->on('calls')->onDelete('cascade');

            // Datos del Menor
            $table->string('curpMenor', 18);
            $table->integer('edad');
            $table->string('curpPdf')->nullable();
            $table->string('actaPdf')->nullable();
            
            // Estatus / Admisión
            $table->string('admit')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('registers');
    }
};