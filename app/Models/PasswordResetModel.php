<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PasswordResetModel extends Model
{
    use HasFactory;
    
    public $timestamps = false;
    
    protected $table = 'password_reset_token';

    protected $fillable = [
        'email',
        'token',
        'created_at',
    ];
}
