<?php

namespace App\Models\Order;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Validation\Rule;
use App\ValidateTrait;

use App\Models\Common\Businessunit;

class OrderUnit extends Model
{
    use SoftDeletes;
    use ValidateTrait;
    public function businessunits(){
        return $this->belongsTo(Businessunit::class)->withDefault();
    }
    protected $guarded = [];
    static $tablecomment = '発注事業所';
    static $modelzone = '発注入荷';
    static $defaultsort = [
        'businessunit_id' => 'asc',
    ];
    static $referencedcolumns = [
        'businessunit_id', 
    ];
    static $uniquekeys = [
       ['businessunit_id'], 
    ];

    // input has_many clause here

    protected function rules()
    {
        return [
            'businessunit_id' => ['required','integer','numeric',],
            'getorderdayofweek' => ['nullable','string','max:7',],
            'shippingdayofweek' => ['nullable','string','max:7',],
            'remarks' => ['nullable','string','max:200',],
            'start_on' => ['nullable','date',],
            'end_on' => ['nullable','date',],
        ];
    }
}
