<?php
declare(strict_types=1);
namespace App\Services\Table;

use App\Services\SessionService;
use App\Services\Database\QueryService;
use App\Services\Database\SaveTempsqlService;

class GetTableRowsService 
{
    public function __construct() {
    }

    // 表示するListの実体を取得する
    public function getTableRows($request, $tempsort) {
        $displaymode = 'list';
        $queryservice = new QueryService;
        $tablequery = $queryservice->getTableQuery($request, $displaymode, $tempsort);
        // $tablequeryからリスト表示に使用したsql文をSessionに保存する
        $savetempsqlservice = new SaveTempsqlService;
        $savetempsqlservice->saveTempsql($tablequery);
        // 取得実行
        $sessionservice = new SessionService;
        $paginatecnt = $sessionservice->getSession('paginatecnt');
        $rows = $tablequery->Paginate($paginatecnt);
        return $rows;
    }
}