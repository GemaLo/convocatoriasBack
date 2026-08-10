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
            $table->string('curpMenor', 18);
            $table->string('curpPdf');
            $table->string('actaPdf');
            $table->integer('edad');
            $table->unsignedBigInteger('idCandidato');
            $table->foreign('idCandidato')->references('idCandidato')->on('candidatos');
            $table->unsignedBigInteger('idCall');
            $table->foreign('idCall')->references('idCall')->on('calls');
            $table->string('admit')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('registers');
    }
};
