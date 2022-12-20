<?php
declare(strict_types=1);
namespace App\Services\Table;

use App\Services\SessionService;

class GetSearchConditionsService {
    
    // $requestからtable_searchの情報を抽出してSessionに保存する
    public function getSearchConditions($request) {
        $sessionservice = new SessionService;
        $searchconditions =[];
        $rawparams = $request->all();
        foreach($rawparams as $rawname => $value) {
            if (substr($rawname, 0, 7) == 'search_') {
                if (substr($rawname, -3) == '_id' && $value == "0") {
                    // _id = 0 はセレクタの無選択
                    $value = null;
                }
                $searchconditions[substr($rawname, 7)] = $value;
            }
        }
        $sessionservice->putSession('searchconditions', $searchconditions);
        return $searchconditions;
    }
}

