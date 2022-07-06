<?php

namespace App\Models\Base;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Validation\Rule;
use App\ValidateTrait;

use App\Models\Common\Jobtype;

class OptionChoice extends Model
{
    use SoftDeletes;
    use ValidateTrait;
    public function jobtypes(){
        return $this->belongsTo(Jobtype::class)->withDefault();
    }
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
       ['variablename_systrem', 'valuename_systrem', ]
    ];

    // input has_many clause here

    protected function rules()
    {
        return [
            'variablename' => ['required','string','max:30',],
            'variablename_systrem' => ['required','string','max:30',],
            'no' => ['required','integer','numeric',],
            'valuename' => ['required','string','max:30',],
            'valuename_systrem' => ['required','string','max:30',
                Rule::unique('option_choices')->ignore($this->id)->where(function($query){
                    $query->where('variablename_systrem', $this->variablename_systrem);
                }),],
            'remarks' => ['nullable','string','max:255',],
            'start_on' => ['nullable','date',],
            'end_on' => ['nullable','date',],
        ];
    }
}
