<?php

namespace App\Models\Common;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Validation\Rule;
use App\ValidateTrait;

use App\Models\Common\Brand;

class Product extends Model
{
    use SoftDeletes;
    use ValidateTrait;
    public function brands(){
        return $this->belongsTo(Brand::class)->withDefault();
    }
    protected $guarded = [];
    static $tablecomment = '商品';
    static $modelzone = '共通';
    static $defaultsort = [
        'brand_id' => 'asc',
        'name_kana' => 'asc',
    ];
    static $referencedcolumns = [
        'brand_id', 
        'name', 
    ];
    static $uniquekeys = [
       ['brand_id', 'name', ]
    ];

    // input has_many clause here
    public function productitems() {
        return $this->hasMany(Productitem::class);
    }

    protected function rules()
    {
        return [
            'brand_id' => ['required','integer','numeric',],
            'name' => ['required','string','max:40',
                Rule::unique('products')->ignore($this->id)->where(function($query){
                    $query->where('brand_id', $this->brand_id);
                })
            ],
            'name_kana' => ['nullable','string','max:70',],
            'url' => ['nullable','string','max:100','url'],
            'image' => ['nullable','string','max:100',],
            'remark' => ['nullable','string','max:255',],
            'start_on' => ['nullable','date',],
            'end_on' => ['nullable','date',],
        ];
    }
}
