<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Call extends Model
{
    protected $table = 'CALLS';
    protected $primaryKey = 'idcall';
    protected $connection = 'oracle_primary';

    public $timestamps = true;

    protected $fillable = [
        'idcall',
        'namecall',
        'yearcall',
        'dateinitialcall',
        'datefinalcall',
        'activo',
    ];

    public static $snakeAttributes = false;
}