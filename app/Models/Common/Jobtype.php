<?php
namespace App\Models\Common;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Jobtype extends Model
{
    use SoftDeletes;
    protected $guarded = [];
    static $tablecomment = '業務種類';
    static $modelzone = '共通';
    static $defaultsort = [
        'code' => 'asc',
    ];
    static $referencedcolumns = [
       'code', 'name', 
    ];
    
    public function concerns() {
        return $this->hasMany(Concern::class);
    }
    public function accountauthorities() {
        return $this->hasMany(Accountauthority::class);
    }

}

