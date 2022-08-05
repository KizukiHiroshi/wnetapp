<?php
declare(strict_types=1);
namespace App\Services\Database;

use Illuminate\Support\Facades\DB;
use App\Services\SessionService;
use App\Services\Database\QueryService;
use App\Services\Database\GetWhereService;
use App\Services\Database\SetWhereclauseToQueryService;

class GetForeginSelectsService 
{
    public function __construct() {
    }

    // card表示用にforeignkey用のセレクトリストを用意する
    public function getForeginSelects($cardcolumnsprop, $searchinput = NULL) {
        $foreignselects = [];
        $concats = [];           // 合体する参照先カラムの配列
        // 必要なセレクトをまず決める
        foreach ($cardcolumnsprop AS $columnname => $prop) {
            if (substr($columnname, -13) == '_id_reference' ) {
                $foreignselects[$columnname] = [];
            }
        }
        // foreignkey用セレクトの実体を得る
        foreach ($foreignselects AS $forerignreferencename => $blank) {
            foreach ($cardcolumnsprop AS $columnname => $prop) {
                // referenceの対象カラムを探す
                if (strripos($columnname, '_id_') 
                    && strpos($columnname, '_id_2nd') == false 
                    && substr($columnname, -3) !== '_id'
                    && substr($columnname, -13) !== '_id_reference') {
                    if (substr($columnname, 0, strripos($columnname, '_id_')) 
                        == substr($forerignreferencename, 0, strripos($forerignreferencename, '_id_'))) {
                        $referencetablename = $prop['tablename'];
                        $concats[] = $prop['tablename'].'.'.$prop['realcolumn'];
                    }
                }
            }
            // searchinputに該当条件があれば、where句として追加する
            $getwhereservice = new GetWhereService;
            $where = $getwhereservice->getWhere($searchinput, $cardcolumnsprop);    
            $foreignselectrows = $this->getIdReferenceSelects($forerignreferencename, $referencetablename, $concats, $where, $cardcolumnsprop);
            $foreignselects[$forerignreferencename] = $foreignselectrows;
            // 参照内容を初期化
            $concats = [];
        }
        return $foreignselects;
    }

    // 参照用selects作成
    private function getIdReferenceSelects($referencename, $tablename, $concats, $where, $columnsprop) {
        $idreferenceselects =[];
        DB::enableQueryLog();
        // queryのfrom,join,select句を取得する
        $sessionservice = new SessionService;
        $modelindex = $sessionservice->getSession('modelindex');
        $modelname = $modelindex[$tablename]['modelname'];
        $tablequery = $modelname::query();
        // from句
        $tablequery = $tablequery->from($tablename);
        // where句
        $setwhereclausetoqueryservice = new SetWhereclauseToQueryService;
        $tablequery = $setwhereclausetoqueryservice->setWhereclauseToQuery($tablename, $tablequery, $where);
        $queryservice = new QueryService;
        // join句
        $concatclause = $queryservice->getConcatClause($concats, ' ', $referencename);
        $queryservice->addJoinToQuery($tablequery, $tablename, $columnsprop, $where);
        $tablequery = $tablequery->select($tablename.'.id', DB::raw($concatclause));
        $rows = $tablequery->get();
        $answer = DB::getQueryLog();
        if (count($rows) > 200) {
            $idreferenceselects[0] = '200行以上は非表示';
        } else {
            foreach ($rows AS $row) {
                $idreferenceselects[$row->id] = $row->$referencename;
            }
        }
        return $idreferenceselects;
    }
}