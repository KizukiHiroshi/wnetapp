<?php

namespace App\Models\Zero;

use Illuminate\Database\Eloquent\Model;
use App\ValidateTrait;

class Oldsql extends Model
{
    use ValidateTrait;
    protected $guarded = [];
    static $tablecomment = '旧SQL';
    static $modelzone = 'システム開発';
    static $defaultsort = [];
    static $referencedcolumns = [];
    static $uniquekeys = [];
}

