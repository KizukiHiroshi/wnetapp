<?php
declare(strict_types=1);
namespace App\Http\Controllers\Common;

use App\Http\Controllers\Controller;
use App\Service\Common\SessionService;

class AccountController extends Controller {

    private $sessionservice;
    public function __construct(SessionService $sessionservice) {
        $this->sessionservice = $sessionservice;
    }

    public function index() {
        // accountuserを決める
        // ★未実装
        // accountusersに同じuser_idを検索して、1件だったらそれを取得=個人アカウント
        // 複数あったら事業所アカウントなので、個人を限定するセレクタを表示する
        $accountuser = '杵築(弘)';
        $accountuserid = 1;
        $this->sessionservice->putSession('accountuser', $accountuser);
        $this->sessionservice->putSession('accountuserid', $accountuserid);
        return view('common/menu');
    }
}
