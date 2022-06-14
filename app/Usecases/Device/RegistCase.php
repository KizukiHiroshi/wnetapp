<?php
declare(strict_types=1);
namespace App\Usecases\Device;

use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\Auth;
use App\Services\Database\FindValueService;
use App\Services\Database\Get_ByNameService;
use App\Services\Database\Add_ByNameToFormService;
use App\Services\Database\ExcuteSaveService;
use App\Services\SessionService;

class RegistCase {

    private $get_bynameservice;
    private $add_bynametoformservice;
    private $excutesaveservice;
    private $sessionservice;
    public function __construct(
        Get_ByNameService $get_bynameservice,
        Add_ByNameToFormService $add_bynametoformservice,
        ExcuteSaveService $excutesaveservice,
        SessionService $sessionservice) {
            $this->get_bynameservice = $get_bynameservice;
            $this->add_bynametoformservice = $add_bynametoformservice;
            $this->excutesaveservice = $excutesaveservice;
            $this->sessionservice = $sessionservice;
    }

    // 登録済のデバイス名と重複しないかチェックする
    public function isRegistedName($request) {
        $name = $request->name;
        // $findvalueset =  参照テーブル名?参照カラム名=値&参照カラム名=値
        $findvalueset = 'devices?name='.$name;
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
        $byname = $this->get_bynameservice->Get_ByName();
        $columnnames = ['created_by', 'updated_by'];
        $mode = 'store';
        $form = $this->add_bynametoformservice->add_ByNameToForm($byname, $form, $columnnames, $mode);
        return $form;
    }

    private function excuteSave($form){
        $tablename = 'devices';
        $id = 0;
        $createdid = $this->excutesaveservice->excuteSave($tablename, $form, $id);
        return $createdid;
    }

    // 管理者へメール送信
    public function sendDeviceRequestMail($request){
        // ★未実装
    }

    // デバイスへのCookie登録
    public function setDeviceCookie($form){
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
