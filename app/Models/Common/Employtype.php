<?php

namespace App\Models\Common;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Validation\Rule;
use App\ValidateTrait;


class Employtype extends Model
{
    use SoftDeletes;
    use ValidateTrait;
    protected $guarded = [];
    static $tablecomment = '雇用形態';
    static $modelzone = '共通';
    static $defaultsort = [
        'code' => 'asc',
    ];
    static $referencedcolumns = [
        'code', 
        'name', 
    ];
    static $uniquekeys = [
        ['code'], ['name'], 
    ];

    protected function rules()
    {
        return [
            'code' => ['required','string','max:2',Rule::unique('employtypes')->ignore($this->id),'regex:/\d{2}/'],
            'name' => ['required','string','max:20',Rule::unique('employtypes')->ignore($this->id),'regex:/[^\x01-\x7E\uFF61-\uFF9F]/'],
        ];
    }
}
