<?php

// ServiceではIlluminate\Http\Requestにアクセスしない
// 汎用性のある関数を登録する

declare(strict_types=1);
namespace App\Services;

use Illuminate\Support\Facades\Auth;
use App\Services\SessionService;

class CommonService {

    private $sessionservice;
    public function __construct(SessionService $sessionservice) {
        $this->sessionservice = $sessionservice;
    }




    // Formに_byを加える
    public function addBytoForm($columnnames, $form, $mode) {
        $accountvalue = $this->sessionservice->getSession('accountvalue');
        if (!$accountvalue) {
            $username = ''; 
        } else {
            $username = array_key_exists('name', $accountvalue) ? $accountvalue['name'] : '';            
        }
        $username = $username == '' ? Auth::user()->name : $username;
        if (in_array('created_by', $columnnames) && $mode == 'store') {
            $form['created_by'] = $username;
        }        
        if (in_array('updated_by', $columnnames)) {
            $form['updated_by'] = $username;
        }        
        return $form;
    }
}