<?php
declare(strict_types=1);
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Usecases\Device\ConfirmCase;
use App\Usecases\Device\RegistCase;
use App\Usecases\Device\DeleteCase;

class DeviceController extends Controller {

    private $confirmcase;
    private $registcase;
    private $deletecase;
    public function __construct(
        ConfirmCase $confirmcase,
        RegistCase $registcase,
        DeleteCase $deletecase){
            $this->confirmcase = $confirmcase;
            $this->registcase = $registcase;
            $this->deletecase = $deletecase;
        }

    public function index(){
        // デバイス名が設定されているか確認する
        // デバイスクッキー設定を取得する
        $devicecookie = $this->confirmcase->getDeviceCookie();
        if ($devicecookie['name'] == ''){
            // 設定されていなければ設定画面へ遷移
            return view('common/device_regist');
        }
        // devicesテーブルへの登録を確認する
        $deviceid = $this->confirmcase->getDeviceId($devicecookie);
        if ($deviceid){
            // 使用可能なレコードか確認する
            $is_availableid = $this->confirmcase->isAvailableId($deviceid);
            if ($is_availableid){
                // sessionにdevice名を入れる
                $this->confirmcase->putSession($devicecookie['name']);
                // ■■■■ accountの確認へ進む ■■■■
                return redirect('/account');
            } else {
                // idが未認可を知らせる
                $danger = $devicecookie['name'].'のアクセスは未認可です。管理者にお問い合わせください。';
                return view('common/alert',compact('danger'));
            }
        }
    }

    // デバイス名・キーを保存・登録
    public function regist(Request $request){
        // 登録済のデバイス名と重複しないかチェックする
        $is_regitedname = $this->registcase->isRegistedName($request);
        if ($is_regitedname){
            $danger = 'その名前は既に使用されているので登録できません';
            return view('common/device_regist',compact('danger'));
        } else {
            // devicesテーブルへ登録する
            $createdid = $this->registcase->registDevice($request);
            if ($createdid !== 0){
                // デバイスへのCookie登録
                $this->registcase->setDeviceCookie($request);
                // 管理者へメール送信 ★未実装
                $this->registcase->sendDeviceRequestMail($request);
                $sucsess = 'アクセス機器を登録しました。';
                return view('common/alert', compact('sucsess'));
           } else {
                // 失敗メッセージ
                $danger = 'アクセス機器の登録に失敗しました。';
                return view('common/alert', compact('danger'));
            }
        }
    }

    // デバイス情報のクッキーを削除する
    public function delete(){
        $this->deletecase->deleteDevice();
        $success = '登録を削除しました';
        return view('common/alert',compact('success'));
    }
}
