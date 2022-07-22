<?php

namespace App\Models\Base;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\ValidateTrait;


class Sequence extends Model
{
    use SoftDeletes;
    use ValidateTrait;
    protected $guarded = [];
    static $tablecomment = '連番管理';
    static $modelzone = '共通基礎';
    static $defaultsort = [
        'key' => 'asc',
    ];
    static $referencedcolumns = [
        'key', 
    ];
    static $uniquekeys = [
       ['key'], 
    ];

    // input has_many clause here

    protected function rules()
    {
        return [
            'name' => ['required','string','max:64',],
            'key' => ['required','string','max:64',],
            'sequence' => ['required','integer','numeric',],
        ];
    }
    // 独自コネクション名
    // ※ここでしか使わないからあえて config に書かない
    const DB_CONNECTION = 'mysql_sequence';
    // 独自コネクション
    protected $connection = self::DB_CONNECTION;
    // タイムスタンプなし
    public $timestamps = false;
    // キー変更
    protected $primaryKey = 'key';
    protected static function boot()
    {
        parent::boot();
        // デフォルトコネクションをコピーして独自コネクションを作る
        config(['database.connections.' . self::DB_CONNECTION =>
            config('database.connections.' . config('database.default')),
        ]);
    }
}
