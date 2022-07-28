<?php

namespace App\Models\Common;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Validation\Rule;
use App\ValidateTrait;

use App\Models\Common\Company;

class PerformerInCompany extends Model
{
    use SoftDeletes;
    use ValidateTrait;
    public function companies(){
        return $this->belongsTo(Company::class)->withDefault();
    }
    protected $guarded = [];
    static $tablecomment = '利用企業';
    static $modelzone = '共通';
    static $defaultsort = [
        'company_id' => 'asc',
    ];
    static $referencedcolumns = [
        'company_id', 
        'fiscalyearstart_on', 
        'personnelyearstart_on', 
    ];
    static $uniquekeys = [
       ['company_id'], 
    ];

    // input has_many clause here

    protected function rules()
    {
        return [
            'company_id' => ['required','integer','numeric',],
            'fiscalyearstart_on' => ['nullable','date',],
            'personnelyearstart_on' => ['nullable','date',],
            'start_on' => ['nullable','date',],
            'end_on' => ['nullable','date',],
        ];
    }
}
