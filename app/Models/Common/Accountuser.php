<?php

namespace App\Models\Common;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

use Illuminate\Validation\Rule;
use App\ValidateTrait;

use App\Models\Common\User;
use App\Models\Common\Department;
use App\Models\Common\Businessunit;

class Accountuser extends Model
{
    use SoftDeletes;
    use ValidateTrait;
    public function users() {
        return $this->belongsTo(User::class)->withDefault();
    }
    public function departments() {
        return $this->belongsTo(Department::class)->withDefault();
    }
    public function businessunits() {
        return $this->belongsTo(Businessunit::class)->withDefault();
    }
    protected $guarded = [];
    static $tablecomment = 'アカウントユーザー';
    static $modelzone = '共通';
    static $defaultsort = [
        'department_id' => 'asc',
        'businessunit_id' => 'asc',
    ];
    static $referencedcolumns = [
        'department_id', 
        'businessunit_id', 
        'name', 
    ];
    static $uniquekeys = [
       'user_id', 'name', 
    ];

    protected function rules()
    {
        return [
            'user_id' => ['required','integer','numeric',Rule::unique('accountusers')->ignore($this->id),],
            'name' => ['required','string','max:12',
            Rule::unique('accountusers')->ignore($this->id)->where(function($query) {
                $query->where('user_id', $this->user_id);
            }),],
            'password' => ['required','string','max:16','regex:/^[a-zA-Z0-9-_]+$/'],
            'department_id' => ['required','integer','numeric',],
            'businessunit_id' => ['required','integer','numeric',],
            'start_on' => ['nullable','date',],
            'end_on' => ['nullable','date',],
        ];
    }
}
