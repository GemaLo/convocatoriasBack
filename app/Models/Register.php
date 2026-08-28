<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Register extends Model
{
    use HasFactory;

    protected $connection = 'oracle_primary';
    protected $table = 'registers';
    protected $primaryKey = 'idRegister';

    // Desactiva la conversión a snake_case para Eloquent
    public static $snakeAttributes = false;

    protected $fillable = [
        'idCandidato',
        'idCall',
        'curpMenor',
        'edad',
        'curpPdf',
        'actaPdf',
        'admit'
    ];

// En Register.php
public function candidato()
{
    return $this->belongsTo(Candidato::class, 'idcandidato', 'idcandidato');
}

public function call()
{
    return $this->belongsTo(Call::class, 'idcall', 'idcall');
}
}
