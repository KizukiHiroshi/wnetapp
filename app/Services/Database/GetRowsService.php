<?php

// ServiceではIlluminate\Http\Requestにアクセスしない
// DatabaseService:Databaseへの直接のAccsessを担う

declare(strict_types=1);
namespace App\Services\Database;

use App\Services\SessionService;
use App\Services\Database\QueryService;

class GetRowsService 
{
    public function __construct() {
    }

    // 表示するListの実体を取得する
    public function getRows($request, $columnsprop, $searchinput, $paginatecnt, $tempsort) {
        $sessionservice = new SessionService;
        $modelindex = $sessionservice->getSession('modelindex');
        $displaymode = 'list';
        $queryservice = new QueryService;
        $tablequery = $queryservice->getTableQuery($request, $modelindex, $columnsprop, $searchinput, $displaymode, $tempsort);
        // $tablequeryからリスト表示に使用したsql文をSessionに保存する
        $this->saveTempsql($tablequery);
        // 取得実行
        $rows = $tablequery->Paginate($paginatecnt);
        return $rows;
    }

    // $tablequeryからリスト表示に使用したsql文をSessionに保存する
    private function saveTempsql($tablequery) {
        $sqlparams = [];
        // パラメータを取り出す
        $rawparams = $tablequery->getBindings();
        // コーテーションで囲む
        foreach ($rawparams as $rawparam) {
            $sqlparams[] = "'".$rawparam."'";
        }
        // パラメータをSQLに入れる
        $tempsql = preg_replace_array('/\?/', $sqlparams, $tablequery->toSql());
        $sessionservice = new SessionService;
        $sessionservice->putSession('tempsql', $tempsql);
    }
}