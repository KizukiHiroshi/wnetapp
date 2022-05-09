<?php

namespace App\Models\Zero;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Validation\Rule;
use App\ValidateTrait;


class Oldtable extends Model
{
    use SoftDeletes;
    use ValidateTrait;
    protected $guarded = [];
    static $tablecomment = '旧テーブル';
    static $modelzone = 'システム開発';
    static $defaultsort = [
    ];
    static $referencedcolumns = [
        'name', 
    ];
    static $uniquekeys = [
       ['name'], 
    ];

    // input has_many clause here
    public function columnreplacements() {
        return $this->hasMany(Columnreplacement::class);
    }

    protected function rules()
    {
        return [
            'name' => ['required','string','max:30',Rule::unique('oldtables')->ignore($this->id),],
            'latest_created' => ['required','date',],
            'latest_updated' => ['required','date',],
        ];
    }
}
