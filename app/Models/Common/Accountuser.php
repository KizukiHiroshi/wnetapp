<?php

namespace App\Models\Common;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Accountuser extends Model
{
    use SoftDeletes;
    protected $guarded = [];
    static $tablecomment = 'アカウントユーザー';
    static $modelzone = '共通';
    static $defaultsort = [
        'company_id' => 'asc',
        'department_id' => 'asc',
        'businessunit_id' => 'asc',
    ];
    static $referencedcolumns = [
       'businessunit_id', 
       'name', 
    ];
    static $uniquekeys = [];

    public function users() {
        return $this->belongsTo(User::class)->withDefault();
    }
    public function companies() {
        return $this->belongsTo(Company::class)->withDefault();
    }
    public function departments() {
        return $this->belongsTo(Department::class)->withDefault();
    }
    public function businessunits() {
        return $this->belongsTo(Businessunit::class)->withDefault();
    }
    public function accountauthorities() {
        return $this->hasMany(Accountauthority::class);
    }

}
