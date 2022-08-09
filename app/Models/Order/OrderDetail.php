<?php

namespace App\Models\Order;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Validation\Rule;
use App\Traits\ValidateTrait;

use App\Models\Common\Orderlabel;
use App\Models\Common\Productitem;

class OrderDetail extends Model
{
    use SoftDeletes;
    use ValidateTrait;
    public function orderlabels(){
        return $this->belongsTo(Orderlabel::class)->withDefault();
    }
    public function productitems(){
        return $this->belongsTo(Productitem::class)->withDefault();
    }
    protected $guarded = [];
    static $tablecomment = '発注明細';
    static $modelzone = '発注入荷';
    static $defaultsort = [
        'orderlabel_id' => 'asc',
        'detail_no' => 'asc',
    ];
    static $referencedcolumns = [
        'orderlabel_id', 
        'detail_no', 
        'productitem_id', 
        'price', 
        'quantity', 
    ];
    static $uniquekeys = [
       ['orderlabel_id', 'detail_no', ]
    ];

    // input has_many clause here

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
