<?php

namespace App\Models\Common;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Company extends Model
{
    use SoftDeletes;
    protected $guarded = [];
    static $tablecomment = '企業';
    static $modelzone = '共通';
    static $defaultsort = [
        'name_kana' => 'asc',
    ];
    static $referencedcolumns = [
        'code', 
        'name', 
    ];
    static $uniquekeys = [
        'code', 
    ];
    public function departments() {
        return $this->hasMany(Department::class);
    }
    public function businessunits() {
        return $this->hasMany(Businessunit::class);
    }
    public function accountusers() {
        return $this->hasMany(Accountuser::class);
    }
}
