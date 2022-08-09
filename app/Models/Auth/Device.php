<?php

namespace App\Models\Auth;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Validation\Rule;
use App\Traits\ValidateTrait;

use App\Models\Common\User;

class Device extends Model
{
    use SoftDeletes;
    use ValidateTrait;
    public function users(){
        return $this->belongsTo(User::class)->withDefault();
    }
    protected $guarded = [];
    static $tablecomment = 'デバイス';
    static $modelzone = '共通';
    static $defaultsort = [
        'user_id' => 'asc',
        'name' => 'asc',
    ];
    static $referencedcolumns = [
        'user_id', 
        'name', 
    ];
    static $uniquekeys = [
       ['name'], 
    ];

    // input has_many clause here

    protected function rules()
    {
        return [
            'user_id' => ['required','integer','numeric',],
            'name' => ['required','string','max:50',],
            'key' => ['required','string','max:200',],
            'paginatecnt' => ['required','integer','numeric',],
            'accesstime' => ['required','date',],
            'accessip' => ['required','string','max:20',],
            'validityperiod' => ['required','date',],
            'start_on' => ['nullable','date',],
            'end_on' => ['nullable','date',],
        ];
    }
}
