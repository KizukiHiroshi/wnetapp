<?php
declare(strict_types=1);
namespace App\Services\Database;

use Illuminate\Support\Facades\Auth;
use App\Services\SessionService;

class Get_ByNameService {
    private $byname;
    public function __construct(){
    }

    // Formで〇〇_byに使用する名前を取得する
    public function Get_ByName(){
        $sessionservice = new SessionService;
        $accountvalue = $sessionservice->getSession('accountvalue');
        if (!$accountvalue){
            $this->byname = ''; 
        } else {
            $this->byname = array_key_exists('name', $accountvalue) ? $accountvalue['name'] : '';            
        }
        $this->byname = $this->byname == '' ? Auth::user()->name : $this->byname;  
        return $this->byname;
    }
}