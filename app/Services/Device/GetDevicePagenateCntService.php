<?php
declare(strict_types=1);
namespace App\Services\Device;

use App\Services\SessionService;
use App\Services\Database\FindValueService;

class GetDevicePagenateCntService 
{
    public function __construct() {
    }

    // デバイスの名前からPagenateCntを取得する
    public function getDevicePagenateCnt() {
        $sessionservice = new SessionService;
        $devicename = $sessionservice->getSession('devicename');
        $devicepagenatecnt = 18;
        if (isset($devicename) && $devicename <> '') {
            // $foreginkey = 参照テーブル名?参照カラム名=urlencode(値)&参照カラム名=urlencode(値)
            $tablename = 'devices';
            $foreginkey = $tablename.'?name='.urlencode($devicename);
            $findvalueservice = new FindValueService;
            $devicepagenatecnt = $findvalueservice->findValue($foreginkey, 'pagenatecnt');
        }
        return $devicepagenatecnt;
    }
}
