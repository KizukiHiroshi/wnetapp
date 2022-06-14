<?php
// ServiceではIlluminate\Http\Requestにアクセスしない
// 汎用性のある関数を登録する

declare(strict_types=1);
namespace App\Services\Database;

use Illuminate\Support\Facades\Auth;
use App\Services\SessionService;

class Get_ByNameService {
    private $byname;
    private $sessionservice;
    public function __construct(SessionService $sessionservice) {
        $this->sessionservice = $sessionservice;
    }

    // Formで〇〇_byに使用する名前を取得する
    public function Get_ByName() {
        $accountvalue = $this->sessionservice->getSession('accountvalue');
        if (!$accountvalue) {
            $this->byname = ''; 
        } else {
            $this->byname = array_key_exists('name', $accountvalue) ? $accountvalue['name'] : '';            
        }
        $this->byname = $this->byname == '' ? Auth::user()->name : $this->byname;  
        return $this->byname;
    }
}