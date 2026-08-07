<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Call extends Model
{
    protected $connection = 'oracle_primary';
    protected $table = 'calls';
    protected $primaryKey = 'idCall';

    protected $fillable = [
        'yearCall',
        'nameCall',
        'dateInitialCall',
        'dateFinalCall',
        'activo'
    ];
}
