<?php
declare(strict_types=1);
namespace App\Services\Table;

use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class OpimizeRawformWithIddictionaryService {

    public function __construct(){
    }

    // uploadされたリストをiddictionary参照利用して登録可能な配列に替える
    public function opimizeRawformWithIddictionary($tablename, $rawform, $foreginkeys, $iddictionary){
        $form = [];
        // ★ Columnspropと比較して、数値にはnullを
        $columnnames = Schema::getColumnListing($tablename);
        foreach ($rawform as $key => $value){
            if (in_array($key, $columnnames) && substr($key,-3) !== '_at'){
                $form[$key] = $value == '' ? null : $value;
            }
        }
        foreach ($foreginkeys as $foreginkey){
            $foregintablename = Str::singular(Str::before($foreginkey,'?')).'_id';
            if (in_array($foregintablename, $columnnames)){
                $form[$foregintablename] = $iddictionary[$foreginkey];
            }
        }
        return $form;
    }
}
