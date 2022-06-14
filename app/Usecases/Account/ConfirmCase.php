<?php
declare(strict_types=1);
namespace App\Usecases\Account;

use App\Services\SessionService;
use App\Services\Database\FindIdService;
use App\Services\Database\IsAvailableIdService;

class ConfirmCase
{
    private $sessionservice;
    private $findidservice;
    private $isavailableidservice;
    public function __construct(
        SessionService $sessionservice,
        FindIdService $findidservice,
        IsAvailableIdService $isavailableidservice) {
            $this->sessionservice = $sessionservice;
            $this->findidservice = $findidservice;
            $this->isavailableidservice = $isavailableidservice;
     }

    // デバイスクッキー設定を取得する
    public function getDeviceCookie() {
        $devicecookie = $this->getdevicecookieservice->getDeviceCookie();
        return $devicecookie;
    }



}
