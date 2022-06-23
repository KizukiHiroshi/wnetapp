<?php
declare(strict_types=1);
namespace App\Services\Database;

use App\Services\Model\GetModelIndexService;

class IsRestoredService 
{
    public function __construct() {
    }

    // 削除実行
    public function IsRestored($tablename, $id) {
        $getmodelindexservice = new GetModelIndexService;
        $modelindex = $getmodelindexservice->getModelIndex();
        $modelname = $modelindex[$tablename]['modelname'];
        $targetrow = $modelname::withTrashed()->findOrFail($id);
        $is_restored = $targetrow->restore();
        return $is_restored;
    }
}