<?php
declare(strict_types=1);
namespace App\Services\Device;

use App\Services\Database\FindValueService;

class GetDeviceIdService 
{
    public function __construct() {
    }

    // 登録済のデバイスかチェックする
    public function getDeviceId($devicecookie) {
        // $foreginkey = 参照テーブル名?参照カラム名=urlencode(値)&参照カラム名=urlencode(値)
        $tablename = 'devices';
        $foreginkey = $tablename.'?name='.urlencode($devicecookie['name']).'&key='.urlencode($devicecookie['key']);
        $findvalueservice = new FindValueService;
        $deviceid = $findvalueservice->findValue($foreginkey, 'id');
        return $deviceid;
    }
}
