<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasFactory, HasApiTokens,  Notifiable;
    protected $table = 'users';
    protected $connection = 'oracle_primary';
    protected $primaryKey = 'id_user';
    protected $fillable = [
        'firstName',
        'lastName',
        'middleName',
        'nivel',
        'unidad',
        'active',
        'idType',
        'email',
        'password',
    ];
}
