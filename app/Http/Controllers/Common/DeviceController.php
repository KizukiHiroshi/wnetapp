<?php
declare(strict_types=1);
namespace App\Http\Controllers\Common;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Service\Common\SessionService;

class DeviceController extends Controller {

    private $sessionservice;
    public function __construct(SessionService $sessionservice) {
        $this->sessionservice = $sessionservice;
    }

    public function index() {
        // 登録済のデバイスかどうか判断する
        // cookie取得
        // devicesへ照合
        if (true) {
            // 登録済みならdevicesからディスプレーの高さを取得してセッションに入れる
            // accountの確認へ
            return redirect('/account');
        } else {
            // ログインユーザー情報と共にデバイス登録画面へ
            // ログインユーザー情報取得
            $params = null;
            return view('common/device')->with($params);;
        }
    }

    // デバイス登録
    public function regist(Request $request) {
        
        // 最初のアクセス時のみディスプレーの高さを取得して、

        // デバイステーブル(devices?)に記入する
        $paginatecnt = 15;
        $this->sessionservice->putSession('paginatecnt', $paginatecnt);
        return redirect('/account');
        return view('common/components/get_screenheight');
    }

    public function store(Request $request) {
        // 登録済のデバイスかどうか判断する

        // ★未実装
        // 最初のアクセス時のみディスプレーの高さを取得して、

        // デバイステーブル(devices?)に記入する
        $paginatecnt = 15;
        $this->sessionservice->putSession('paginatecnt', $paginatecnt);
        return redirect('/account');
        return view('common/components/get_screenheight');
    }
}
