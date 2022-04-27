<?php

// ServiceではIlluminate\Http\Requestにアクセスしない
// 汎用性のある関数を登録する

declare(strict_types=1);
namespace App\Service\Common;
use Illuminate\Support\Facades\Storage;

class CommonService {

    // $nを$bで囲む
    public function brackets($n, $b){
        return $b.$n.$b;
    }
      
    // $requestから$columnspropに合致する要素を抽出する
    public function getOldinput($request, $columnsprop) {
        $oldinput =[];
        foreach ($columnsprop as $columnsname => $prop) {
            $oldinput[$columnsname] = $request->$columnsname;
        }
        return $oldinput;
    }

    // 新規配列の内、既知のKeyで既存配列に存在しないKeyValueを既存配列に追加する
    public function addArrayIfknownsKeyAndNotExist($newarray, $temparray, $knownkeys) {
        foreach ($newarray as $key => $value) {
            if (!array_key_exists($key, $temparray) && in_array($key, $knownkeys)) {
                $temparray[$key] = $value;
            }
        }
        return $temparray;
    }

    // wnetapp\storage\app\public\csv内の10分以上前にuploadされたファイルを削除する
    public function killOldfile() {
        $setminuts = 10;
        $files = Storage::allFiles('public/csv/');;
        foreach ($files as $file) {
            $updatedtime = Storage::lastModified($file);;
            if ((time()-$updatedtime)/60 > $setminuts && substr($file, -4) == '.csv') {
                Storage::delete($file);
            }
        }
    }

    // wnetapp\storage\app\public\csv内の$useridがuploadしたファイルを削除する
    public function killMyfile($userid) {
        $files = Storage::allFiles('public/csv/');;
        foreach ($files as $file) {
            if (strpos($file,'_'.strval($userid).'.csv') !== false) {
                Storage::delete($file);
            }
        }
    }
}