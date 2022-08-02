<?php
declare(strict_types=1);
namespace App\Services\Table;

use App\Services\SessionService;

class GetRowByIdService {

    public function __construct() {
    }

    // 表示する行の実体を取得する
    public function getRowById($request, $id) {
        // queryのfrom,join,select句を取得する
        $tablename= $request->tablename;
        $sessionservice = new SessionService;
        $modelindex = $sessionservice->getSession('modelindex');
        $modelname = $modelindex[$tablename]['modelname'];
        $tablequery = $modelname::withTrashed();
        $tablequery = $tablequery->where($tablename.'.id', '=', $id);
        $row = $tablequery->first();
        return $row;
    }
}
