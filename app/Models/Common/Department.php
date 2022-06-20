<?php

namespace App\Models\Common;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Validation\Rule;
use App\ValidateTrait;
use App\Models\Common\Company;

class Department extends Model
{
    use SoftDeletes;
    use ValidateTrait;

    public function companies(){
        return $this->belongsTo(Company::class)->withDefault();
    }
    protected $guarded = [];
    static $tablecomment = '部門';
    static $modelzone = '共通';
    static $defaultsort = [
        'code' => 'asc',
    ];
    static $referencedcolumns = [
        'code', 'name', 
    ];
    static $uniquekeys = [
        ['code'], ['name'], 
    ];
     public function businessunits(){
        return $this->hasMany(Businessunit::class);
    }
    protected function rules(){
        return [
            'company_id' => ['required','integer','numeric',],
            'code' => ['required','string','max:4',
                Rule::unique('departments')->ignore($this->id),'regex:/[0-9]{4}/'],
            'name' => ['required','string','max:30',
                Rule::unique('departments')->ignore($this->id),'regex:/[^\x01-\x7E\uFF61-\uFF9F]/'],
            'name_short' => ['required','string','max:10','regex:/[^\x01-\x7E\uFF61-\uFF9F]/'],
            'department_hierarchy' => ['required','integer','numeric',],
            'departmentpath' => ['required','string','max:50','regex:/^[0-9^]+$/'],
            'start_on' => ['nullable','date',],
            'end_on' => ['nullable','date',],
        ];
    }
}
