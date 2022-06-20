<?php
declare(strict_types=1);
namespace App\Services\File;

use Illuminate\Support\Facades\Storage;

class KillOldFileService
{
    public function __construct(){
    }
    // wnetapp\storage\app\public\csv内の10分以上前にuploadされたファイルを削除する
    public function killOldFile(){
        $setminuts = 10;
        $files = Storage::allFiles('public/csv/');;
        foreach ($files as $file){
            $updatedtime = Storage::lastModified($file);;
            if ((time()-$updatedtime)/60 > $setminuts && substr($file, -4) == '.csv'){
                Storage::delete($file);
            }
        }
    }
}