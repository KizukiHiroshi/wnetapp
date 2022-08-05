<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\ValidateTrait;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;
    use SoftDeletes;
    use ValidateTrait;
    static $tablecomment = 'ログインユーザー';
    static $modelzone = '';
    static $defaultsort = [
        'id' => 'asc',
    ];
    static $referencedcolumns = [
        'name', 'email'
    ];
    static $uniquekeys = [
        ['email'],
    ];
    protected function rules() {
        return [
        ];
    }

    public function accounts() {
        return $this->hasMany(Account::class);
    }
    public function devices(){
        return $this->hasMany(Device::class);
    }
    public function members(){
        return $this->hasMany(Member::class);
    }

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];
}
