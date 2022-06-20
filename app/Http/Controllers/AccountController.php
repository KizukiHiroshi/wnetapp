<?php
declare(strict_types=1);
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Usecases\Account\ConfirmCase;

class AccountController extends Controller {

    public function __construct(){
    }

    public function index(Request $request){
        $confirmservice = new ConfirmCase;
        $memberid = $request->memberid;
        if (!$memberid){
            // ログインidに複数の従業員がいるかチェックする
            $memberid = $confirmservice->checkMemberId();
            if ($memberid == 'many'){
                // いれば従業員を特定する
                // return view();
            }
        }
        // 従業員の業務内容権限を取得する
        $accountvalue = $confirmservice->getAccountValue($memberid);
        // $accountvalueをSessionに保存する
        $confirmservice->putAccountValueToSession($accountvalue);
        // jobmenuを表示するパラメーターを得る
        $params = $confirmservice->getParams($accountvalue);
        return view('common/jobmenu')->with($params);
    }
}