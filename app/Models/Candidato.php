<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Candidato extends Model
{
    use HasFactory;

    protected $connection = 'oracle_primary';
    protected $table = 'candidatos';
    protected $primaryKey = 'idCandidato';

    protected $fillable = [
        'numEmpleado',
        'email',
        'phone',
        'firstName',
        'middleName',
        'lastName',
        'activo',
        'psw',
        'call'
    ];

    public function registers()
    {
        return $this->hasMany(Register::class, 'idCandidato', 'idCandidato');
    }
}