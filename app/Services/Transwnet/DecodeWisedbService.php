<?php
declare(strict_types=1);
namespace App\Services\Transwnet;

use Illuminate\Support\Facades\DB;
use App\Services\Transwnet\TranswnetService;

class DecodeWisedbService {

    public function __construct() {
    }

    // 旧DB13,14の店コード、主体コード、仕入先コード、発注状態、送り状状態から
    // 必要な新テーブル、元、先を判断する
    // $newrecord[tablename => [main => code, sub =>code]]
    public function decodeWisedb($shopcode, $ownercode, $vendorcode, $ordestatus, $shipstatus) {
        $transwnetservice = new TranswnetService;
        $newparams = [];
        $shopcode_company = $transwnetservice->separateRawShopcode($shopcode);
        




        return $newparams;
    }


}
