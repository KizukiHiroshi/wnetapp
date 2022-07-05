<?php
declare(strict_types=1);
namespace App\Usecases\Device;

use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\Auth;
use App\Services\Database\FindValueService;
use App\Services\Database\Get_ByNameService;
use App\Services\Database\Add_ByNameToFormService;
use App\Services\Database\ExcuteSaveService;

class RegistCase {

    public function __construct() {
    }

    // 登録済のデバイス名と重複しないかチェックする
    public function isRegistedName($request) {
        $name = $request->name;
        // $findvalueset = 参照テーブル名?参照カラム名=urlencode(値)&参照カラム名=urlencode(値)
        $findvalueset = 'devices?name='.urlencode($name);
        $findvalueservice = new FindValueService;
        $devicenameid = $findvalueservice->findValue($findvalueset);
        if ($devicenameid !== 0) {
            return true;
        } else {
            return false;
        }
    }

    // devicesテーブルへ登録する
    public function registDevice($request) {
        $form = $this->getDeviceForm($request);
        $createdid = $this->excuteSave($form);
        return $createdid;
    }

    // 新規のデバイスをwNetに登録するためのformを用意する
    private function getDeviceForm($request) {
        $user_id = Auth::id();
        $name = $request->name;
        $key = hash('md5', date("YmdHis"), false);
        $paginatecnt = intval($this->getScreenHeight()/60);
        $accesstime = date("Y-m-d H:i:s");
        $accessip = $_SERVER["REMOTE_ADDR"];
        $validityperiod = date("Y-m-d H:i:s",strtotime("+1 year"));
        $form = [
            'user_id' => $user_id,
            'name' => $name,
            'key' => $key,
            'paginatecnt' => $paginatecnt,
            'accesstime' => $accesstime,
            'accessip' => $accessip,
            'validityperiod' => $validityperiod,
        ];
        $get_bynameservice = new Get_ByNameService;
        $byname = $get_bynameservice->Get_ByName();
        $columnnames = ['created_by', 'updated_by'];
        $mode = 'store';
        $add_bynametoformservice = new Add_ByNameToFormService;
        $form = $add_bynametoformservice->Add_ByNameToForm($byname, $form, $mode, $columnnames);
        return $form;
    }

    private function excuteSave($form) {
        $tablename = 'devices';
        $id = 0;
        $excutesaveservice = new ExcuteSaveService;
        $createdid = $excutesaveservice->excuteSave($tablename, $form, $id);
        return $createdid;
    }

    // 管理者へメール送信
    public function sendDeviceRequestMail($request) {
        $user_id = Auth::id();
        $username = Auth::user();
        $name = $request->name;
    }

    // デバイスへのCookie登録
    public function setDeviceCookie($form) {
        $name = $form['name'];
        $key = $form['key'];
        // cookieのセットと1年の使用期限設定
        $this->queueDeviceCookie($name, $key);
    }

    // cookieのセットと1年の使用期限設定
    private function queueDeviceCookie($name, $key) {
        Cookie::queue('devicename', $name, 60*24*365);
        Cookie::queue('devicekey', $key, 60*24*365);
    }

    // screenの高さを取得する
    private function getScreenHeight() {
        // ★未実装　get_screenheight.php
        $screen_height = 1080;
        return $screen_height;
    }
}
