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
        // $findvalueset =  参照テーブル名?参照カラム名=値&参照カラム名=値
        $tablename = 'devices';
        $findvalueset = $tablename.'?name='.$devicecookie['name'].'&key='.$devicecookie['key'];
        $findvalueservice = new FindValueService;
        $deviceid = $findvalueservice->findValue($findvalueset, 'id');
        return $deviceid;
    }
}
