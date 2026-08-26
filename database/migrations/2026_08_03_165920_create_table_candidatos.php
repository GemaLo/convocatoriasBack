<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('candidatos', function (Blueprint $table) {
            $table->id('idCandidato');
            $table->string('numEmpleado')->unique();
            $table->string('firstName');
            $table->string('middleName')->nullable();
            $table->string('lastName');
            $table->string('email');
            $table->string('phone');
            $table->string('folio')->nullable();
            $table->string('year')->nullable();
            $table->string('activo')->default('1');
            $table->string('psw');
            $table->string('admit')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void    
    {
        Schema::dropIfExists('candidatos');
    }
};