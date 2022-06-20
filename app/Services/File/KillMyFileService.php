<?php
declare(strict_types=1);
namespace App\Services\File;

use Illuminate\Support\Facades\Storage;
use App\Services\SessionService;

class KillMyFileService
{
    public function __construct(){
    }

    // wnetapp\storage\app\public\csv内の$useridがuploadしたファイルを削除する
    public function killMyFile(){
        $sessionservise = new SessionService;
        $accountvalue = $sessionservise->getSession('accountvalue');
        if (!$accountvalue){
            return;
        }
        if (!array_key_exists('memberid', $accountvalue)){
            return;
        }
        $memberid = $accountvalue['memberid'];
        $files = Storage::allFiles('public/csv/');;
        foreach ($files as $file){
            if (strpos($file,'_'.strval($memberid).'.csv') !== false){
                Storage::delete($file);
            }
        }
    }
}