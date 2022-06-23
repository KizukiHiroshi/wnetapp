<?php
declare(strict_types=1);
namespace App\Services\Database;

use Illuminate\Support\Facades\DB;

class GetRowsByRawsqlService {
    
    public function __construct() {
    }

    // RawsqlでListの実体を取得する
    public function getRowsByRawsql($rawsql) {
        $rows = DB::select($rawsql);
        return $rows;
    }    
}