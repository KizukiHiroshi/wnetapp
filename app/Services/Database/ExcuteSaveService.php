<?php
declare(strict_types=1);
namespace App\Services\Database;

use Illuminate\Support\Facades\DB;
use App\Services\SessionService;

class ExcuteSaveService 
{

    public function __construct() {
    }

    // 汎用の登録・更新プロセス
    // tablename:対象のテーブル
    // $form:挿入変更するカラムと値
    // $id==0:STORE,$id!==0:UPDATE
    // return:ERRORであればException又はText、正常であれば$id
    public function excuteSave($tablename, $form, $id) {
        $sessionservice = new SessionService;
        $modelindex = $sessionservice->getSession('modelindex');
        $modelname = $modelindex[$tablename]['modelname'];
        if ($id == 0) {
            $targetrow = new $modelname;
        } else {
            $targetrow = $modelname::findOrFail($id);
        }
        DB::enableQueryLog();
        $targetrow->fill($form)->save();
        $tempsql = DB::getQueryLog();
        return $targetrow->id;
    }
}