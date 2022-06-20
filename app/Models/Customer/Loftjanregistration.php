<?php

namespace App\Models\Customer;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Validation\Rule;
use App\ValidateTrait;


class Loftjanregistration extends Model
{
    use SoftDeletes;
    use ValidateTrait;
    protected $guarded = [];
    static $tablecomment = 'ロフトJAN登録';
    static $modelzone = '顧客用';
    static $defaultsort = [
        'shopcode' => 'asc',
        'jancode' => 'asc',
    ];
    static $referencedcolumns = [
    ];
    static $uniquekeys = [
       ['jancode', 'shopcode', ]
    ];

    // input has_many clause here

    protected function rules()
    {
        return [
            'region' => ['required','string','max:20',],
            'linecode' => ['required','string','max:2','regex:/\d{2}/'],
            'itemcode' => ['required','string','max:4','regex:/\d{4}/'],
            'jancode' => ['required','string','max:13','regex:/\d{13}/'],
            'shopcode' => ['required','string','max:3',
                Rule::unique('loftjanregistrations')->ignore($this->id)->where(function($query){
                    $query->where('jancode', $this->jancode);
                }),'regex:/\d{3}/'],
            'price' => ['required','integer','numeric',],
            'purchaseprice' => ['required','integer','numeric',],
            'pricetermcode' => ['required','string','max:4','regex:/\d/'],
            'finishdatestr' => ['required','string','max:8','regex:/\d{8}/'],
            'updatedatestr' => ['required','string','max:8','regex:/\d{8}/'],
        ];
    }
}
