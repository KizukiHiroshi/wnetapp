<?php

namespace App\Models\Getorder;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Validation\Rule;
use App\ValidateTrait;

use App\Models\Common\Getorderlabel;
use App\Models\Common\Productitem;

class GetorderDetail extends Model
{
    use SoftDeletes;
    use ValidateTrait;
    public function getorderlabels(){
        return $this->belongsTo(Getorderlabel::class)->withDefault();
    }
    public function productitems(){
        return $this->belongsTo(Productitem::class)->withDefault();
    }
    protected $guarded = [];
    static $tablecomment = '受注明細';
    static $modelzone = '受注出荷';
    static $defaultsort = [
        'getorderlabel_id' => 'asc',
        'detail_no' => 'asc',
    ];
    static $referencedcolumns = [
        'getorderlabel_id', 
        'detail_no', 
        'productitem_id', 
        'price', 
        'quantity', 
    ];
    static $uniquekeys = [
       ['getorderlabel_id', 'detail_no', ]
    ];

    // input has_many clause here

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
