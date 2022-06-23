<?php
declare(strict_types=1);
namespace App\Services\Database;

use Illuminate\Support\Facades\Schema;
use App\Services\SessionService;


class IsAvailableIdService 
{
    public function __construct() {
    }

    // 使用可能なレコードか確認する
    public function isAvailableId($tablename, $id) {
        $sessionservice = new SessionService;
        $modelindex = $sessionservice->getSession('modelindex');
        $modelname = $modelindex[$tablename]['modelname'];
        $targetrow = $modelname::withTrashed()->findOrFail($id);
        if (!$targetrow) {
            return false; 
        } else {
            $columnnames = Schema::getColumnListing($tablename);
            if (in_array('deleted_at', $columnnames)) {
                if ($targetrow->deleted_at !== null) { return false; }
            }
            if (in_array('start_on', $columnnames)) {
                if ($targetrow->start_on == null || $targetrow->start_on > date("Y-m-d")) { return false; }
            }
            if (in_array('end_on', $columnnames)) {
                if ($targetrow->end_on == null || $targetrow->end_on < date("Y-m-d")) { return false; }
            }
        } 
        return true;
    }
}