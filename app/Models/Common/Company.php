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
        'code', 
        'name', 
    ];
    static $uniquekeys = [
       ['code'], 
    ];

    protected function rules()
    {
        return [
            'code' => ['required','string','max:4',Rule::unique('companies')->ignore($this->id),'regex:/\d{4}/'],
            'name' => ['required','string','max:30','regex:/[^\x01-\x7E\uFF61-\uFF9F]/u'],
            'name_kana' => ['required','string','max:30','regex:/^[ァ-ヴー]+$/u'],
            'name_short' => ['required','string','max:10','regex:/[^\x01-\x7E\uFF61-\uFF9F]/u'],
            'postalcode' => ['required','string','max:8','regex:/[0-9]{3}-?[0-9]{4}/u'],
            'address1' => ['required','string','max:40',],
            'address2' => ['nullable','string','max:40',],
            'telno' => ['required','string','max:13','regex:/^[a-zA-Z0-9-]+$/u'],
            'foxno' => ['nullable','string','max:13','regex:/^[a-zA-Z0-9-]+$/u'],
            'url' => ['nullable','string','max:100','url'],
            'is_buyer' => ['required','boolean',],
            'is_vendor' => ['required','boolean',],
            'can_order' => ['required','boolean',],
            'can_sale' => ['required','boolean',],
            'can_stock' => ['required','boolean',],
            'can_work' => ['required','boolean',],
            'start_on' => ['nullable','date',],
            'end_on' => ['nullable','date',],
        ];
    }
}
