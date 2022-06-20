<?php
declare(strict_types=1);
namespace App\Usecases\Table;

use App\Services\SessionService;
use App\Services\Database\IsDeletedService;
use App\Services\Database\IsForceDeletedService;
use App\Services\Database\IsRestoredService;
use App\Services\Database\GetRowsByRawsqlService;
use App\Services\Table\SessionOptimizeService;

class TableCase  {

    private $sessionservice;
    public function __construct(
        SessionService $sessionservice){
             $this->sessionservice = $sessionservice;
    }

    // 表示Listのダウンロード用CSVを取得する
    public function getDownloadCSV($request){
        $modelindex = $this->sessionservice->getSession('modelindex');
        $tablename = $request->tablename;
        $columnsprop = $this->sessionservice->getSession('columnsprop', $tablename);
        // id,foreign_idを消す
        foreach ($columnsprop as $columnname => $pops){
            if ($columnname == 'id' or substr($columnname, -3) == '_id'){
                unset($columnsprop[$columnname]);
            }
        }
        // table名部分
        $downloadcsv = [
            [$modelindex[$tablename]['tablecomment'], $tablename]
        ];
        // Property部分
        $downloadcsv[] = array_keys($columnsprop);  // columnname
        $downloadcsv[] = array_column($columnsprop, 'type');
        $downloadcsv[] = array_column($columnsprop, 'notnull');
        $downloadcsv[] = array_column($columnsprop, 'isunique');
        $downloadcsv[] = array_column($columnsprop, 'comment');
        // List実体部分
        $tempsql = $this->sessionservice->getSession('tempsql');
        $getrowsbyrawsqlService = new GetRowsByRawsqlService;
        $rows = $getrowsbyrawsqlService->getRowsByRawsql($tempsql);
        foreach ($rows as $row){
            $values = [];
            foreach ($columnsprop as $columnname => $pops){
                $rowvalue = str_replace(array('\r\n','\r','\n',',',chr(10)), '', $row->$columnname);
                $rowvalue = str_replace('\n', '\\n', $rowvalue);
                // $rowvalue = str_replace(',', '', $rowvalue);
                array_push($values, $rowvalue);
            }
            $downloadcsv[]= $values;
        }
        return $downloadcsv;
    }
    
    // $requestの状態からSessionを適正化する
    public function sessionOptimize($request){
        $sessionoptimizeservice = new SessionOptimizeService;
        $sessionoptimizeservice->sessionOptimize($request);
    }

    // 削除更新(softDelete)実行
    public function is_Deleted($request){
        $tablename = $request->tablename;
        $id = $request->id;
        $isdeletedservice = new IsDeletedService;
        return $isdeletedservice->isDeleted($tablename, $id);
    }

    // 完全削除実行
    public function is_forceDeleted($request){
        $tablename = $request->tablename;
        $id = $request->id;
        $isforcedeletedservice = new IsForceDeletedService;
        return $isforcedeletedservice->isForceDeleted($tablename, $id);
    }

    // 復活実行
    public function is_Restored($request){
        $tablename = $request->tablename;
        $id = $request->id;
        $isrestoredservice = new IsRestoredService;
        return $isrestoredservice->isrestored($tablename, $id);
    }

}

