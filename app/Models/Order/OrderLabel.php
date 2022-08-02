<?php

namespace App\Models\Order;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Validation\Rule;
use App\Rules\Jancode;
use App\ValidateTrait;

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
        'order-company_id', 
        'order-businessunit_id', 
        'getorder-company_id', 
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
            'order_no' => ['required','string','max:13','regex:/\d{13}/', new Jancode],
            'order_on' => ['required','date',],
            'order-company_id' => ['required','integer','numeric',],
            'order-businessunit_id' => ['required','integer','numeric',],
            'getorder-company_id' => ['required','integer','numeric',],
            'getorder-businessunit_id' => ['required','integer','numeric',],
            'need_deliverydate' => ['required','boolean',],
            'due_date' => ['nullable','date',],
            'regularprice_total' => ['required','integer','numeric',],
            'price_total' => ['required','integer','numeric',],
            'tax_total' => ['required','integer','numeric',],
            'delivery-businessunit_id' => ['required','integer','numeric',],
            'published_on' => ['nullable','date',],
            'is_recieved' => ['required','boolean',],
            'remark' => ['nullable','string',],
            'is_completed' => ['required','boolean',],
            'transaction_no' => ['required','integer','numeric',],
            'old13id' => ['nullable','integer','numeric',],
            'old14id' => ['nullable','integer','numeric',],
        ];
    }
}
