<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Candidato extends Model
{
    use HasFactory;

    protected $connection = 'oracle_primary';
    protected $table = 'CANDIDATOS';
    protected $primaryKey = 'IDCANDIDATO';

    protected $fillable = [
        'NUMEMPLEADO',
        'EMAIL',
        'PHONE',
        'FIRSTNAME',
        'MIDDLENAME',
        'LASTNAME',
        'ACTIVO',
        'PSW',
        'CALL'
    ];

    public function registers()
    {
        return $this->hasMany(Register::class, 'IDCANDIDATO', 'IDCANDIDATO');
    }
}