<?php

namespace App\Models\Common;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Validation\Rule;
use App\ValidateTrait;

use App\Models\Common\Company;
use App\Models\Common\User;
use App\Models\Common\Jobtype;

class Account extends Model
{
    use SoftDeletes;
    use ValidateTrait;
    public function companies() {
        return $this->belongsTo(Company::class)->withDefault();
    }
    public function users() {
        return $this->belongsTo(User::class)->withDefault();
    }
    public function jobtypes() {
        return $this->belongsTo(Jobtype::class)->withDefault();
    }
    protected $guarded = [];
    static $tablecomment = 'アカウント';
    static $modelzone = '共通';
    static $defaultsort = [
        'user_id' => 'asc',
        'jobtype_id' => 'asc',
    ];
    static $referencedcolumns = [
        'user_id', 
        'jobtype_id', 
    ];
    static $uniquekeys = [
        ['company_id', 'user_id', 'jobtype_id', ]
    ];

    protected function rules()
    {
        return [
            'company_id' => ['required','integer','numeric',Rule::unique('accounts')->ignore($this->id),],
            'user_id' => ['required','integer','numeric',
                Rule::unique('accounts')->ignore($this->id)->where(function($query) {
                    $query->where('company_id', $this->company_id);
                }),],
            'jobtype_id' => ['required','integer','numeric',
                Rule::unique('accounts')->ignore($this->id)->where(function($query) {
                    $query->where('company_id', $this->company_id)
                     ->where('user_id', $this->user_id);
                }),],
            'self_accesslevel' => ['required','integer','numeric','between:0,5'],
            'unit_accesslevel' => ['required','integer','numeric','between:0,5'],
            'department_accesslevel' => ['required','integer','numeric','between:0,5'],
            'company_accesslevel' => ['required','integer','numeric','between:0,5'],
            'system_accesslevel' => ['required','integer','numeric','between:0,5'],
            'start_on' => ['nullable','date',],
            'end_on' => ['nullable','date',],
        ];
    }
}
