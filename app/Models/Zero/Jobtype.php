<?php
namespace App\Models\Zero;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Jobtype extends Model
{
    use SoftDeletes;
    protected $guarded = [];
    static $tablecomment = '業務種類';
    static $modelzone = 'システム開発';
    static $defaultsort = [
        'code' => 'asc',
    ];
    static $referencedcolumns = [
       'code', 'name', 
    ];
    static $uniquekeys = [
        'code', 'name_system', 
    ];
     
    public function concerns() {
        return $this->hasMany(Concern::class);
    }
    public function accountauthorities() {
        return $this->hasMany(Accountauthority::class);
    }

}

