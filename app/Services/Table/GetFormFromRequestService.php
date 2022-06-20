<?php
declare(strict_types=1);
namespace App\Services\Table;

use App\Services\Database\Get_ByNameService;
use App\Services\Database\Add_ByNameToFormService;
use Illuminate\Support\Facades\Schema;

class GetFormFromRequestService  {

    public function __construct(){
    }

    // requestをtableに登録可能な配列に替える
    public function getFormFromRequest($request, $mode){
        $form = [];
        $tablename = $request->tablename;
        $rawform = $request->all();
        $columnnames = Schema::getColumnListing($tablename);
        foreach ($rawform as $key => $value){
            if ($mode == 'store' && $key == 'id'){
                // store時のidは除外
            } elseif (in_array($key, $columnnames) && substr($key,-3) !== '_at'){
                $form[$key] = $value;
            }
        }
        $get_bynameservice = new Get_ByNameService;
        $byname = $get_bynameservice->Get_ByName();
        $add_bynametoformservice = new Add_ByNameToFormService;
        $form = $add_bynametoformservice->add_ByNameToForm($byname, $form, $columnnames, $mode);
        return $form;
    }
}

