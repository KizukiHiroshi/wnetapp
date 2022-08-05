<?php

namespace App\Models\Common;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Validation\Rule;
use App\ValidateTrait;

use App\Models\Common\Product;

class Productitem extends Model
{
    use SoftDeletes;
    use ValidateTrait;
    public function products(){
        return $this->belongsTo(Product::class)->withDefault();
    }
    protected $guarded = [];
    static $tablecomment = '商品アイテム';
    static $modelzone = '共通';
    static $defaultsort = [
        'product_id' => 'asc',
        'name_kana' => 'asc',
        'code' => 'asc',
    ];
    static $referencedcolumns = [
        'product_id', 
        'code', 
        'name', 
    ];
    static $uniquekeys = [
       ['code'], 
    ];

    // input has_many clause here
    public function getorder_details(){
        return $this->hasMany(GetorderDetail::class);
    }
    public function order_details(){
        return $this->hasMany(OrderDetail::class);
    }
    public function ryutu_stocks(){
        return $this->hasMany(RyutuStock::class);
    }
    public function stocks(){
        return $this->hasMany(Stock::class);
    }

    protected function rules()
    {
        return [
            'product_id' => ['required','integer','numeric',],
            'code' => ['required','string','max:13','regex:/\d{13}/'],
            'jancode' => ['nullable','string','max:13','regex:/\d{13}/'],
            'prdcode' => ['nullable','string','max:20','regex:/^[a-zA-Z0-9- ]+$/'],
            'name' => ['required','string','max:60',],
            'name_kana' => ['nullable','string','max:60',],
            'color' => ['nullable','string','max:40',],
            'size' => ['nullable','string','max:20',],
            'is_janprinted' => ['nullable','boolean',],
            'pricelabel_opt' => ['nullable','integer','numeric',],
            'unit' => ['required','integer','numeric',],
            'unitname_opt' => ['nullable','integer','numeric',],
            'regularprice' => ['required','integer','numeric',],
            'regularprice_2nd' => ['nullable','integer','numeric',],
            'start_2nd_on' => ['nullable','date',],
            'url' => ['nullable','string','max:100','url'],
            'image' => ['nullable','string','max:100',],
            'remark' => ['nullable','string','max:255',],
            'start_on' => ['nullable','date',],
            'end_on' => ['nullable','date',],
        ];
    }
}
