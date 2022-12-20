<?php

namespace App\Models\Order;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Validation\Rule;
use App\Traits\ValidateTrait;

use App\Models\Common\Company;
use App\Models\Common\Businessunit;

class OrderLabel extends Model
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
    static $tablecomment = '発注';
    static $modelzone = '発注入荷';
    static $defaultsort = [
        'order_on' => 'asc',
        'order_no' => 'asc',
    ];
    static $referencedcolumns = [
        'order_on', 
        'order_no', 
        'order__company_id', 
        'order__businessunit_id', 
    ];
    static $uniquekeys = [
       ['order_no'], 
    ];

    // input has_many clause here
    public function order_details(){
        return $this->hasMany(OrderDetail::class);
    }

    protected function rules()
    {
        return [
            'order_no' => ['required','string','max:13',],
            'order_on' => ['required','date',],
            'order__company_id' => ['required','integer','numeric',
                Rule::unique('order_labels')->ignore($this->id)->where(function($query){
                    $query->where('order_no', $this->order_no);
                }),],
            'order__businessunit_id' => ['required','integer','numeric',],
            'getorder__company_id' => ['required','integer','numeric',],
            'getorder__businessunit_id' => ['required','integer','numeric',],
            'need_deliverydate' => ['required','boolean',],
            'due_date' => ['nullable','date',],
            'detail_count' => ['required','integer','numeric',],
            'regularprice_total' => ['required','integer','numeric',],
            'price_total' => ['required','integer','numeric',],
            'tax_total' => ['required','integer','numeric',],
            'delivery__businessunit_id' => ['required','integer','numeric',],
            'is_recieved' => ['required','boolean',],
            'published_on' => ['nullable','date',],
            'remark' => ['nullable','string','max:255',],
            'is_completed' => ['required','boolean',],
            'alltransaction_no' => ['required','integer','numeric',],
            'old13id' => ['nullable','integer','numeric',],
            'old14id' => ['nullable','integer','numeric',],
        ];
    }
}
