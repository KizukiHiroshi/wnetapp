<?php
declare(strict_types=1);
namespace App\Services\Database;

use App\Services\SessionService;

class ExcuteProcessService 
{
    public function __construct(){
    }

    // 汎用の登録・更新プロセス
    // tablename:対象のテーブル
    // $form:挿入変更するカラムと値
    // $id==0:STORE,$id!==0:UPDATE
    // return:ERRORであればException又はText、正常であれば$id
    public function excuteProcess($tablename, $form, $id){
        $sessionservice = new SessionService;
        $tmodelindex = $sessionservice->getSession('modelindex');
        $modelname = $tmodelindex[$tablename]['modelname'];
        if ($id == 0){
            $targetrow = new $modelname;
        } else {
            $targetrow = $modelname::findOrFail($id);
        }
        $targetrow->fill($form)->save();
        return $targetrow->id;
    }

}