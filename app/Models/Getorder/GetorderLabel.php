<?php

namespace App\Models\Getorder;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Validation\Rule;
use App\Traits\ValidateTrait;

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
    public function getorder_details(){
        return $this->hasMany(GetorderDetail::class);
    }

    protected function rules()
    {
        return [
            'getorderlabel_id' => ['required','integer','numeric',],
            'detail_no' => ['required','integer','numeric',
                Rule::unique('getorder_details')->ignore($this->id)->where(function($query){
                    $query->where('getorderlabel_id', $this->getorderlabel_id);
                }),],
            'productitem_id' => ['required','integer','numeric',],
            'regularprice' => ['required','integer','numeric',],
            'price' => ['required','integer','numeric',],
            'quantity' => ['required','integer','numeric',],
            'taxrate' => ['required','integer','numeric',],
            'is_fixed' => ['required','boolean',],
            'remark' => ['nullable','string','max:255',],
            'discount_amount' => ['required','integer','numeric',],
            'allocation_quantity' => ['required','integer','numeric',],
            'available_quantity' => ['required','integer','numeric',],
            'is_completed' => ['required','boolean',],
            'transaction_no' => ['required','integer','numeric',],
            'old13id' => ['nullable','integer','numeric',],
            'old14id' => ['nullable','integer','numeric',],
        ];
    }

}
