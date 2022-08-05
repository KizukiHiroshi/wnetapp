<?php

namespace App\Models\Common;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Validation\Rule;
use App\ValidateTrait;

use App\Models\Common\User;
use App\Models\Common\Businessunit;
use App\Models\Common\Employtype;

class Member extends Model
{
    use SoftDeletes;
    use ValidateTrait;
    public function users(){
        return $this->belongsTo(User::class)->withDefault();
    }
    public function businessunits(){
        return $this->belongsTo(Businessunit::class)->withDefault();
    }
    public function employtypes(){
        return $this->belongsTo(Employtype::class)->withDefault();
    }
    protected $guarded = [];
    static $tablecomment = '従業員';
    static $modelzone = '共通';
    static $defaultsort = [
        'businessunit_id' => 'asc',
        'employtype_id' => 'asc',
    ];
    static $referencedcolumns = [
        'code', 
        'name_sei', 
        'name_mei', 
    ];
    static $uniquekeys = [
       ['code'], 
    ];

    // input has_many clause here

    protected function rules()
    {
        return [
            'user_id' => ['required','integer','numeric',],
            'businessunit_id' => ['required','integer','numeric',],
            'employtype_id' => ['required','integer','numeric',],
            'code' => ['required','string','max:10','regex:/^[a-zA-Z0-9]+$/'],
            'name_sei' => ['required','string','max:10','regex:/[^\x01-\x7E\uFF61-\uFF9F]/'],
            'name_mei' => ['required','string','max:10','regex:/[^\x01-\x7E\uFF61-\uFF9F]/'],
            'name_kana' => ['required','string','max:20','regex:/^[ァ-ンヴー]+$/'],
            'name_short' => ['required','string','max:12','regex:/[^\x01-\x7E\uFF61-\uFF9F()]/'],
            'password' => ['required','string','max:16','regex:/^[a-zA-Z0-9-_]+$/'],
            'email' => ['nullable','string','max:50','email'],
            'hourlywage' => ['nullable','integer','numeric',],
            'start_fulltime_on' => ['nullable','date',],
            'start_2nd_on' => ['nullable','date',],
            'businessunit_id_2nd' => ['nullable','integer','numeric',],
            'employtype_id_2nd' => ['nullable','integer','numeric',],
            'hourlywage_2nd' => ['nullable','integer','numeric',],
            'start_on' => ['nullable','date',],
            'end_on' => ['nullable','date',],
        ];
    }
}
