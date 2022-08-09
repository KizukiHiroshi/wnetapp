<?php

namespace App\Models\Inventory;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Validation\Rule;
use App\Traits\ValidateTrait;

use App\Models\Common\Businessunit;
use App\Models\Common\Productitem;
use App\Models\Common\Stockshell;

class Stock extends Model
{
    use SoftDeletes;
    use ValidateTrait;
    public function businessunits(){
        return $this->belongsTo(Businessunit::class)->withDefault();
    }
    public function productitems(){
        return $this->belongsTo(Productitem::class)->withDefault();
    }
    public function stockshells(){
        return $this->belongsTo(Stockshell::class)->withDefault();
    }
    protected $guarded = [];
    static $tablecomment = '在庫';
    static $modelzone = '在庫管理';
    static $defaultsort = [
        'businessunit_id' => 'asc',
        'stockshell_id' => 'asc',
        'stockshellno' => 'asc',
        'productitem_id' => 'asc',
    ];
    static $referencedcolumns = [
        'businessunit_id', 
        'productitem_id', 
        'stockshell_id', 
        'stockshellno', 
    ];
    static $uniquekeys = [
       ['businessunit_id', 'productitem_id', ]
    ];

    // input has_many clause here

    protected function rules()
    {
        return [
            'businessunit_id' => ['required','integer','numeric',],
            'productitem_id' => ['required','integer','numeric',
                Rule::unique('stocks')->ignore($this->id)->where(function($query){
                    $query->where('businessunit_id', $this->businessunit_id);
                }),],
            'stockshell_id' => ['required','integer','numeric',],
            'stockshellno' => ['nullable','integer','numeric',],
            'stockshell_id_2nd' => ['required','integer','numeric',],
            'stockshellno2' => ['nullable','integer','numeric',],
            'currentstock' => ['required','integer','numeric',],
            'stockstatus_opt' => ['required','integer','numeric',],
            'is_autoreorder' => ['required','boolean',],
            'reorderpoint' => ['nullable','integer','numeric',],
            'maxstock' => ['nullable','integer','numeric',],
            'stockupdeted_on' => ['nullable','date',],
            'remark' => ['nullable','string','max:255',],
            'start_on' => ['nullable','date',],
            'end_on' => ['nullable','date',],
        ];
    }
}
