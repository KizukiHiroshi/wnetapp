<?php
declare(strict_types=1);
namespace App\Services\Table;

class ArangeCardColumnspropWithForeignSelectsService {

    public function __construct() {
    }

    // foreignselectsがNULLの場合に$cardcolumnspropの該当columnをリファレンスするはずだった生カラムに戻す
    public function arangeCardColumnspropWithForeignSelects($cardcolumnsprop, $foreignselects, $columnsprop) {
        $arangecardcolumnsprop = [];
        foreach ($cardcolumnsprop AS $cardcolumnname => $prop) {
            // cardcolumnnameがforeignselectで空だったら
            if (array_key_exists($cardcolumnname, $foreignselects) 
                && $foreignselects[$cardcolumnname] == NULL) {
                // $cardcolumnspropの該当columnをリファレンスするはずだった生カラムに戻す
                $beforereferencename = str_replace('_reference', '', $cardcolumnname);
                foreach ($columnsprop AS $columnname => $prop) {
                    // referenceの対象カラムを探して元のcolumnspropに戻す
                    if (strripos($columnname, '_id_') 
                        && strpos($columnname, '_id_2nd') == false 
                        && substr($columnname, -3) !== '_id') {
                        if (substr($columnname, 0, strlen($beforereferencename)) == $beforereferencename) {
                            $arangecardcolumnsprop[$columnname] = $prop;
                        }
                    }
                }
            } else {
                $arangecardcolumnsprop[$cardcolumnname] = $prop;
            }
        }
        return $arangecardcolumnsprop;
    }
}
