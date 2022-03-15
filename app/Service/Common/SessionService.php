<?php

// ServiceではIlluminate\Http\Requestにアクセスしない
// Sessionの多用を避けるためにAccessは全てここを経由する

declare(strict_types=1);
namespace App\Service\Common;

class SessionService
{
    public function __construct() {

    }
    
    // Session使用が許された変数
    private $wnetsessions = [
        'modelindex'    => ['service' => 'ModelService', 'function' => 'getModelindex'],
        'modelselects'  => ['service' => 'ModelService', 'function' => 'getModelselects'],
        'columnsprop'   => ['service' => 'ModelService', 'function' => 'getColumnsProp'],
        'tablename'     => ['service' => ''],
        'lastsort'      => ['service' => ''],
        'page'          => ['service' => ''],
        'downloadsql'   => ['service' => ''],
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
        if ($value['service']!='') {
            $classname = 'App\Service\Common\\'.$value['service'];
            $tempservice = new $classname;
            $func = $value['function'];
            $sessionvalue = $tempservice->$func(...$params);
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