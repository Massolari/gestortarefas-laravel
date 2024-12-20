<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class UserModel extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, HasFactory, SoftDeletes;

    protected $fillable = [
        'id',
        'name',
        'lastName',
        'avatar',
        'email',
        'password',
        'created_at',
        'updated_at',
        'deleted_at',
    ];

    protected $table = 'users';

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    public function tasklists()
    {
        return $this->hasMany(TasklistModel::class);
    }
}
