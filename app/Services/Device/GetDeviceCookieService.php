<?php
declare(strict_types=1);
namespace App\Services\Device;
use Illuminate\Support\Facades\Cookie;

class GetDeviceCookieService {
    public function __construct() {
    }
    // デバイスクッキー設定を取得する
    public function getDeviceCookie() {
        $name = Cookie::get('devicename');
        $key = Cookie::get('devicekey');
        $devicecookie['name'] = !$name ? '' : $name;
        $devicecookie['key'] = !$key ? '' : $key;
        return $devicecookie;
    }

    // デバイスクッキー設定を取得する
    public function getDeviceName() {
        $devicename = Cookie::get('devicename');
        return $devicename;
    }
}
