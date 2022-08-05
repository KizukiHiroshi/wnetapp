<?php

namespace App\Models\Base;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Validation\Rule;
use App\ValidateTrait;


class OptionChoice extends Model
{
    use SoftDeletes;
    use ValidateTrait;
    protected $guarded = [];
    static $tablecomment = 'オプション選択肢';
    static $modelzone = '共通基礎';
    static $defaultsort = [
        'variablename' => 'asc',
        'no' => 'asc',
    ];
    static $referencedcolumns = [
        'variablename', 
    ];
    static $uniquekeys = [
       ['variablename_system', 'valuename_system', ]
    ];

    // input has_many clause here

    protected function rules()
    {
        return [
            'variablename' => ['required','string','max:30',],
            'variablename_system' => ['required','string','max:30',],
            'no' => ['required','integer','numeric',],
            'valuename' => ['required','string','max:30',],
            'valuename_system' => ['required','string','max:30',
                Rule::unique('option_choices')->ignore($this->id)->where(function($query){
                    $query->where('variablename_system', $this->variablename_system);
                }),],
            'remarks' => ['nullable','string','max:255',],
            'start_on' => ['nullable','date',],
            'end_on' => ['nullable','date',],
        ];
    }
}
