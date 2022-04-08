<?php

// ServiceではIlluminate\Http\Requestにアクセスしない
// Modelから取得したデータを整理する

declare(strict_types=1);
namespace App\Service\Common;

class DeviceService {

    public function __construct() {
    }

    // 登録済のデバイスかどうかの確認
    public function isRegistedDevice() {
        // cookieの存在確認
        // devicesと照合
        return true;
    }

    // デバイスの登録
    public function registDevice() {
        // devicesへの登録
    }

    // デバイス登録の削除
    public function unregistDevice() {
        
    }
}
