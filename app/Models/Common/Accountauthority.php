<?php

namespace App\Models\Common;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Accountauthority extends Model
{
    use SoftDeletes;
    public function accountusers() {
        return $this->belongsTo(Accountuser::class)->withDefault();
    }
    public function jobtypes() {
        return $this->belongsTo(Jobtype::class)->withDefault();
    }
    protected $guarded = [];
    static $tablecomment = 'アカウント権限';
    static $modelzone = '共通';
    static $defaultsort = [
        'accountuser_id' => 'asc',
    ];
    static $referencedcolumns = [
    ];
}

