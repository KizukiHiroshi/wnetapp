<?php

namespace App\Models\Getorder;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Validation\Rule;
use App\ValidateTrait;

use App\Models\Common\Company;
use App\Models\Common\Businessunit;

class GetorderLabel extends Model
{
    use SoftDeletes;
    use ValidateTrait;
    public function companies(){
        return $this->belongsTo(Company::class)->withDefault();
    }
    public function businessunits(){
        return $this->belongsTo(Businessunit::class)->withDefault();
    }
    protected $guarded = [];
    static $tablecomment = '受注';
    static $modelzone = '受注出荷';
    static $defaultsort = [
        'getorder_on' => 'asc',
        'getorder_no' => 'asc',
    ];
    static $referencedcolumns = [
        'getorder_on', 
        'getorder_no', 
        'getorder__company_id', 
        'getorder__businessunit_id', 
        'order__company_id', 
    ];
    static $uniquekeys = [
       ['getorder_no'], 
    ];

    // input has_many clause here

    protected function rules()
    {
        return [
            'getorder_no' => ['required','string','max:13',],
            'getorder_on' => ['required','date',],
            'getorder__company_id' => ['required','integer','numeric',],
            'getorder__businessunit_id' => ['required','integer','numeric',],
            'order__company_id' => ['required','integer','numeric',],
            'order__businessunit_id' => ['required','integer','numeric',],
            'guestorder_no' => ['nullable','string','max:20',],
            'need_deliverydate' => ['required','boolean',],
            'due_date' => ['nullable','date',],
            'regularprice_total' => ['required','integer','numeric',],
            'price_total' => ['required','integer','numeric',],
            'tax_total' => ['required','integer','numeric',],
            'delivery__businessunit_id' => ['required','integer','numeric',],
            'is_fixed' => ['required','boolean',],
            'published_on' => ['nullable','date',],
            'estimate_no' => ['nullable','string','max:13',],
            'remark' => ['nullable','string','max:255',],
            'is_completed' => ['required','boolean',],
            'transaction' => ['required','integer','numeric',],
            'old13id' => ['nullable','integer','numeric',],
            'old14id' => ['nullable','integer','numeric',],
        ];
    }
}
