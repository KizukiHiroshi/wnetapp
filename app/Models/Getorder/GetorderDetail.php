<?php

namespace App\Models\Getorder;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Validation\Rule;
use App\Traits\ValidateTrait;

use App\Models\Common\Getorderlabel;
use App\Models\Common\Productitem;

class GetorderDetail extends Model
{
    use SoftDeletes;
    use ValidateTrait;
    public function getorder_labels(){
        return $this->belongsTo(Getorderlabel::class)->withDefault();
    }
    public function productitems(){
        return $this->belongsTo(Productitem::class)->withDefault();
    }
    protected $guarded = [];
    static $tablecomment = '受注明細';
    static $modelzone = '受注出荷';
    static $defaultsort = [
        'getorder_label_id' => 'asc',
        'detail_no' => 'asc',
    ];
    static $referencedcolumns = [
        'getorder_label_id', 
        'detail_no', 
        'productitem_id', 
        'price', 
        'quantity', 
    ];
    static $uniquekeys = [
       ['getorder_label_id', 'detail_no', ]
    ];

    // input has_many clause here

    protected function rules()
    {
        return [
            'getorder_label_id' => ['required','integer','numeric',],
            'detail_no' => ['required','integer','numeric',
                Rule::unique('getorder_details')->ignore($this->id)->where(function($query){
                    $query->where('getorder_label_id', $this->getorder_label_id);
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
            'alltransaction_no' => ['required','integer','numeric',],
            'old13id' => ['nullable','integer','numeric',],
            'old14id' => ['nullable','integer','numeric',],
        ];
    }

}
