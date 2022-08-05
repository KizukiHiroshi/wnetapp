<?php

namespace App\Models\Common;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Validation\Rule;
use App\ValidateTrait;

use App\Models\Common\Company;

class Businessunit extends Model
{
    use SoftDeletes;
    use ValidateTrait;
    public function companies(){
        return $this->belongsTo(Company::class)->withDefault();
    }
    protected $guarded = [];
    static $tablecomment = '事業所';
    static $modelzone = '共通';
    static $defaultsort = [
        'code' => 'asc',
    ];
    static $referencedcolumns = [
        'company_id', 
        'code', 
        'name', 
    ];
    static $uniquekeys = [
       ['company_id', 'code', ]
    ];

    // input has_many clause here
    public function getorder_labels(){
        return $this->hasMany(GetorderLabel::class);
    }
    public function members(){
        return $this->hasMany(Member::class);
    }
    public function order_labels(){
        return $this->hasMany(OrderLabel::class);
    }
    public function ryutu_stocks(){
        return $this->hasMany(RyutuStock::class);
    }
    public function ryutu_stockshells(){
        return $this->hasMany(RyutuStockshell::class);
    }
    public function stockshells(){
        return $this->hasMany(Stockshell::class);
    }

    protected function rules()
    {
        return [
            'company_id' => ['required','integer','numeric',],
            'code' => ['required','string','max:5',
                Rule::unique('businessunits')->ignore($this->id)->where(function($query){
                    $query->where('company_id', $this->company_id);
                }),'regex:/[0-9]{5}/'],
            'name' => ['required','string','max:30','regex:/[^\x01-\x7E\uFF61-\uFF9Fa-zA-Z0-9-]/'],
            'name_short' => ['required','string','max:10','regex:/[^\x01-\x7E\uFF61-\uFF9F]/'],
            'postalcode' => ['required','string','max:8','regex:/[0-9]{3}-?[0-9]{4}/'],
            'address1' => ['required','string','max:40',],
            'address2' => ['nullable','string','max:40',],
            'telno' => ['required','string','max:13','regex:/^[a-zA-Z0-9-]+$/'],
            'faxno' => ['nullable','string','max:13','regex:/^[a-zA-Z0-9-]+$/'],
            'url' => ['nullable','string','max:100','url'],
            'email' => ['nullable','string','max:50','email'],
            'start_on' => ['nullable','date',],
            'end_on' => ['nullable','date',],
        ];
    }
}
