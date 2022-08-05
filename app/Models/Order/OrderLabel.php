<?php

namespace App\Models\Order;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Validation\Rule;
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
        'order__company_id', 
        'order__businessunit_id', 
        'getorder__company_id', 
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
            'orderlabel_id' => ['required','integer','numeric',],
            'detail_no' => ['required','integer','numeric',
                Rule::unique('order_details')->ignore($this->id)->where(function($query){
                    $query->where('orderlabel_id', $this->orderlabel_id);
                }),],
            'productitem_id' => ['required','integer','numeric',],
            'regularprice' => ['required','integer','numeric',],
            'price' => ['required','integer','numeric',],
            'quantity' => ['required','integer','numeric',],
            'taxrate' => ['required','integer','numeric',],
            'is_fixed' => ['required','boolean',],
            'remark' => ['nullable','string','max:255',],
            'available_quantity' => ['required','integer','numeric',],
            'is_completed' => ['required','boolean',],
            'transaction_no' => ['required','integer','numeric',],
            'old13id' => ['nullable','integer','numeric',],
            'old14id' => ['nullable','integer','numeric',],
        ];
    }
}
