<?php
declare(strict_types=1);
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Usecases\Account\ConfirmCase;

class AccountController extends Controller {

    public function __construct() {
    }

    public function index(Request $request) {
        $confirmcase = new ConfirmCase;
        $memberid = $request->memberid;
        if (!$memberid) {
            // ログインidに複数の従業員がいるかチェックする
            $memberid = $confirmcase->checkMemberId();
            if ($memberid == 'many') {
                // いれば従業員を特定する
                // return view();
            }
        }
        // 従業員の業務内容権限を取得する
        $accountvalue = $confirmcase->getAccountValue($memberid);
        // $accountvalueをSessionに保存する
        $confirmcase->putAccountValueToSession($accountvalue);
        // jobmenuを表示するパラメーターを得る
        $params = $confirmcase->getParams($accountvalue);
        return view('common/jobmenu')->with($params);
    }
}