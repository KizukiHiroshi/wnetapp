<?php
declare(strict_types=1);
namespace App\Services\Database;

use App\Services\SessionService;

class SaveTempsqlService 
{
    public function __construct(){
    }

    // $tablequeryからリスト表示に使用したsql文をSessionに保存する
    public function saveTempsql($tablequery){
        $sqlparams = [];
        // パラメータを取り出す
        $rawparams = $tablequery->getBindings();
        // コーテーションで囲む
        foreach ($rawparams as $rawparam){
            $sqlparams[] = "'".$rawparam."'";
        }
        // パラメータをSQLに入れる
        $tempsql = preg_replace_array('/\?/', $sqlparams, $tablequery->toSql());
        $sessionservice = new SessionService;
        $sessionservice->putSession('tempsql', $tempsql);
    }
}