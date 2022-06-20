<?php
declare(strict_types=1);
namespace App\Services\Database;

use App\Services\Model\GetModelIndexService;

class IsForceDeletedService 
{
    public function __construct(){
    }

    // 完全削除実行
    public function isForceDeleted($tablename, $id){
        $getmodelindexservice = new GetModelIndexService;
        $modelindex = $getmodelindexservice->getModelIndex();
        $modelname = $modelindex[$tablename]['modelname'];
        $targetrow = $modelname::withTrashed()->findOrFail($id);
        $is_forceDeleted = $targetrow->forceDelete();
        return $is_forceDeleted;
    }
}