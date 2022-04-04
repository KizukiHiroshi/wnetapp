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
        // 取得実行
        $rows = $tablequery->Paginate($paginatecnt);
        // ダウンロードをするためにsqlを保存する
        $downloadsql = $tablequery->toSql();
        $this->sessionservice->putSession('downloadsql', $downloadsql);
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
            if (substr($columnname,-3)=='_id') {
                // 参照元カラム名を取得する
                $forerignreferencename = substr($columnname,0,-3).'_reference';
                $foreignselects[$forerignreferencename] = [];
            }
        }
        // セレクトの実体を得る
        foreach ($foreignselects AS $forerignreferencename => $blank) {
            foreach ($columnsprop AS $columnname => $prop) {
                if (Str::before($columnname,'_id_') == Str::before($forerignreferencename,'_reference')) {
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
        $referencedcolumnname = substr($tablename,0,-3).'_reference';
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
        $subcolset = explode('&',Str::after($findidset,'?'));
        foreach($subcolset as $subcol) {
            $colset[Str::before($subcol,'=')] = Str::after($subcol,'=');
        }
        $modelname = $this->modelindex[$tablename]['modelname'];
        $tablequery = $modelname::query();
        // from句
        $tablequery = $tablequery->from($tablename);
        foreach ($colset as $columnnama => $value) {
            $tablequery = $tablequery->where($tablename.'.'.$columnnama, '=', ''.$value.'');
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

    // Upload前のチェック
    public function checkForm($tablename, $form, $id) {
        $modelname = $this->modelindex[$tablename]['modelname'];
        if (!$id) {
            $targetrow = new $modelname;
        } else {
            $targetrow = $modelname::findOrFail($id);
        }
        $errors = $targetrow->fill($form)->check();
        return $errors->toArray();     
    }

    // table変更:store or update、実行かテスト:save or check
    // tablename:対象のテーブル
    // $form:挿入変更するカラムと値
    // $id:isnull->STORE,not null->UPDATE
    // $mode:save->実行してERRORを発生する、check->チェックしてTEXTを返す
    // return:ERRORであればException又はText、正常であれば$id
    public function excuteProcess($tablename, $form, $id, $mode){
        $modelname = $this->modelindex[$tablename]['modelname'];
        if (!$id) {
            $targetrow = new $modelname;
        } else {
            $targetrow = $modelname::findOrFail($id);
        }
        if ($mode=='save') {
            if($targetrow->fill($form)->save()) {
                return $targetrow->id;
            }
        } elseif ($mode=='check') {
            if($targetrow->fill($form)->check()) {
                return $targetrow->id;
            }
        } else {
            return false;
        }
    }

    // 登録実行
    public function createdId($tablename, $form) {
        $modelname = $this->modelindex[$tablename]['modelname'];
        $targetrow = new $modelname;
        $targetrow->fill($form)->save();
        return $targetrow->id;
    }

    // 更新実行
    public function is_Updated($tablename, $form, $id) {
        $modelname = $this->modelindex[$tablename]['modelname'];
        $targetrow = $modelname::findOrFail($id);
        $is_updated = $targetrow->fill($form)->save();
        return $is_updated;
    }

}