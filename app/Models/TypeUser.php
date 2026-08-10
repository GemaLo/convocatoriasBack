<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TypeUser extends Model
{
    protected $table = 'typeUser';
    protected $connection = 'oracle_primary';
    protected $primaryKey = 'idType';
    protected $fillable = ['type'];
}
