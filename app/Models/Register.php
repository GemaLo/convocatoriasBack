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

    protected $fillable = [
        'idCandidato', 
        'idcandidato', 
        'idCall',
        'curpMenor',
        'edad',
        'curpPdf',
        'actaPdf'
    ];

    public function candidato()
    {
        return $this->belongsTo(Candidato::class, 'idCandidato', 'idCandidato');
    }
}