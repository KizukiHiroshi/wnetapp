<?php

namespace App\Models\Work;

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
    static $modelzone = '労務管理';
    static $defaultsort = [
        'code' => 'asc',
    ];
    static $referencedcolumns = [
        'code', 
        'name', 
    ];
    static $uniquekeys = [
        ['code'], 
    ];

    protected function rules()
    {
        return [
            'code' => ['required','string','max:2',Rule::unique('employtypes')->ignore($this->id),'regex:/\d{2}/'],
            'name' => ['required','string','max:20','regex:/[^\x01-\x7E\uFF61-\uFF9F]/'],
        ];
    }
}
