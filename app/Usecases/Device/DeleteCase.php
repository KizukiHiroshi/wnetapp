<?php
declare(strict_types=1);
namespace App\Usecases\Device;

use Illuminate\Support\Facades\Cookie;
use App\Services\Device\GetDeviceCookieService;
use App\Services\Device\GetDeviceIdService;
use App\Services\Database\IsForceDeletedService;

class DeleteCase {

    private $getdevicecookieservice;
    public function __construct(
        GetDeviceCookieService $getdevicecookieservice,
        GetDeviceIdService $getdeviceidservice,
        IsForceDeletedService $isforcedeletedservice){
            $this->getdevicecookieservice = $getdevicecookieservice;
            $this->getdeviceidservice = $getdeviceidservice;
            $this->isforcedeletedservice = $isforcedeletedservice;
    }

    // deviceの削除
    public function deleteDevice(){
        $devicecookie = $this->getdevicecookieservice->getDeviceCookie();
        // テーブルに登録済のデバイスを削除する
        $this->deleteRegistedDevice($devicecookie);
        // cookieの削除
        $this->deleteDeviceCookie($devicecookie);
    }

    // テーブルに登録済のデバイスを削除する
    private function deleteRegistedDevice($devicecookie){
        // devicesテーブルへの登録確認
        $deviceid = $this->getdeviceidserveise->getDeviceId($devicecookie);
        if ($deviceid){
            $tablename = 'devices';
            $this->isforcedeletedservice->isForceDeleted($tablename, $deviceid);
        }
    }

    // cookieの削除
    private function deleteDeviceCookie($devicecookie ){
        if ($devicecookie['name'] !== ''){
            try {
                // cookieの削除
                Cookie::queue(Cookie::forget('devicename'));
                Cookie::queue(Cookie::forget('devicekey'));
            } catch ( \Exception $e){
                // ★エラー処理の方法未知
                report($e);
                session()->flash('flash_message', '更新が失敗しました');
            }
        }
    }
}
