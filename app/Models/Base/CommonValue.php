<?php

namespace App\Models\Base;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Validation\Rule;
use App\ValidateTrait;


class CommonValue extends Model
{
    use SoftDeletes;
    use ValidateTrait;
    protected $guarded = [];
    static $tablecomment = '共通変数';
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
    public function concerns(){
        return $this->hasMany(Concern::class);
    }

    protected function rules()
    {
        return [
            'name' => ['required','string','max:30',],
            'name_system' => ['required','string','max:30',],
            'value' => ['required','string','max:255',],
            'value_2nd' => ['nullable','string','max:255',],
            'start_2nd_on' => ['nullable','date',],
            'remarks' => ['nullable','string','max:200',],
            'start_on' => ['nullable','date',],
            'end_on' => ['nullable','date',],
        ];
    }
}
