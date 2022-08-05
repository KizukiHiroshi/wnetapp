<?php

namespace App\Models\Common;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Validation\Rule;
use App\ValidateTrait;


class Company extends Model
{
    use SoftDeletes;
    use ValidateTrait;
    protected $guarded = [];
    static $tablecomment = '企業';
    static $modelzone = '共通';
    static $defaultsort = [
        'name_kana' => 'asc',
    ];
    static $referencedcolumns = [
        'name', 
        'code', 
    ];
    static $uniquekeys = [
       ['code'], 
    ];

    // input has_many clause here
    public function businessunits(){
        return $this->hasMany(Businessunit::class);
    }
    public function buyer_in_companies(){
        return $this->hasMany(BuyerInCompany::class);
    }
    public function departments(){
        return $this->hasMany(Department::class);
    }
    public function getorder_labels(){
        return $this->hasMany(GetorderLabel::class);
    }
    public function order_labels(){
        return $this->hasMany(OrderLabel::class);
    }
    public function performer_in_companies(){
        return $this->hasMany(PerformerInCompany::class);
    }
    public function vendor_in_companies(){
        return $this->hasMany(VendorInCompany::class);
    }

    protected function rules()
    {
        return [
            'code' => ['required','string','max:4','regex:/\d{4}/'],
            'name' => ['required','string','max:30','regex:/[^\x01-\x7E\uFF61-\uFF9F]/'],
            'name_kana' => ['required','string','max:30','regex:/^[ァ-ンヴーッ]+$/'],
            'name_short' => ['required','string','max:10','regex:/[^\x01-\x7E\uFF61-\uFF9F]/'],
            'postalcode' => ['required','string','max:8','regex:/[0-9]{3}-?[0-9]{4}/'],
            'address1' => ['required','string','max:40',],
            'address2' => ['nullable','string','max:40',],
            'telno' => ['required','string','max:14','regex:/^[a-zA-Z0-9-]+$/'],
            'telno2' => ['nullable','string','max:14','regex:/^[a-zA-Z0-9-]+$/'],
            'faxno' => ['nullable','string','max:14','regex:/^[a-zA-Z0-9-]+$/'],
            'url' => ['nullable','string','max:100','url'],
            'email' => ['nullable','string','max:255','email'],
            'remarks' => ['nullable','string','max:100',],
            'has_businessunit' => ['required','boolean',],
            'is_buyer' => ['required','boolean',],
            'is_vendor' => ['required','boolean',],
            'can_work' => ['required','boolean',],
            'start_on' => ['nullable','date',],
            'end_on' => ['nullable','date',],
        ];
    }
}
