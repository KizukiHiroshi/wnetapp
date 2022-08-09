<?php

namespace App\Models\Inventory;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Validation\Rule;
use App\Traits\ValidateTrait;

use App\Models\Common\Businessunit;

class Stockshell extends Model
{
    use SoftDeletes;
    use ValidateTrait;
    public function businessunits(){
        return $this->belongsTo(Businessunit::class)->withDefault();
    }
    protected $guarded = [];
    static $tablecomment = '在庫棚';
    static $modelzone = '在庫管理';
    static $defaultsort = [
        'businessunit_id' => 'asc',
        'code' => 'asc',
    ];
    static $referencedcolumns = [
        'name', 
    ];
    static $uniquekeys = [
       ['businessunit_id', 'code', ]
    ];

    // input has_many clause here
    public function stocks(){
        return $this->hasMany(Stock::class);
    }

    protected function rules()
    {
        return [
            'businessunit_id' => ['required','integer','numeric',],
            'code' => ['required','string','max:20',
                Rule::unique('stockshells')->ignore($this->id)->where(function($query){
                    $query->where('businessunit_id', $this->businessunit_id);
                }),],
            'name' => ['nullable','string','max:60',],
            'remark' => ['nullable','string','max:255',],
            'start_on' => ['nullable','date',],
            'end_on' => ['nullable','date',],
        ];
    }
}
