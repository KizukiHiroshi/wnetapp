<?php
declare(strict_types=1);
namespace App\Usecases\Table;

use App\Services\Database\ExcuteProcessService;
use App\Services\Table\GetFormFromRequestService;

class ExcuteCase  {

    public function __construct() {
    }

    public function Excute($request, $sqlmode) {
        $getformfromrequestservice = new GetFormFromRequestService;
        $form = $getformfromrequestservice->getFormFromRequest($request, $sqlmode);
        // 登録実行
        $tablename = $request->tablename;
        if ($sqlmode == 'store') {
            $id = 0;
        } elseif ($sqlmode == 'update') {
            $id = $request->id;
        }
        // 汎用の登録・更新プロセス
        $excuteprocessservice = new ExcuteProcessService;
        $excutedid = $excuteprocessservice->excuteProcess($tablename, $form, $id);
        return $excutedid;
    }
}

