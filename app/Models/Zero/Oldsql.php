<?php

namespace App\Models\Zero;

use Illuminate\Database\Eloquent\Model;

class Oldsql extends Model
{
    protected $guarded = [];
    static $tablecomment = '旧SQL';
    static $modelzone = 'システム開発';
    static $defaultsort = [
    ];
    static $referencedcolumns = [];
    static $uniquekeys = [];
}

