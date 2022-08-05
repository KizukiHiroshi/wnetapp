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

    // input has_many clause here
    public function members(){
        return $this->hasMany(Member::class);
    }

    protected function rules()
    {
        return [
            'code' => ['required','string','max:2',Rule::unique('employtypes')->ignore($this->id),'regex:/\d{2}/'],
            'name' => ['required','string','max:20',Rule::unique('employtypes')->ignore($this->id),'regex:/[^\x01-\x7E\uFF61-\uFF9F]/'],
            'start_on' => ['nullable','date',],
            'end_on' => ['nullable','date',],
        ];
    }
}
