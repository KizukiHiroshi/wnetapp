<?php
declare(strict_types=1);
namespace App\Services\Database;

use App\Services\SessionService;

class GetRowByIdService 
{
    public function __construct() {
    }

    // テーブルの値を配列で返す
    public function getRowById($tablename, $id) {
        $sessionservice = new SessionService;
        $modelindex = $sessionservice->getSession('modelindex');
        $modelname = $modelindex[$tablename]['modelname'];
        $row = $modelname::findOrFail($id)->toArray();
        return $row;
    }
}