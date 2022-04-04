<?php
declare(strict_types=1);
namespace App\Http\Controllers\Common;

use App\Http\Controllers\Controller;
use App\Service\Common\SessionService;
use Illuminate\Http\Request;

class MenuController extends Controller {

    private $sessionservice;
    public function __construct(SessionService $sessionservice) {
        $this->sessionservice = $sessionservice;
    }

    public function index(Request $request) {
        // accountuserを決める
        // 未実装
        $accountuser = '杵築(弘)';
        $accountuserid = 1;
        $this->sessionservice->putSession('accountuser', $accountuser);
        $this->sessionservice->putSession('accountuserid', $accountuserid);
        return view('common/menu');
    }
}
