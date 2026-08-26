<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Call extends Model
{
    protected $table = 'calls';
    protected $primaryKey = 'idCall';
    protected $connection = 'oracle_primary';

    public $timestamps = true;

    protected $fillable = [
        'namecall',
        'yearcall',
        'dateinitialcall',
        'datefinalcall',
        'activo'
    ];

    public static $snakeAttributes = false;
}