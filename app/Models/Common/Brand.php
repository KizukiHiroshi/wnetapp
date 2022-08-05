<?php

namespace App\Models\Common;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Validation\Rule;
use App\ValidateTrait;


class Brand extends Model
{
    use SoftDeletes;
    use ValidateTrait;
    protected $guarded = [];
    static $tablecomment = 'ブランド';
    static $modelzone = '共通';
    static $defaultsort = [
        'name_kana' => 'asc',
    ];
    static $referencedcolumns = [
        'name', 
    ];
    static $uniquekeys = [
       ['name'], 
    ];

    // input has_many clause here
    public function products(){
        return $this->hasMany(Product::class);
    }

    protected function rules()
    {
        return [
            'name' => ['required','string','max:30','regex:/[^\x01-\x7E\uFF61-\uFF9F]/'],
            'name_kana' => ['nullable','string','max:30',],
            'url' => ['nullable','string','max:255','url'],
            'image' => ['nullable','string','max:255','regex:/^[a-zA-Z0-9-_]+$/'],
            'remark' => ['nullable','string','max:255',],
            'start_on' => ['nullable','date',],
            'end_on' => ['nullable','date',],
        ];
    }
}
