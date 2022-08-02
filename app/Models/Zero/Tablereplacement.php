<?php

namespace App\Models\Zero;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Validation\Rule;
use App\ValidateTrait;


class Tablereplacement extends Model
{
    use SoftDeletes;
    use ValidateTrait;
    protected $guarded = [];
    static $tablecomment = 'テーブル置換';
    static $modelzone = 'システム開発';
    static $defaultsort = [
        'no' => 'asc',
    ];
    static $referencedcolumns = [
        'name', 
        'oldtablename', 
        'newtablename', 
    ];
    static $uniquekeys = [
       ['name', 'oldtablename', 'newtablename', ]
    ];

    // input has_many clause here

    protected function rules()
    {
        return [
            'no' => ['required','integer','numeric',],
            'name' => ['required','string','max:30',],
            'systemname' => ['required','string','max:30',],
            'oldtablename' => ['required','string','max:30',
                Rule::unique('tablereplacements')->ignore($this->id)->where(function($query){
                }),],
            'newtablename' => ['required','string','max:30',
                Rule::unique('tablereplacements')->ignore($this->id)->where(function($query){
                    $query->where('oldtablename', $this->oldtablename);
                }),],
            'latest_created' => ['required','date',],
            'latest_updated' => ['required','date',],
            'maxvalue' => ['required','string','max:50',],
            'remarks' => ['nullable','string','max:200',],
            'start_on' => ['nullable','date',],
            'end_on' => ['nullable','date',],
        ];
    }
}
