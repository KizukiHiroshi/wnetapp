<?php

namespace App\Models\Zero;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Validation\Rule;
use App\ValidateTrait;

use App\Models\Common\Jobtype;

class Concern extends Model
{
    use SoftDeletes;
    use ValidateTrait;
    public function jobtypes(){
        return $this->belongsTo(Jobtype::class)->withDefault();
    }
    protected $guarded = [];
    static $tablecomment = 'どうする';
    static $modelzone = 'システム開発';
    static $defaultsort = [
        'jobtype_id' => 'asc',
        'name' => 'asc',
        'is_solved' => 'asc',
        'importance' => 'asc',
        'priority' => 'asc',
    ];
    static $referencedcolumns = [
        'name', 
    ];
    static $uniquekeys = [
       ['name'], 
    ];

    // input has_many clause here

    protected function rules()
    {
        return [
            'jobtype_id' => ['required','integer','numeric',],
            'name' => ['required','string','max:40',],
            'content' => ['required','string','max:255',],
            'importance' => ['required','string','max:2',Rule::in(['A', 'B', 'C', 'D', 'E', 'X']),],
            'priority' => ['required','integer','numeric','between:1, 100'],
            'solution' => ['required','string','max:255',],
            'is_solved' => ['required','boolean',],
            'start_on' => ['nullable','date',],
            'end_on' => ['nullable','date',],
        ];
    }
}
