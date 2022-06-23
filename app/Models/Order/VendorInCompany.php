<?php

namespace App\Models\Order;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Validation\Rule;
use App\ValidateTrait;

use App\Models\Common\Company;

class VendorInCompany extends Model
{
    use SoftDeletes;
    use ValidateTrait;
    public function companies() {
        return $this->belongsTo(Company::class)->withDefault();
    }
    protected $guarded = [];
    static $tablecomment = '仕入先企業';
    static $modelzone = '発注入荷';
    static $defaultsort = [
        'company_id' => 'asc',
    ];
    static $referencedcolumns = [
        'company_id', 'pic', 
    ];
    static $uniquekeys = [
       ['company_id'], 
    ];

    // input has_many clause here

    protected function rules()
    {
        return [
            'company_id' => ['required','integer','numeric',],
            'department' => ['nullable','string','max:30',],
            'position' => ['nullable','string','max:30',],
            'pic' => ['nullable','string','max:20',],
            'telno' => ['nullable','string','max:14','regex:/^[a-zA-Z0-9-]+$/'],
            'faxno' => ['nullable','string','max:14','regex:/^[a-zA-Z0-9-]+$/'],
            'emails' => ['nullable','string','max:200',],
            'orderdayofweek' => ['nullable','string','max:7',],
            'ordertimeonday' => ['nullable','date',],
            'arrivaldayofweek' => ['nullable','string','max:7',],
            'freeshippingquantity' => ['nullable','integer','numeric',],
            'freeshippingamount' => ['nullable','integer','numeric',],
            'price_rounding_opt' => ['nullable','integer','numeric',],
            'is_cansenddirect' => ['nullable','boolean',],
            'shippinggremarks' => ['nullable','string','max:200',],
            'closingdate_opt' => ['nullable','integer','numeric',],
            'tax_rounding_opt' => ['nullable','integer','numeric',],
            'shiftoftax_opt' => ['nullable','integer','numeric',],
            'paymentmethod_opt' => ['nullable','integer','numeric',],
            'accountspayable' => ['nullable','integer','numeric',],
            'bankname' => ['nullable','string','max:20','regex:/[^\x01-\x7E\uFF61-\uFF9F]/'],
            'bankname_kana' => ['nullable','string','max:20','regex:/^[ァ-ンヴーッ]+$/'],
            'bankbranchno' => ['nullable','string','max:5',],
            'bankbranchname' => ['nullable','string','max:20','regex:/[^\x01-\x7E\uFF61-\uFF9F]/'],
            'bankbranchname_kana' => ['nullable','string','max:20','regex:/^[ァ-ンヴーッ]+$/'],
            'bankdeposittype_opt' => ['nullable','integer','numeric',],
            'bankaccountnumber' => ['nullable','string','max:8',],
            'bankaccountname' => ['nullable','string','max:30',],
            'bankaccountname_kana' => ['nullable','string','max:30',],
            'is_vendorpaysfee' => ['nullable','boolean',],
            'orderpriority' => ['nullable','integer','numeric',],
            'ordermethod_opt' => ['nullable','integer','numeric',],
            'remarks' => ['nullable','string','max:200',],
            'start_on' => ['nullable','date',],
            'end_on' => ['nullable','date',],
        ];
    }
}
