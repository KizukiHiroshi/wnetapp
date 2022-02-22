<?php

namespace App\Models\Common;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\Common\Company;

class Department extends Model
{
    use SoftDeletes;
    public function companies() {
        return $this->belongsTo(Company::class)->withDefault();
    }
    protected $guarded = [];
    static $tablecomment = '部門';
    static $modelzone = '共通';
    static $defaultsort = [
        'code' => 'asc',
    ];
    static $referencedcolumns = [
       'code', 
       'name', 
    ];
    public function businessunits() {
        return $this->hasMany(Businessunit::class);
    }
    public function accountusers() {
        return $this->hasMany(Accountuser::class);
    }

}
