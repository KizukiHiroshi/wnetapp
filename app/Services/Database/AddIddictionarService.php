<?php
declare(strict_types=1);
namespace App\Services\Database;

use App\Services\Database\FindValueService;

class AddIddictionarService {
    // Formに_byを加える
    public function addIddictionary($iddictionary, $foreginkey) {
        if (array_key_exists($foreginkey, $iddictionary)) {
            // 登録済なら何もしない
        } else {
            // 未登録の参照を$iddictionaryに追加する
            $findvalueservice = new FindValueService;
            $id = $findvalueservice->findValue($foreginkey, 'id');
            if ($id == 0) {
                $id = NULL;
            }
            $iddictionary[$foreginkey] = $id;
        }
        return $iddictionary;
    }
}