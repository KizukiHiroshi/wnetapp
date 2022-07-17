<?php
declare(strict_types=1);
namespace App\Services\Table;

use App\Services\Database\QueryService;

class GetRowByIdService {

    public function __construct() {
    }

    // 表示する行の実体を取得する
    public function getRowById($request, $modelindex, $columnsprop, $id) {
        // queryのfrom,join,select句を取得する
        $searchinput  = null;
        $displaymode = 'card';
        $queryservice = new QueryService;
        $tablequery = $queryservice->getTableQuery($request, $displaymode, $tempsort = null);
        // where句
        $tablename = $request->tablename;
        $tablequery = $tablequery->where($tablename.'.id', '=', $id);
        $row = $tablequery->first();
        return $row;
    }
}
