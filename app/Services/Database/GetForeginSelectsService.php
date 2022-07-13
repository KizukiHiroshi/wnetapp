<?php
declare(strict_types=1);
namespace App\Services\Database;

use Illuminate\Support\Facades\DB;
use App\Services\SessionService;
use App\Services\Database\QueryService;

class GetForeginSelectsService 
{
    public function __construct() {
    }

    // card表示用にforeignkey用のセレクトリストを用意する
    public function getForeginSelects($columnsprop) {
        $foreignselects = [];
        $concats = [];           // 合体する参照先カラムの配列
        // 必要なセレクトをまず決める
        foreach ($columnsprop AS $columnname => $prop) {
            if (substr($columnname, -3) =='_id' || substr($columnname, -7) =='_id_2nd') {
                if (strpos($columnname, '_id_2nd_') == false) {
                    // 参照元カラム名を取得する
                    $forerignreferencename = substr($columnname, 0, strripos($columnname, '_id')).'_id_reference';
                    $foreignselects[$forerignreferencename] = [];
                }
            }
        }
        // foreignkey用セレクトの実体を得る
        foreach ($foreignselects AS $forerignreferencename => $blank) {
            foreach ($columnsprop AS $columnname => $prop) {
                // referenceの対象カラムを探す
                if (strripos($columnname, '_id_') 
                    && strpos($columnname, '_id_2nd') == false 
                    && substr($columnname, -3) !== '_id') {
                    if (substr($columnname, 0, strripos($columnname, '_id_')) 
                        == substr($forerignreferencename, 0, strripos($forerignreferencename, '_id_'))) {
                        $referencetablename = $prop['tablename'];
                        $concats[] = $prop['tablename'].'.'.$prop['realcolumn'];
                    }
                }
            }
            $foreignselectrows = $this->getIdReferenceSelects($forerignreferencename, $referencetablename, $concats);
            $foreignselects[$forerignreferencename] = $foreignselectrows;
            // 参照内容を初期化
            $concats = [];
        }
        return $foreignselects;
    }

    // 参照用selects作成
    private function getIdReferenceSelects($referencename, $tablename, $concats) {
        $idreferenceselects =[];
        // queryのfrom,join,select句を取得する
        $sessionservice = new SessionService;
        $modelindex = $sessionservice->getSession('modelindex');
        $modelname = $modelindex[$tablename]['modelname'];
        if ($modelname::count() > 50) {
            return $idreferenceselects;
        }
        $tablequery = $modelname::query();
        // from句
        $tablequery = $tablequery->from($tablename);
        $queryservice = new QueryService;
        $concatclause = $queryservice->getConcatClause($concats, ' ', $referencename);
        $tablequery = $tablequery->select('id', DB::raw($concatclause));
        $rows = $tablequery->get();
        foreach ($rows AS $row) {
            $idreferenceselects[$row->id] = $row->$referencename;
        }
        return $idreferenceselects;
    }
}