<?php

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\AccountController;
use App\Http\Controllers\DeviceController;
use App\Http\Controllers\MailController;
use App\Http\Controllers\MenuController;
use App\Http\Controllers\TableController;
/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/


Route::get('/', function () {
    return view('auth.login');
});

Auth::routes();

Route::middleware('auth')->group(function () {

    Route::get('/device', [DeviceController::class, 'index']);
    Route::post('/device/setname', [DeviceController::class, 'setname']);
    Route::post('/device/regist', [DeviceController::class, 'regist']);
    Route::get('/device/delete', [DeviceController::class, 'delete']);

    Route::get('/account',[AccountController::class, 'index']);

    Route::get('/menu',[MenuController::class, 'index']);

    Route::get('/mail/send', [MailController::class, 'send']);

    Route::post('/table/csvupload/{csvmode}',[TableController::class, 'csvupload']);    // アップロード処理
    Route::post('/table/{tablename}/{id}/update',[TableController::class, 'update']);   // 更新
    Route::post('/table/{tablename}/store',[TableController::class, 'store']);          // 追加
    Route::get('/table/csvupload/{csvmode}',[TableController::class, 'csvupload']);     // アップロード画面
    Route::get('/table/{tablename}/create',[TableController::class, 'create']);         // 新規作成
    Route::get('/table/{tablename}/download',[TableController::class, 'download']);     // ダウンロード
    Route::get('/table/{tablename}/{id}/show',[TableController::class, 'show']);        // 一件表示
    Route::get('/table/{tablename}/{id}',[TableController::class, 'show']);             // 一件表示
    Route::get('/table/{tablename}/{id}/edit',[TableController::class, 'edit']);        // 編集
    Route::get('/table/{tablename}/{id}/delete',[TableController::class, 'delete']);    // 削除
    Route::get('/table/{tablename}/{id}/forcedelete',[TableController::class, 'forcedelete']);    // 完全削除
    Route::get('/table/{tablename}/{id}/restore',[TableController::class, 'restore']);  // 復活
    Route::get('/table/{tablename?}',[TableController::class, 'index']);                // 一覧表示
    Route::post('/table/{tablename?}',[TableController::class, 'index']);               // 一覧表示


});
