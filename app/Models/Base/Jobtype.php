<?php

namespace App\Models\Zero;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Validation\Rule;
use App\ValidateTrait;


class Jobtype extends Model
{
    use SoftDeletes;
    use ValidateTrait;
    protected $guarded = [];
    static $tablecomment = '業務種類';
    static $modelzone = 'システム開発';
    static $defaultsort = [
        'code' => 'asc',
    ];
    static $referencedcolumns = [
        'code', 'name', 
    ];
    static $uniquekeys = [
       ['code'], ['name_system'], 
    ];

    // input has_many clause here

    protected function rules()
    {
        return [
            'code' => ['required','string','max:3','regex:/[0-9]{3}/'],
            'name' => ['required','string','max:20','regex:/[^\x01-\x7E\uFF61-\uFF9F]/'],
            'name_system' => ['required','string','max:40','regex:/^[a-zA-Z0-9-_]+$/'],
            'remarks' => ['nullable','string','max:80',],
            'start_on' => ['nullable','date',],
            'end_on' => ['nullable','date',],
        ];
    }
}
