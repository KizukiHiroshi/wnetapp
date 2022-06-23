<?php
declare(strict_types=1);
namespace App\Usecases\Jobmenu;

use App\Services\SessionService;

class MakeButtonListCase {

    public function __construct() {
    }

    public function makeButtonList() {
        $buttonlist =[];
        $sessionservice = new SessionService;
        $devicename = $sessionservice->getSession('devicename');
        $buttonlist['devicename'] = $devicename;
        return $buttonlist;
    }

}