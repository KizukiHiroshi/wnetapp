<?php

namespace App\Models\Zero;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Validation\Rule;
use App\ValidateTrait;

use App\Models\Common\Oldtable;

class Columnreplacement extends Model
{
    use SoftDeletes;
    use ValidateTrait;
    public function oldtables() {
        return $this->belongsTo(Oldtable::class)->withDefault();
    }
    protected $guarded = [];
    static $tablecomment = '対応カラム';
    static $modelzone = 'システム開発';
    static $defaultsort = [
        'oldcolumnno' => 'asc',
    ];
    static $referencedcolumns = [
        'oldtable_id', 
        'oldcolumnname', 
        'newtablename', 
        'newcolumnname', 
    ];
    static $uniquekeys = [
       ['oldtable_id', 'oldcolumnname', ]
    ];

    // input has_many clause here

    protected function rules()
    {
        return [
            'oldtable_id' => ['required','integer','numeric',],
            'oldcolumnno' => ['required','integer','numeric',],
            'oldcolumnname' => ['required','string','max:50',
                Rule::unique('columnreplacements')->ignore($this->id)->where(function($query) {
                    $query->where('oldtable_id', $this->oldtable_id);
                }),],
            'newtablename' => ['required','string','max:50',],
            'newcolumnname' => ['required','string','max:50',],
        ];
    }
}
