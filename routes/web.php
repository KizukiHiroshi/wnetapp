<?php

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\Common\TableController;
use App\Http\Controllers\Common\MenuController;
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

Route::get('/home', [MenuController::class, 'index']);

Route::middleware('auth')->group(function () {
    Route::get('/menu',[MenuController::class, 'index']);

    Route::get('/table/{tablename}/create',[TableController::class, 'create']);         // 新規作成
    Route::get('/table/{tablename}/download_csv',[TableController::class, 'download_csv']);         // ダウンロード
    Route::get('/table/{tablename}/{id}/show',[TableController::class, 'show']);        // 一件表示
    Route::get('/table/{tablename}/{id}',[TableController::class, 'show']);             // 一件表示
    Route::get('/table/{tablename}/{id}/edit',[TableController::class, 'edit']);        // 編集
    Route::get('/table/{tablename}/{id}/delete',[TableController::class, 'delete']);    // 削除
    Route::get('/table/{tablename}/{id}/forcedelete',[TableController::class, 'forcedelete']);    // 完全削除
    Route::get('/table/{tablename}/{id}/restore',[TableController::class, 'restore']);  // 復活
    Route::get('/table/{tablename?}',[TableController::class, 'index']);                // 一覧表示
    Route::post('/table/{tablename}/{id}/update',[TableController::class, 'update']);   // 更新
    Route::post('/table/{tablename}/store',[TableController::class, 'store']);          // 追加

});
