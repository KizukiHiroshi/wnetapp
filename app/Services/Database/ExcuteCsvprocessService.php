<?php
declare(strict_types=1);
namespace App\Services\Database;

use App\Services\SessionService;

class ExcuteCsvprocessService 
{
    public function __construct(){
    }

    // upload用の登録・更新プロセス
    // tablename:対象のテーブル
    // $form:挿入変更するカラムと値
    // $id:0->STORE,not null->UPDATE
    // $mode:実行かテスト:save or check
    // save->失敗したらARRAYを返す、check->チェックしてARRAYを返す
    public function excuteCsvprocess($tablename, $form, $id, $mode){
        $sessionservice = new SessionService;
        $modelindex = $sessionservice->getSession('modelindex');
        $modelname = $modelindex[$tablename]['modelname'];
        if ($id == 0){
            $targetrow = new $modelname;
        } else {
            $targetrow = $modelname::findOrFail($id);
        }
        if ($mode == 'save'){
            $error = $targetrow->fill($form)->csvSave();
            return $error;
        } elseif ($mode == 'check'){
            $error = $targetrow->fill($form)->csvCheck();
            return $error;
        } else {
            return false;
        }
    }
}