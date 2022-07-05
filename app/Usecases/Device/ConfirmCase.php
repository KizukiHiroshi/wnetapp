<?php
declare(strict_types=1);
namespace App\Usecases\Device;

use App\Services\SessionService;
use App\Services\Database\FindValueService;
use App\Services\Database\IsAvailableIdService;
use App\Services\Device\GetDeviceCookieService;

class ConfirmCase
{
    private $sessionservice;
    private $findidservice;
    private $isavailableidservice;
    private $getdevicecookieservice;
    public function __construct(
        SessionService $sessionservice,
        IsAvailableIdService $isavailableidservice,
        GetDeviceCookieService $getdevicecookieservice) {
            $this->sessionservice = $sessionservice;
            $this->isavailableidservice = $isavailableidservice;
            $this->getdevicecookieservice = $getdevicecookieservice;
    }

    // デバイスクッキー設定を取得する
    public function getDeviceCookie() {
        $devicecookie = $this->getdevicecookieservice->getDeviceCookie();
        return $devicecookie;
    }

    // 登録済のデバイスかチェックする
    public function getDeviceId($devicecookie) {
        // $findvalueset = 参照テーブル名?参照カラム名=urlencode(値)&参照カラム名=urlencode(値)
        $tablename = 'devices';
        $findvalueset = $tablename.'?name='.urlencode($devicecookie['name']).'&key='.urlencode($devicecookie['key']);
        $findvalueservice = new FindValueService;
        $deviceid = $findvalueservice->findValue($findvalueset, 'id');
        return $deviceid;
    }

    // 使用可能なレコードか確認する
    public function isAvailableId($deviceid) {
        $tablename = 'devices';
        $is_availableid = $this->isavailableidservice->isAvailableId($tablename, $deviceid);
        return $is_availableid;
    }

    // sessionにdevice名を入れる
    public function putSession($devicename) {
        $this->sessionservice->putSession('devicename', $devicename);
    }
}
