<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VerificationMailModel extends Model
{
    use HasFactory;

    protected $table = 'email_verifications';

    protected $fillable = [
        'email',
        'verification_code',
        'verified',
        'expires_at',
        'created_at',
        'updated_at',
    ];
}
