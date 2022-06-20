<?php
declare(strict_types=1);
namespace App\Services\Table;

use App\Services\SessionService;

class SessionOptimizeService {

    public function __construct(){
    }
    
    // $requestの状態からSessionを適正化する
    public function sessionOptimize($request){
        // テーブル名が更新されている時は既存のtable関連Sessionを消す
        $tablename = $request->tablename;
        $sessionservice = new SessionService;
        $lasttablename = $sessionservice->getSession('tablename');
        if ($lasttablename !== $tablename){
            $sessionservice->putSession('tablename', $tablename);
            $sessionservice->forgetSession('columnsprop');
            $sessionservice->forgetSession('searchinput');
            $sessionservice->forgetSession('lastsort');
            $sessionservice->forgetSession('page');
        }
    }
}

