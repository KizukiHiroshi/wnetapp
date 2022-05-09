<?php

namespace App\Models\Zero;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Validation\Rule;
use App\ValidateTrait;

use App\Models\Common\Columnreplacement;

class Codereplacement extends Model
{
    use SoftDeletes;
    use ValidateTrait;
    public function columnreplacements() {
        return $this->belongsTo(Columnreplacement::class)->withDefault();
    }
    protected $guarded = [];
    static $tablecomment = '対応コード';
    static $modelzone = 'システム開発';
    static $defaultsort = [
    ];
    static $referencedcolumns = [
        'columnreplacement_id', 
        'oldcode', 
    ];
    static $uniquekeys = [
       ['columnreplacement_id', 'oldcode', ]
    ];

    // input has_many clause here

    protected function rules()
    {
        return [
            'columnreplacement_id' => ['required','integer','numeric',Rule::unique('codereplacements')->ignore($this->id),],
            'oldcode' => ['required','string','max:50',
                Rule::unique('codereplacements')->ignore($this->id)->where(function($query) {
                    $query->where('columnreplacement_id', $this->columnreplacement_id);
                    $query->where('columnreplacement_id', $this->columnreplacement_id);
                }),],
            'newcode' => ['required','string','max:50',],
        ];
    }
}
