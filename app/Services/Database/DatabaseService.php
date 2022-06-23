<?php
declare(strict_types=1);
namespace App\Services\Database;

use App\Services\SessionService;
use App\Services\Database\QueryService;

class DatabaseService 
{
    private $modelindex;
    private $queryservice;
    private $sessionservice;
    public function __construct(
        SessionService $sessionservice, 
        QueryService $queryservice) {
        $this->modelindex = $sessionservice->getSession('modelindex');
        $this->queryservice = $queryservice;
        $this->sessionservice = $sessionservice;
    }

    // ★★ このファイルはもう要らない…はず

    // 既存のDownload用のSqlで表示するListの実体を取得する
    // public function getRowsWithDownloadSql($request, $paginatecnt) {
    //     $displaymode = 'list';
    //     $tablename = $request->tablename;
    //     $modelname = $this->modelindex[$tablename]['modelname'];
    //     $rawsql = $this->sessionservice->getSession('tempsql');
    //     $tablequery = $modelname::query();
    //     $tablequery = $tablequery->select($rawsql);
    //     // 取得実行
    //     $rows = $tablequery->Paginate($paginatecnt);
    //     return $rows;
    // }

    
    // card表示用にforeignkey用のセレクトリストを用意する
    // public function getForeginSelects($columnsprop) {
    //     $foreignselects = [];
    //     $concats = [];           // 合体する参照先カラムの配列
    //     // 必要なセレクトをまず決める
    //     foreach ($columnsprop AS $columnname => $prop) {
    //         if (substr($columnname, -3) =='_id' || substr($columnname, -7) =='_id_2nd') {
    //             if (strpos($columnname, '_id_2nd_') == false) {
    //                 // 参照元カラム名を取得する
    //                 $forerignreferencename = substr($columnname, 0, strripos($columnname, '_id')).'_id_reference';
    //                 $foreignselects[$forerignreferencename] = [];
    //             }
    //         }
    //     }
    //     // foreignkey用セレクトの実体を得る
    //     foreach ($foreignselects AS $forerignreferencename => $blank) {
    //         foreach ($columnsprop AS $columnname => $prop) {
    //             // referenceの対象カラムを探す
    //             if (strripos($columnname, '_id_') 
    //                 && strpos($columnname, '_id_2nd') == false 
    //                 && substr($columnname, -3) !== '_id') {
    //                 if (substr($columnname, 0, strripos($columnname, '_id_')) 
    //                     == substr($forerignreferencename, 0, strripos($forerignreferencename, '_id_'))) {
    //                     $referencetablename = $prop['tablename'];
    //                     $concats[] = $prop['tablename'].'.'.$prop['realcolumn'];
    //                 }
    //             }
    //         }
    //         $foreignselectrows = $this->getIdReferenceSelects($forerignreferencename, $referencetablename, $concats);
    //         $foreignselects[$forerignreferencename] = $foreignselectrows;
    //         // 参照内容を初期化
    //         $concats = [];
    //     }
    //     return $foreignselects;
    // }

    // 参照用selects作成
    // public function getIdReferenceSelects($referencename, $tablename, $concats) {
    //     $idreferenceselects =[];
    //     // queryのfrom,join,select句を取得する
    //     $modelname = $this->modelindex[$tablename]['modelname'];
    //     $tablequery = $modelname::query();
    //     // from句
    //     $tablequery = $tablequery->from($tablename);
    //     $concatclause = $this->queryservice->getConcatClause($concats, ' ', $referencename);
    //     $tablequery = $tablequery->select('id', DB::raw($concatclause));
    //     $rows = $tablequery->get();
    //     foreach ($rows AS $row) {
    //         $idreferenceselects[$row->id] = $row->$referencename;
    //     }
    //     return $idreferenceselects;
    // }
}