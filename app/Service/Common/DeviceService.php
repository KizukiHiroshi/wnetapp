<?php

// ServiceではIlluminate\Http\Requestにアクセスしない
// デバイスを管理する

declare(strict_types=1);

namespace App\Service\Common;

use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\Auth;
use App\Service\Utility\DbioService;
use App\Service\Utility\SessionService;
use App\Service\Utility\ModelService;

class DeviceService {

    private $dbioservice;
    public function __construct(
        DbioService $dbioservice,
        ModelService $modelService,
        SessionService $sessionservice) {
        $this->dbioservice = $dbioservice;
        $this->sessionservice = $sessionservice;
        $this->modelService = $modelService;       
    }

    // 登録済のデバイス名と重複しないかチェックする
    public function checkIsRegistedName($name) {
        // $findidset =  参照テーブル名?参照カラム名=値&参照カラム名=値
        $findidset = 'devices?name='.$name;
        $devicenameid = $this->dbioservice->findId($findidset);
        if ($devicenameid) {
            return true;
        } else {
            return false;
        }
    }

    // 新規のデバイスをwNetに登録するためのformを用意する
    public function getDeviceForm($request) {
        $user_id = Auth::id();
        $name = $request->name;
        $devicekey = hash('md5', date("YmdHis"), false);
        $paginatecnt = intval($this->getScreenHeight()/30);
        $accesstime = date("Y-m-d H:i:s");
        $accessip = $_SERVER["REMOTE_ADDR"];
        $validityperiod = date("Y-m-d H:i:s",strtotime("+1 year"));
        $form = [
            'user_id' => $user_id,
            'name' => $name,
            'devicekey' => $devicekey,
            'paginatecnt' => $paginatecnt,
            'accesstime' => $accesstime,
            'accessip' => $accessip,
            'validityperiod' => $validityperiod,
        ];
        $columnnames = ['created_by', 'updated_by'];
        $mode = 'store';
        $form = $this->commonservice->addBytoForm($columnnames, $form, $mode);
        return $form;
    }

    public function excuteProcess($tablename, $form, $id){
        $this->dbioservice->excuteProcess($tablename, $form, $id);
    }

    public function isAvailableId($deviceid) {
        $tablename = 'devices';
        $is_availableid = $this->dbioservice->isAvailableId($tablename, $deviceid);
        return $is_availableid;
    }

    // 登録済のデバイスかどうかの確認
    public function isRegistedDevice() {
        // cookieの存在確認
        // devicesと照合
        return true;
    }

    // デバイスの登録
    public function registDevice() {
        // devicesへの登録
    }

    // デバイス登録の削除
    public function unregistDevice() {
        
    }

    // デバイス名が設定されているか確認する
    public function comfirmDeviceName() {
        // デバイス名の確認と使用期限の延長
        $name = Cookie::get('name');
        if ($name) {
            $devicekey = Cookie::get('devicekey');
            // cookieのセットと1年の使用期限設定
            $this->queueDeviceCookie($name, $devicekey);
        }
        return $name;
    }

    // デバイスへのCookie登録
    public function setDeviceCookie($form){
        $name = $form['name'];
        $devicekey = $form['devicekey'];
        // cookieのセットと1年の使用期限設定
        $this->queueDeviceCookie($name, $devicekey);
    }

    // cookieのセットと1年の使用期限設定
    private function queueDeviceCookie($name, $devicekey) {
        Cookie::queue('name', $name, 60*24*365);
        Cookie::queue('devicekey', $devicekey, 60*24*365);
    }

    // cookieの削除
    public function deleteDeviceCookie() {
        $name = $this->comfirmDeviceName();
        if ($name) {
            $devicekey = $this->getDevicekeyCookie();
            // cookieの削除
            Cookie::queue(Cookie::forget('name'));
            Cookie::queue(Cookie::forget('devicekey'));
            // devicesの登録削除
            $this->deleteRegistedDevice($name, $devicekey);
        }
    }

    // devicekeyCookie取得
    public function getDevicekeyCookie() {
        return Cookie::get('devicekey');
    }

    // 登録済のデバイスを削除する
    private function deleteRegistedDevice($name, $devicekey) {
        // devicesテーブルへの登録確認
        $deviceid = $this->getRegistedDevice($name, $devicekey);
        if ($deviceid) {
            $tablename = 'devices';
            $this->dbioservice->is_forceDeleted($tablename, $deviceid);
        }
    }
    
    // 登録済のデバイスかチェックする
    public function getRegistedDevice($name, $devicekey) {
        $tablename = 'devices';
        // $findidset =  参照テーブル名?参照カラム名=値&参照カラム名=値
        $findidset = $tablename.'?name='.$name.'&devicekey='.$devicekey;
        $deviceid = $this->dbioservice->findId($findidset);
        return $deviceid;
    }
    
    // screenの高さを取得する
    private function getScreenHeight() {
        require(base_path('app/').'get_screenheight.php');
        $screen_height = $this->sessionservice->getSession('screen_height');
        return $screen_height;
    }

    // 管理者へメール送信
    public function sendDeviceRequestMail($name){
        
    }

}
