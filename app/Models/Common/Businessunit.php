<?php

namespace App\Models\Common;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Validation\Rule;
use App\ValidateTrait;

use App\Models\Common\Department;

class Businessunit extends Model
{
    use SoftDeletes;
    use ValidateTrait;
    public function departments() {
        return $this->belongsTo(Department::class)->withDefault();
    }
    protected $guarded = [];
    static $tablecomment = '事業所';
    static $modelzone = '共通';
    static $defaultsort = [
        'code' => 'asc',
    ];
    static $referencedcolumns = [
        'code', 
        'name', 
    ];
    static $uniquekeys = [
       'code', 'name', 
    ];

    protected function rules()
    {
        return [
            'department_id' => ['required','integer','numeric',],
            'code' => ['required','string','max:5',Rule::unique('businessunits')->ignore($this->input('id')),'regex:/[0-9]{5}/'],
            'name' => ['required','string','max:30',Rule::unique('businessunits')->ignore($this->input('id')),'regex:/[^\x01-\x7E\uFF61-\uFF9F]/'],
            'name_short' => ['required','string','max:10','regex:/[^\x01-\x7E\uFF61-\uFF9F]/'],
            'postalcode' => ['required','string','max:8','regex:/[0-9]{3}-?[0-9]{4}/'],
            'address1' => ['required','string','max:40',],
            'address2' => ['nullable','string','max:40',],
            'telno' => ['required','string','max:13','regex:/^[a-zA-Z0-9-]+$/'],
            'foxno' => ['nullable','string','max:13','regex:/^[a-zA-Z0-9-]+$/'],
            'url' => ['nullable','string','max:100','url'],
            'email' => ['nullable','string','max:50','email'],
            'start_on' => ['nullable','date',],
            'end_on' => ['nullable','date',],
        ];
    }
}
