<?php

// ServiceではIlluminate\Http\Requestにアクセスしない
// DbioService:Databaseへの直接のAccsessを担う

declare(strict_types=1);
namespace App\Service\Common;

use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use App\Service\Common\SessionService;
use App\Service\Common\QueryService;

class DbioService 
{
    private $modelindex;
    private $queryservice;
    private $sessionservice;
    public function __construct(SessionService $sessionservice, QueryService $queryservice) {
        $this->modelindex = $sessionservice->getSession('modelindex');
        $this->queryservice = $queryservice;
        $this->sessionservice = $sessionservice;
    }

    // 削除実行
    public function is_Deleted($tablename, $id) {
        $modelname = $this->modelindex[$tablename]['modelname'];
        $targetrow = $modelname::findOrFail($id);
        $is_deleted = $targetrow->delete();
        return $is_deleted;
    }

    // 完全削除実行
    public function is_forceDeleted($tablename, $id) {
        $modelname = $this->modelindex[$tablename]['modelname'];
        $targetrow = $modelname::withTrashed()->findOrFail($id);
        $is_forceDeleted = $targetrow->forceDelete();
        return $is_forceDeleted;
    }

    // 復元実行
    public function is_Restored($tablename, $id) {
        $modelname = $this->modelindex[$tablename]['modelname'];
        $targetrow = $modelname::withTrashed()->findOrFail($id);
        $is_restored = $targetrow->restore();
        return $is_restored;
    }
    
    // 表示するListの実体を取得する
    public function getRows($request, $modelindex, $columnsprop, $tempsort, $paginatecnt) {
        $displaymode = 'list';
        $tablequery = $this->queryservice->getTableQuery($request, $modelindex, $columnsprop, $displaymode, $tempsort);
        // ダウンロードをするためにsqlを保存する
        $downloadsql = $tablequery->toSql();
        $this->sessionservice->putSession('downloadsql', $downloadsql);
        // 取得実行
        $rows = $tablequery->Paginate($paginatecnt);
        return $rows;
    }

    // 表示する行の実体を取得する
    public function getRowById($request, $modelindex, $columnsprop, $id) {
        // queryのfrom,join,select句を取得する
        $displaymode = 'card';
        $tablequery = $this->queryservice->getTableQuery($request, $modelindex, $columnsprop, $displaymode);
        // where句
        $tablename = $request->tablename;
        $tablequery = $tablequery->where($tablename.'.id', '=', $id);
        $row = $tablequery->first();
        return $row;
    }
    
    // card表示用にforeignkey用のセレクトリストを用意する
    public function getForeginSelects($columnsprop) {
        $foreignselects = [];
        $referencetablename = '';    // 参照先テーブル名
        $concats = [];           // 合体する参照先カラムの配列
        // 必要なセレクトをまず決める
        foreach ($columnsprop AS $columnname => $prop) {
            if (substr($columnname, -3) =='_id' || substr($columnname, -7) =='_id_2nd') {
                // 参照元カラム名を取得する
                $forerignreferencename = $columnname.'_reference';
                $foreignselects[$forerignreferencename] = [];
            }
        }
        // セレクトの実体を得る
        foreach ($foreignselects AS $forerignreferencename => $blank) {
            foreach ($columnsprop AS $columnname => $prop) {
                if (Str::before($columnname,'_id_') == Str::before($forerignreferencename,'_id_reference')
                    || Str::before($columnname,'_id_') == Str::before($forerignreferencename,'_id_2nd_reference')) {
                    // 参照元カラム名を取得する
                    $referencetablename = Str::plural(Str::before($columnname,'_id_'));
                    $concats[Str::before($columnname,'_id_').'_id'] = $prop['tablename'].'.'.$prop['realcolumn'];
                }
            }
            $foreignselectrows = $this->getIdReferenceSelects($referencetablename, $concats);
            $foreignselects[$forerignreferencename] = $foreignselectrows;
            // 参照内容を初期化
            $concats = [];
        }
        return $foreignselects;
    }

    // 参照用selects作成
    public function getIdReferenceSelects($tablename, $concats) {
        $idreferenceselects =[];
        $referencedcolumnname = $tablename.'_reference';
        // queryのfrom,join,select句を取得する
        $modelname = $this->modelindex[$tablename]['modelname'];
        $tablequery = $modelname::query();
        // from句
        $tablequery = $tablequery->from($tablename);
        $concatclause = $this->queryservice->getConcatClause($concats, ' ', $referencedcolumnname);
        $tablequery = $tablequery->select('id', DB::raw($concatclause));
        $rows = $tablequery->get();
        foreach ($rows AS $row) {
            $idreferenceselects[$row->id] = $row->$referencedcolumnname;
        }
        return $idreferenceselects;
    }

    // RawsqlでListの実体を取得する
    public function getRowsByRawsql($rawsql) {
        $rows = DB::select($rawsql);
        return $rows;
    }

    // 参照id取得
    // $findidset =  参照テーブル名?参照カラム名=値&参照カラム名=値
    public function findId($findidset) {
        $foundid = null;
        $tablename = Str::plural(Str::before($findidset,'?'));
        $is_joinedunique = strpos(Str::after($findidset,'?'),'&&',) !== false ? true : false;
        if ($is_joinedunique) {
            $subcolset = explode('&&',Str::after($findidset,'?'));
        } else {
            $subcolset = explode('&',Str::after($findidset,'?'));
        }
        foreach($subcolset as $subcol) {
            $colset[Str::before($subcol,'=')] = Str::after($subcol,'=');
        }
        $modelname = $this->modelindex[$tablename]['modelname'];
        $tablequery = $modelname::query();
        // from句
        $tablequery = $tablequery->from($tablename);
        $wherecnt = 1;
        foreach ($colset as $columnnama => $value) {
            if ($wherecnt == 1 || $is_joinedunique) {
                $tablequery = $tablequery->where($tablename.'.'.$columnnama, '=', ''.$value.'');
            } else {
                $tablequery = $tablequery->orWhere($tablename.'.'.$columnnama, '=', ''.$value.'');
            }
            $wherecnt += 1;
        }
        $rows = $tablequery->get();
        if (count($rows) == 1) {
            foreach ($rows as $row) {
                $foundid = $row->id;
            }
        } elseif (count($rows) > 1) {
            $foundid = 'many';
        }
        return $foundid;
    }

    // tablename:対象のテーブル
    // $form:挿入変更するカラムと値
    // $id:isnull->STORE,not null->UPDATE
    // $mode:save->実行してERRORを発生する、check->チェックしてTEXTを返す
    // return:ERRORであればException又はText、正常であれば$id
    public function excuteProcess($tablename, $form, $id){
        $modelname = $this->modelindex[$tablename]['modelname'];
        if (!$id) {
            $targetrow = new $modelname;
        } else {
            $targetrow = $modelname::findOrFail($id);
        }
        $targetrow->fill($form)->save();
        return $targetrow->id;
    }

    // tablename:対象のテーブル
    // $form:挿入変更するカラムと値
    // $id:isnull->STORE,not null->UPDATE
    // $mode:実行かテスト:save or check
    // save->失敗したらARRAYを返す、check->チェックしてARRAYを返す
    public function excuteCsvprocess($tablename, $form, $id, $mode){
        $modelname = $this->modelindex[$tablename]['modelname'];
        if (!$id) {
            $targetrow = new $modelname;
        } else {
            $targetrow = $modelname::findOrFail($id);
        }
        if ($mode == 'save') {
            $error = $targetrow->fill($form)->csvSave();
            return $error;
        } elseif ($mode == 'check') {
            $error = $targetrow->fill($form)->csvCheck();
            return $error;
        } else {
            return false;
        }
    }
}