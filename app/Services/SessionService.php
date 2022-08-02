<?php
declare(strict_types=1);
namespace App\Services;

class SessionService
{
    public function __construct() {
    }
    
    // Session使用が許された変数
    // 初期値があればredirectに
    private $wnetsessions = [
        'modelindex'        => ['service' => 'Model\GetModelIndexService', 'function' => 'getModelIndex'],
        'columnsprop'       => ['service' => 'Table\GetColumnsPropService', 'function' => 'getColumnsProp'],
        'cardcolumnsprop'   => ['service' => 'Table\GetCardColumnspropService', 'function' => 'getCardColumnsprop'],
        'paginatecnt'       => ['service' => 'Device\GetDevicePagenateCntService', 'function' => 'getDevicePagenateCnt'],
        'devicename'        => ['service' => 'Device\GetDeviceCookieService', 'function' => 'getDeviceName'],
        'tablename'         => ['is_array' => false, 'service' => '', 'redirect' => ''],
        'page'              => ['is_array' => false, 'service' => '', 'redirect' => ''],
        'tempsql'           => ['is_array' => false, 'service' => '', 'redirect' => ''],
        'screen_height'     => ['is_array' => false, 'service' => '', 'redirect' => ''],
        'lastsort'          => ['is_array' => false, 'service' => '', 'redirect' => ''],
        'iddictionary'      => ['is_array' => true, 'service' => '', 'redirect' => ''],
        'searchinput'       => ['is_array' => true, 'service' => '', 'redirect' => ''],
        'accountvalue'      => ['is_array' => true, 'service' => '', 'redirect' => ''],
    ];

    // 外部からのSession呼び出しに答える
    public function getSession($sessionname, ...$params) {
        if (session($sessionname)) {
            return session($sessionname);
        } else {
            $sessionvalue = null;
            foreach ($this->wnetsessions AS $wanetsession => $value) {
                if ($sessionname == $wanetsession) {
                    $sessionvalue = $this->makeSessionvalue($value, ...$params);
                    break;
                }
            }
            if ($sessionvalue) {
                session([$sessionname => $sessionvalue]);
            }
            return $sessionvalue;
        }
    }

    private function makeSessionvalue($value, ...$params) {
        $sessionvalue = null;
        if ($value['service'] !== '') {
            $classname = 'App\Services\\'.$value['service'];
            $tempservice = new $classname;
            $func = $value['function'];
            $sessionvalue = $tempservice->$func(...$params);
        } elseif ($value['redirect'] !== '') {
            redirect($value['redirect']);
        } elseif ($value['is_array'] == true) {
            $sessionvalue = [];
        }
        return $sessionvalue;
    }

    // 外部からのsession作成要請に答える
    public function putSession($sessionname, $sessionvalue) {
        if (array_key_exists($sessionname, $this->wnetsessions)) {
            session([$sessionname => $sessionvalue]);
        }
    }

    // 外部からのsession削除要請に答える
    public function forgetSession($sessionname) {
        if (session()->has($sessionname)) {
            session()->forget($sessionname);
        }
    }
}