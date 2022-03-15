<?php

// ControllerではIlluminate\Support\Facades\DB,Schema にアクセスしない
declare(strict_types=1);
namespace App\Http\Controllers\Common;

use App\Http\Controllers\Controller;
use App\Service\Common\DbioService;
use App\Service\Common\ModelService;
use App\Service\Common\TableService;
use Illuminate\Http\Request;
use App\Http\Requests\Common\TableRequest;
use Symfony\Component\HttpFoundation\StreamedResponse;

class TableController extends Controller
{
    private $dbioservice;
    private $modelservice;
    private $tableservice;
    public function __construct(
        DbioService $dbioservice,
        ModelService $modelservice,
        TableService $tableservice) {
        $this->dbioservice = $dbioservice;
        $this->modelservice = $modelservice;
        $this->tableservice = $tableservice;
    }

    // (GET) http://wnet2020.com/table/{tablename}　・・・　一覧表示。index()    
    public function index(Request $request) {
        // List表示用のパラメータを取得する
        $params = $this->tableservice->getListParams($request);
        return view('common/table')->with($params);
    }

    // (GET) http://wnet2020.com/table/{tablename}/create　・・・　新規更新。create()
    public function create(Request $request) {
        $mode = 'create';
        return $this->displayCard($mode, $request);
    }

    // (GET) http://wnet2020.com/table/{tablename}/1/show　・・・　該当行表示。show()
    public function show(Request $request) {
        $mode = 'show';
        return $this->displayCard($mode, $request);
   }

    // (GET) http://wnet2020.com/table/{tablename}/1/edit　・・・　編集。edit()
    public function edit(Request $request) {
        $mode = 'edit';
        return $this->displayCard($mode, $request);
    }

    // カードを表示する
    public function displayCard($mode, $request) {
        $params = $this->tableservice->getCardParams($request, $mode);
        return view('common/table')->with($params);
    }

    // (POST) http://wnet2020.com/table/{tablename}　・・・　追加。store()
    /**
     * 新しいブログ投稿を保存
     *
     * @param  \App\Http\Requests\Common\TableRequest  $request
     * @return Illuminate\Http\Response
    */
    public function store(TableRequest $request) {
        // $requestをtableに合わせた配列にする
        $form = $request->validated();
        $form = $this->modelservice->getForm($request);
        $tablename = $request->tablename;
        // 登録実行
        $createdid = $this->dbioservice->createdId($tablename, $form);
        if ($createdid) {
            // 完了メッセージ
            $success = '登録しました';
            return redirect('/table/'.$tablename.'/'.$createdid.'?success='.$success);
        } else {

        }
    }

    // (PUT) http://wnet2020.com/table/{tablename}/{id}　・・・　更新。update()
    public function update(Request $request) {
        // $requestのValidation
        $tablename = $request->tablename;
        $form = $this->modelservice->getForm($request);
        $id = $request->id;
        // 更新実行
        if ($this->dbioservice->is_Updated($tablename, $form, $id)) {
            // 完了メッセージ
            $success = '更新しました';
            return redirect('/table/'.$tablename.'/'.$id.'/show'.'?success='.$success);
        } else {

        }
    }

    // (DELETE) http://wnet2020.com/table/{tablename}/{id}　・・・　削除。destroy()
    public function delete(Request $request) {
        $tablename = $request->tablename;
        $id = $request->id;
        // 更新実行
        if ($this->dbioservice->is_Deleted($tablename, $id)) {
            // 現在のページ
            $page = $request->page != '' ? $request->page : '';
            // 完了メッセージ
            $success = '削除しました';
            return redirect('/table/'.$tablename.'?page='.$page.'&success='.$success);
        } else {

        }
    }

    // softDeleteされた行を完全削除する
    public function forcedelete(Request $request) {
        $tablename = $request->tablename;
        $id = $request->id;
        // 更新実行
        if ($this->dbioservice->is_forceDeleted($tablename, $id)) {
            // 現在のページ
            $page = $request->page != '' ? $request->page : '';
            // 完了メッセージ
            $success = '完全削除しました';
            return redirect('/table/'.$tablename.'?page='.$page.'&success='.$success);
        } else {

        }
    }

    // softDeleteされた行を復活する=deleted_atをNULLにする
    public function restore(Request $request) {
        $tablename = $request->tablename;
        $id = $request->id;
        // 更新実行
        if ($this->dbioservice->is_Restored($tablename, $id)) {
            // 完了メッセージ
            $success = '復活しました';
            return redirect('/table/'.$tablename.'/'.$id.'/show'.'?success='.$success);
        } else {

        }
    }

    // 表示Listをダウンロードする
    /**
     * Export List with csv
     * @return Symfony\Component\HttpFoundation\StreamedResponse
     */
    public function download(Request $request) {
        $downloadcsv = $this->tableservice->getDownloadCSV($request);
        $response = new StreamedResponse (function() use ($downloadcsv){
            $stream = fopen('php://output', 'w');
            //　文字化け回避
            stream_filter_prepend($stream,'convert.iconv.utf-8/cp932//TRANSLIT');
            foreach ($downloadcsv as $csv) {
                fputcsv($stream, $csv);
            }
            fclose($stream);
        });
        $response->headers->set('Content-Type', 'application/octet-stream');
        $filename = $downloadcsv[0][0].'_download.csv';
        $response->headers->set('Content-Disposition', 'attachment; filename="'.$filename.'"');
        return $response;
    }

    // アップロード画面
    public function upload(Request $request) {
        $params = $this->tableservice->getUploadParams($request);
        return view('common/table')->with($params);
    }

    // アップロード実行
    public function upload_action(Request $request) {
        $params = $this->tableservice->getUploadParams($request);
        return view('common/table')->with($params);
    }

}
