<?php

namespace App\Models\Common;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Validation\Rule;
use App\ValidateTrait;

use App\Models\Common\Company;

class Edifile extends Model
{
    use SoftDeletes;
    use ValidateTrait;
    public function companies() {
        return $this->belongsTo(Company::class)->withDefault();
    }
    protected $guarded = [];
    static $tablecomment = 'EDIファイル';
    static $modelzone = '共通';
    static $defaultsort = [
    ];
    static $referencedcolumns = [
    ];
    static $uniquekeys = [
       ['name'], 
    ];

    protected function rules()
    {
        return [
            'company_id' => ['required','integer','numeric',],
            'name' => ['required','string','max:50',Rule::unique('edifiles')->ignore($this->id),],
            'or_up_down' => ['required','string','max:1',],
            'filenamepattern' => ['required','string','max:50',],
            'postingtable' => ['required','string','max:30',],
            'loginurl' => ['required','string','max:255',],
            'loginid' => ['required','string','max:20',],
            'loginpassword' => ['required','string','max:20',],
            'processurl' => ['required','string','max:255',],
            'frequency' => ['required','string','max:30',],
            'start_on' => ['nullable','date',],
            'end_on' => ['nullable','date',],
        ];
    }
}
