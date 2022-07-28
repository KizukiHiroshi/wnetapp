<?php
declare(strict_types=1);
namespace App\Services\Database;

class GetJancodeService {
    public function getJancode($value) {
        $strvalue = trim(strval($value));
        if (strlen($strvalue) >12) {
            return '0000000000000';
        }
        $rawcode = substr('000000000000'.$strvalue, -12);
        $checkdigit = (10 - ((intval(Substr($rawcode, 2, 1)) + intval(Substr($rawcode, 4, 1)) + 
        intval(Substr($rawcode, 6, 1)) + intval(Substr($rawcode, 8, 1)) + 
        intval(Substr($rawcode, 10, 1)) + intval(Substr($rawcode, 12, 1))) * 3 + 
        intval(Substr($rawcode, 1, 1)) + intval(Substr($rawcode, 3, 1)) + 
        intval(Substr($rawcode, 5, 1)) + intval(Substr($rawcode, 7, 1)) + 
        intval(Substr($rawcode, 9, 1)) + intval(Substr($rawcode, 11, 1))) % 10) % 10;    
        $jancode = $rawcode.trim(strval($checkdigit));
        return $jancode;
    }
}