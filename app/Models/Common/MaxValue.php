<?php

namespace App\Models\Common;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Validation\Rule;
use App\Traits\ValidateTrait;


class MaxValue extends Model
{
    use SoftDeletes;
    use ValidateTrait;
    protected $guarded = [];
    static $tablecomment = '最大値管理';
    static $modelzone = '共通';
    static $defaultsort = [
        'name' => 'asc',
    ];
    static $referencedcolumns = [
        'name', 
        'value', 
    ];
    static $uniquekeys = [
       ['name'], ['name_system'], 
    ];

    // input has_many clause here

    protected function rules()
    {
        return [
            'name' => ['required','string','max:30',],
            'name_system' => ['required','string','max:30',],
            'value' => ['required','string','max:255',],
            'remarks' => ['nullable','string','max:200',],
            'start_on' => ['nullable','date',],
            'end_on' => ['nullable','date',],
        ];
    }
}
