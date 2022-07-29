<?php

namespace App\Models\Inventory;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Validation\Rule;
use App\ValidateTrait;

use App\Models\Common\Businessunit;
use App\Models\Common\Productitem;
use App\Models\Common\Ryutustockshell;

class RyutuStock extends Model
{
    use SoftDeletes;
    use ValidateTrait;
    public function businessunits(){
        return $this->belongsTo(Businessunit::class)->withDefault();
    }
    public function productitems(){
        return $this->belongsTo(Productitem::class)->withDefault();
    }
    public function ryutu_stockshells(){
        return $this->belongsTo(Ryutustockshell::class)->withDefault();
    }
    protected $guarded = [];
    static $tablecomment = '流通在庫';
    static $modelzone = '在庫管理';
    static $defaultsort = [
        'businessunit_id' => 'asc',
        'ryutustockshell_id' => 'asc',
        'ryutustockshellno' => 'asc',
        'productitem_id' => 'asc',
    ];
    static $referencedcolumns = [
        'businessunit_id', 
        'productitem_id', 
        'ryutustockshell_id', 
        'ryutustockshellno', 
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
                Rule::unique('ryutu_stocks')->ignore($this->id)->where(function($query){
                    $query->where('businessunit_id', $this->businessunit_id);
                }),],
            'ryutustockshell_id' => ['required','integer','numeric',],
            'ryutustockshellno' => ['nullable','integer','numeric',],
            'ryutustockshell_id_2nd' => ['required','integer','numeric',],
            'ryutustockshellno2' => ['nullable','integer','numeric',],
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
