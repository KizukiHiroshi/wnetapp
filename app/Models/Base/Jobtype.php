<?php

namespace App\Models\Base;

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
    static $modelzone = '共通基礎';
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
    public function concerns() {
        return $this->hasMany(Concern::class);
    }
    public function accountauthorities() {
        return $this->hasMany(Accountauthority::class);
    }
    public function option_choices(){
        return $this->hasMany(OptionChoice::class);
    }

    protected function rules()
    {
        return [
            'code' => ['required','string','max:3',Rule::unique('jobtypes')->ignore($this->id),'regex:/[0-9]{3}/'],
            'name' => ['required','string','max:20','regex:/[^\x01-\x7E\uFF61-\uFF9F]/'],
            'name_system' => ['required','string','max:40',Rule::unique('jobtypes')->ignore($this->id),'regex:/^[a-zA-Z0-9-_]+$/'],
            'remarks' => ['nullable','string','max:80',],
        ];
    }
}
