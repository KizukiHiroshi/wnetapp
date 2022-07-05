<?php
declare(strict_types=1);
namespace App\Usecases\Account;

use Illuminate\Support\Facades\Auth;

use App\Services\SessionService;
use App\Services\Database\FindValueService;

class ConfirmCase
{
    public function __construct() {
    }

    // ログインidに複数の従業員がいるかチェック
    public function checkMemberId() {
        $memberid = 0;
        $userid = Auth::id();
        $tablename = 'members';
        // $findvalueset = 参照テーブル名?参照カラム名=urlencode(値)&参照カラム名=urlencode(値)
        $findvalueset = $tablename.'?user_id='.urlencode(strval($userid));
        $findvalueservice = new FindValueService;
        $memberid = $findvalueservice->findValue($findvalueset, 'id');
        return $memberid;
    }

    // accountを確認する ★★未実装
    public function getAccountValue($memberid) {
        $accountvalue = [];
        // アカウント名
        // アカウントとして持ってる機能
        // 所属事業所が持ってる機能
        // 部門から賦与された機能
        // $tablename = 'accounts';
        // // $findvalueset = 参照テーブル名?参照カラム名=urlencode(値)&参照カラム名=urlencode(値)
        // $findvalueset = $tablename.'?member_id='.urlencode(strval($memberid));
        // $findvalueservice = new FindValueService;
        // $accountid = $findvalueservice->findValue($findvalueset, 'id');
        // $getrowbyidservice = new GetRowByIdService;
        // $row = $getrowbyidservice->getRowById('accounts', $accountid);
        $accountvalue['name'] = '杵築(弘)';
        $accountvalue['memberid'] = 1;
        return $accountvalue;
    }

    // $accountvalueをSessionに保存する
    public function putAccountValueToSession($accountvalue) {
        $sessionservice = new SessionService;
        $sessionservice->putSession('accountvalue', $accountvalue);
    }

    // jobmenuを表示するパラメーターを得る
    public function getParams($accountvalue) {
        $params = [];
        $sessionservice = new SessionService;
        $devicename = $sessionservice->getSession('devicename');
        $params['devicename'] = $devicename;
        return $params;
    }

}
