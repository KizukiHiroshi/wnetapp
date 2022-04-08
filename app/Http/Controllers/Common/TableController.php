<?php

// ControllerではIlluminate\Support\Facades\DB,Schema にアクセスしない
declare(strict_types=1);
namespace App\Http\Controllers\Common;

use App\Http\Controllers\Controller;
use App\Service\Common\CommonService;
use App\Service\Common\DbioService;
use App\Service\Common\ModelService;
use App\Service\Common\TableService;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class TableController extends Controller
{
    private $commonservice;
    private $dbioservice;
    private $modelservice;
    private $tableservice;
    public function __construct(
        CommonService $commonservice,
        DbioService $dbioservice,
        ModelService $modelservice,
        TableService $tableservice) {
            $this->commonservice = $commonservice;
            $this->dbioservice = $dbioservice;
            $this->modelservice = $modelservice;
            $this->tableservice = $tableservice;
    }

    // (GET) http://wnet2020.com/table/{tablename}　・・・　一覧表示。index()    
    public function index(Request $request) {
        // List表示用のパラメータを取得する
        $params = $this->tableservice->getMenuParams($request);
        $params += $this->tableservice->getListParams($request);
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
        $params = $this->tableservice->getMenuParams($request);
        $params += $this->tableservice->getCardParams($request, $mode);
        return view('common/table')->with($params);
    }

    // (POST) http://wnet2020.com/table/{tablename}　・・・　追加。store()
    /**
     *
     * @param  \App\Http\Requests\Common\TableRequest  $request
     * @return Illuminate\Http\Response
    */
    public function store(Request $request) {
        $form = $request->all();
        // $requestをtableに合わせた配列にする
        $sqlmode = 'store';
        $form = $this->modelservice->getForm($request, $sqlmode);
        // 登録実行
        $tablename = $request->tablename;
        $id = null;
        $mode = 'save';
        $createdid = $this->dbioservice->excuteProcess($tablename, $form, $id, $mode);
        if ($createdid) {
            $success = '登録しました';
            return redirect('/table/'.$tablename.'/'.$createdid.'/show/?success='.$success);
        }
    }

    // (PUT) http://wnet2020.com/table/{tablename}/{id}　・・・　更新。update()
    public function update(Request $request) {
        $form = $request->all();
        // $requestをtableに合わせた配列にする
        $sqlmode = 'update';
        $form = $this->modelservice->getForm($request, $sqlmode);
        // 更新実行
        $tablename = $request->tablename;
        $id = $request->id;
        $mode = 'save';
        $id = $this->dbioservice->excuteProcess($tablename, $form, $id, $mode);
        if ($id) {
            $success = '更新しました';
            return redirect('/table/'.$tablename.'/'.$id.'/show?success='.$success);
        }
    }

    // (DELETE) http://wnet2020.com/table/{tablename}/{id}　・・・　削除。destroy()
    public function delete(Request $request) {
        $tablename = $request->tablename;
        $id = $request->id;
        // 更新実行
        if ($this->dbioservice->is_Deleted($tablename, $id)) {
            // 現在のページ
            $page = $request->page !== '' ? $request->page : '';
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
            $page = $request->page !== '' ? $request->page : '';
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
    // アップロード
    public function csvupload(Request $request) {
        $csvmode = $request->csvmode;
        if ($csvmode !== 'csvcancel') {
            $uploadresult = $this->tableservice->csvUpload($request, $csvmode);
            $params = $this->tableservice->getMenuParams($request);
            $params += $this->tableservice->getUploadParams($request, $uploadresult);
            return view('common/table')->with($params);    
        } elseif ($csvmode == 'csvcancel') {
            // strage/app/public/csv内の自分のファイル削除
            $this->tableservice->killMyfile();
            $tablename = $request->tablename;
            return redirect('/table/'.$tablename);
        }
    }

    // アップロード確認
    public function csvupload_check(Request $request) {
        $file_name = $request->file('file')->getClientOriginalName();
        $request->file('file')->storeAs('public',$file_name);
        $mode = 'check';
        $uploadresult = $this->tableservice->csvUpload($request, $mode);
        if ($uploadresult['error'] == NULL) {
            // modeを変えてもう一度表示
            $mode = 'csvsave';
            $params = $this->tableservice->getUploadParams($request, $mode);
            return view('common/table')->with($params);
        } else {
            // もう一度表示
            $mode = 'csvcheck';
            $params = $this->tableservice->getUploadParams($request, $mode);
            $params['errormsg'] = $uploadresult['error'];
            return view('common/table')->with($params);
        }

    }
    // アップロード実行
    public function csvupload_action(Request $request) {
        $mode = 'action';
        $uploadresult = $this->tableservice->csvUpload($request, $mode);
        if ($uploadresult['error'] == NULL) {
            // 完了メッセージ
            $success = 'アップロードしました';
            return redirect('/table/'.$uploadresult['tablename'].'?success='.$success);
        } else {

        }
    }

}
