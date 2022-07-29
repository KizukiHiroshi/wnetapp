<?php
declare(strict_types=1);
namespace App\Services\Database;

class GetNoHeaderColumnnameService {
    public function getNoHeaderColumnname($columnname) {
        $truecolumnname = $columnname;
        if(strpos($columnname, "__") > 0) {
            $truecolumnname = substr($columnname, strpos($columnname, "__") +2);
        }
        return $truecolumnname;
    }
}