<?php
declare(strict_types=1);
namespace App\Services;

class SessionService
{
    public function __construct() {
    }
    
    // Session使用が許された変数
    private $wnetsessions = [
        'modelindex'    => ['service' => 'Model\GetModelIndexService', 'function' => 'getModelIndex'],
        'columnsprop'   => ['service' => 'Table\GetColumnsPropService', 'function' => 'getColumnsProp'],
        'paginatecnt'   => ['service' => 'Device\GetDevicePagenateCntService', 'function' => 'getDevicePagenateCnt'],
        'tablename'     => ['service' => '', 'redirect' => ''],
        'lastsort'      => ['service' => '', 'redirect' => ''],
        'page'          => ['service' => '', 'redirect' => ''],
        'tempsql'       => ['service' => '', 'redirect' => ''],
        'iddictionary'  => ['service' => '', 'redirect' => ''],
        'searchinput'   => ['service' => '', 'redirect' => ''],
        'screen_height' => ['service' => '', 'redirect' => ''],
        'accountvalue'  => ['service' => '', 'redirect' => ''],
        'devicename'    => ['service' => '', 'redirect' => ''],
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