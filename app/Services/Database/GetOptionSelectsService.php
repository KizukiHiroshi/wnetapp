<?php
declare(strict_types=1);
namespace App\Services\Database;

use Illuminate\Support\Facades\DB;
use App\Services\SessionService;
use App\Services\Database\QueryService;

class GetOptionSelectsService 
{
    public function __construct() {
    }

    // card表示用にoption用のセレクトリストを用意する
    public function getOptionSelects($columnsprop) {
        $optionselects = [];
        $concats = [];           // 合体する参照先カラムの配列
        // 必要なセレクトをまず決める
        foreach ($columnsprop AS $columnname => $prop) {
            if (substr($columnname, -4) =='_opt') {
                $optionreferencename = substr($columnname, 0, -4).'ion_reference';
                $optionselects[$optionreferencename] = [];
            }
        }
        // foption用セレクトの実体を得る
        foreach ($optionselects AS $optionreferencename => $blank) {
            foreach ($columnsprop AS $columnname => $prop) {
                $referencetablename = $prop['tablename'];
                $concats[] = $prop['tablename'].'.'.$prop['realcolumn'];
            }
        }
        $optionselectrows = $this->getIdReferenceSelects($optionreferencename, $referencetablename, $concats);
        $optionselects[$optionreferencename] = $optionselectrows;
        // 参照内容を初期化
        $concats = [];
        return $optionselects;
    }

    // 参照用selects作成
    private function getIdReferenceSelects($referencename, $tablename, $concats) {
        $idreferenceselects =[];
        // queryのfrom,join,select句を取得する
        $sessionservice = new SessionService;
        $modelindex = $sessionservice->getSession('modelindex');
        $modelname = $modelindex[$tablename]['modelname'];
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