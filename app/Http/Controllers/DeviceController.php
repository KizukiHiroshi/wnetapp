<?php
declare(strict_types=1);
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Usecases\Device\DeviceCase;

class DeviceController extends Controller {

    private $devicecase;
    public function __construct(DeviceCase $devicecase) {
        $this->devicecase = $devicecase;
    }

    public function index() {
        // デバイス名が設定されているか確認する
        $devicename = $this->devicecase->comfirmDeviceName();
        if (!$devicename) {
            // 設定されていなければ設定画面へ遷移
            return view('common/device');
        }
        $devicekey = $this->devicecase->getDevicekeyCookie();
        // devicesテーブルへの登録を確認する
        $deviceid = $this->devicecase->getRegistedDevice($devicename, $devicekey);
        if ($deviceid) {
            // 使用可能なレコードか確認する
            $tablename = 'devices';
            $is_availableid = $this->devicecase->isAvailableId($deviceid);
            if ($is_availableid) {
                // accountの確認へ
                return redirect('/account');
            } else {
                // 未認可の知らせ
                $errormsg = $devicename.'のアクセスは未認可です。管理者にお問い合わせください。';
                return view('common/alert',compact('errormsg'));
            }
        }
    }

    // デバイス名・キーを保存・登録
    public function setname(Request $request) {
        $name = $request->name;
        $is_regitedname = $this->devicecase->checkIsRegistedName($name);
        if (!$is_regitedname) {
            $tablename = 'devices';
            $form = $this->devicecase->getDeviceForm($request);
            $id = null;
            // 汎用の登録・更新プロセス 
            $createdid = $this->devicecase->excuteProcess($tablename, $form, $id);
            if ($createdid) {
                // デバイスへのCookie登録
                $this->devicecase->setDeviceCookie($form);
                // 管理者へメール送信 ★未実装
                // $this->devicecase->sendDeviceRequestMail($name);
                // 完了メッセージ
                $success = '登録しました。管理者からの承認メールをお待ちください。';
                return view('common/alert',compact('success'));
            }
        } else {
            $errormsg = 'その名前は既に使用されているので登録できません';
            return view('common/device',compact('errormsg'));
        }
    }

    // デバイス情報のクッキーを削除する
    public function delete() {
        $this->devicecase->deleteDeviceCookie();
        $success = '登録を削除しました';
        return view('common/alert',compact('success'));
}
}
