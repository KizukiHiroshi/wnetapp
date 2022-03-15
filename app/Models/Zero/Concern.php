<?php

namespace App\Models\Zero;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\Common\Jobtype;

class Concern extends Model
{
    use SoftDeletes;
    public function jobtypes() {
        return $this->belongsTo(Jobtype::class)->withDefault();
    }
    protected $guarded = [];
    static $tablecomment = 'どうする';
    static $modelzone = 'システム開発';
    static $defaultsort = [
        'jobtype_id' => 'asc',
        'name' => 'asc',
        'is_solved' => 'asc',
        'importance' => 'asc',
        'priority' => 'asc',
    ];
    static $referencedcolumns = [
        'name', 
    ];
    static $uniquekeys = [
        'name', 
    ]; 
}
