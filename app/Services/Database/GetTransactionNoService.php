<?php
declare(strict_types=1);
namespace App\Services\Database;

use App\Services\SequenceService;
use App\Services\Database\GetJancodeService;

// sequenceとnowstringでJAN機能のあるコードを作る
class GetTransactionNoService {
    public function getTransactionNo($key, $rawyear) {
        $sequenceservice = new SequenceService;
        $sequence = $sequenceservice->getNewNo($key);
        $value = strval($rawyear).substr('000000'.strval($sequence), -6);
        $getjancodeservice = new GetJancodeService;
        $value = $getjancodeservice->getJancode($value);
        $transaction_no = $this->deleteLeftZero($value);
        return $transaction_no;
    }

    // 先頭の'0'を消して数値にする
    private function deleteLeftZero($value) {
        while (substr($value, 0, 1) == '0') {
            $value = substr($value, 1);
        }
        return $value;
    }
}