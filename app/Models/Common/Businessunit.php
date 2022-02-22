<?php

namespace App\Models\Common;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\Common\Company;
use App\Models\Common\Department;

class Businessunit extends Model
{
    use SoftDeletes;

    protected $guarded = [];
    static $tablecomment = '事業所';
    static $modelzone = '共通';
    static $defaultsort = [
        'code' => 'asc',
    ];
    static $referencedcolumns = [
       'code', 
       'name', 
    ];
    
    public function companies() {
        return $this->belongsTo(Company::class)->withDefault();
    }
    public function departments() {
        return $this->belongsTo(Department::class)->withDefault();
    }
    public function accountusers() {
        return $this->hasMany(Accountuser::class);
    }

}
