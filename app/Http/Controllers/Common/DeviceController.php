<?php
declare(strict_types=1);
namespace App\Http\Controllers\Common;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Service\Common\DeviceService;

class DeviceController extends Controller {

    private $deviceservice;
    public function __construct(DeviceService $deviceservice) {
        $this->deviceservice = $deviceservice;
    }

    public function index() {
        // デバイス名が設定されているか確認する
        $name = $this->deviceservice->comfirmDeviceName();
        if ($name) {
            $devicekey = $this->deviceservice->getDevicekeyCookie();
            // devicesテーブルへの登録を確認する
            $deviceid = $this->deviceservice->getRegistedDevice($name, $devicekey);
            if ($deviceid) {
                // 使用可能なレコードか確認する
                $tablename = 'devices';
                $is_availableid = $this->deviceservice->isAvailableId($deviceid);
                if ($is_availableid) {
                    // accountの確認へ
                    return redirect('/account');
                } else {
                    // 未認可の知らせ
                    $errormsg = $name.'のアクセスは未認可です。管理者にお問い合わせください。';
                    return view('common/alert',compact('errormsg'));
                }
            }
        }
        // 設定されていなければ設定画面へ遷移
        return view('common/device');
    }

    // デバイス名・キーを保存・登録
    public function setname(Request $request) {
        $name = $request->name;
        $is_regitedname = $this->deviceservice->checkIsRegistedName($name);
        if (!$is_regitedname) {
            $tablename = 'devices';
            $form = $this->deviceservice->getDeviceForm($request);
            $id = null;
            // 汎用の登録・更新プロセス 
            $createdid = $this->deviceservice->excuteProcess($tablename, $form, $id);
            if ($createdid) {
                // デバイスへのCookie登録
                $this->deviceservice->setDeviceCookie($form);
                // 管理者へメール送信
                $this->deviceservice->sendDeviceRequestMail($name);
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
        $this->deviceservice->deleteDeviceCookie();
        $success = '登録を削除しました';
        return view('common/alert',compact('success'));
}
}
