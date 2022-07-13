<?php

namespace App\Models\Base;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Validation\Rule;
use App\ValidateTrait;


class Tempjan extends Model
{
    use SoftDeletes;
    use ValidateTrait;
    protected $guarded = [];
    static $tablecomment = '仮JAN';
    static $modelzone = '共通基礎';
    static $defaultsort = [
        'jancode' => 'asc',
    ];
    static $referencedcolumns = [
        'jancode', 
    ];
    static $uniquekeys = [
       ['jancode'], 
    ];

    // input has_many clause here

    protected function rules()
    {
        return [
            'jancode' => ['required','string',],
            'start_on' => ['nullable','date',],
            'end_on' => ['nullable','date',],
        ];
    }
}
