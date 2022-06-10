<?php

namespace App\Models\Zero;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Validation\Rule;
use App\ValidateTrait;

use App\Models\Common\Tablereplacement;

class Columnreplacement extends Model
{
    use SoftDeletes;
    use ValidateTrait;
    public function tablereplacements() {
        return $this->belongsTo(Tablereplacement::class)->withDefault();
    }
    protected $guarded = [];
    static $tablecomment = 'カラム置換';
    static $modelzone = 'システム開発';
    static $defaultsort = [
        'no' => 'asc',
    ];
    static $referencedcolumns = [
        'tablereplacement_id', 
        'oldcolumnname', 
        'newcolumnname', 
    ];
    static $uniquekeys = [
       ['oldcolumnname', 'newcolumnname', ]
    ];

    // input has_many clause here

    protected function rules()
    {
        return [
            'tablereplacement_id' => ['required','integer','numeric',],
            'no' => ['required','integer','numeric',],
            'oldcolumnname' => ['required','string','max:50',],
            'is_keycolumn' => ['required','boolean',],
            'newcolumnname' => ['required','string','max:50',
                Rule::unique('columnreplacements')->ignore($this->id)->where(function($query) {
                    $query->where('oldcolumnname', $this->oldcolumnname);
                }),],
            'remarks' => ['required','string','max:200',],
        ];
    }
}
