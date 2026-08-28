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

    public static $snakeAttributes = false;

 protected $fillable = [
    'numEmpleado',
    'firstName',
    'middleName',
    'lastName',
    'CURP',
    'RFC',
    'email',
    'phone',
    'no_unidad',
    'unidad',
    'folio',
    'year',
    'activo',
    'psw',
    'admit'
];

public function registers()
{
    return $this->hasMany(Register::class, 'idcandidato', 'idcandidato');
}
}