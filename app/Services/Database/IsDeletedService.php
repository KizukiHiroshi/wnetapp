<?php
declare(strict_types=1);
namespace App\Services\Database;

use App\Services\Model\GetModelIndexService;

class IsDeletedService 
{
    public function __construct(){
    }

    // 削除実行
    public function isDeleted($tablename, $id){
        $getmodelindexservice = new GetModelIndexService;
        $modelindex = $getmodelindexservice->getModelIndex();
        $modelname = $modelindex[$tablename]['modelname'];
        $targetrow = $modelname::findOrFail($id);
        $is_deleted = $targetrow->delete();
        return $is_deleted;
    }
}