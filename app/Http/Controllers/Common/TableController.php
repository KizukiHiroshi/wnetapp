<?php

// ControllerではIlluminate\Support\Facades\DB,Schema にアクセスしない
declare(strict_types=1);
namespace App\Http\Controllers\Common;

use App\Http\Controllers\Controller;
use App\Service\Common\TableService;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class TableController extends Controller
{

    private $tableservice;
    public function __construct(TableService $tableservice) {
            $this->tableservice = $tableservice;
    }

    // (GET) http://wnet2020.com/table/{tablename}　・・・　一覧表示。index()    
    public function index(Request $request) {
        // request内容の変更に応じて既存のセッションを消す
        $this->tableservice->sessionOptimaize($request);
        // Table選択、検索表示のパラメータを取得する
        $params = $this->tableservice->getMenuParams($request);
        // List表示用のパラメータを取得する
        $params = array_merge($params, $this->tableservice->getListParams($request, $params));
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
        // request内容の変更に応じて既存のセッションを消す
        $this->tableservice->sessionOptimaize($request);
        // Table選択、検索表示のパラメータを取得する
        $params = $this->tableservice->getMenuParams($request);
        // Card表示用のパラメータを取得する
        $params += $this->tableservice->getCardParams($request, $mode);
        return view('common/table')->with($params);
    }

    // (POST) http://wnet2020.com/table/{tablename}　・・・　追加。store()
    public function store(Request $request) {
        // $requestから新規登録に必要な値の配列を得る
        $sqlmode = 'store';
        $form = $this->tableservice->getForm($request, $sqlmode);
        // 登録実行
        $tablename = $request->tablename;
        $id = null;
        // 汎用の登録・更新プロセス 
        $createdid = $this->tableservice->excuteProcess($tablename, $form, $id);
        if ($createdid) {
            // 完了メッセージ
            $success = '登録しました';
            // 登録された行の表示
            return redirect('/table/'.$tablename.'/'.$createdid.'/show/?success='.$success);
        }
    }

    // (PUT) http://wnet2020.com/table/{tablename}/{id}　・・・　更新。update()
    public function update(Request $request) {
        // $requestから更新に必要な値の配列を得る
        $form = $request->all();
        $sqlmode = 'update';
        $form = $this->tableservice->getForm($request, $sqlmode);
        // 更新実行
        $tablename = $request->tablename;
        $id = $request->id;
        // 汎用の登録・更新プロセス 
        $id = $this->tableservice->excuteProcess($tablename, $form, $id);
        if ($id) {
            // 完了メッセージ
            $success = '更新しました';
            // 更新された行の表示
            return redirect('/table/'.$tablename.'/'.$id.'/show?success='.$success);
        }
    }

    // (DELETE) http://wnet2020.com/table/{tablename}/{id}　・・・　削除。destroy()
    public function delete(Request $request) {
        $tablename = $request->tablename;
        $id = $request->id;
        // 削除更新(softDelete)実行
        if ($this->tableservice->is_Deleted($tablename, $id)) {
            // 完了メッセージ
            $success = '削除しました';
            // 元のページ表示
            $page = $request->page !== '' ? $request->page : '';
            return redirect('/table/'.$tablename.'?page='.$page.'&success='.$success);
        } else {

        }
    }

    // softDeleteされた行を完全削除する
    public function forcedelete(Request $request) {
        $tablename = $request->tablename;
        $id = $request->id;
        // 完全削除実行
        if ($this->tableservice->is_forceDeleted($tablename, $id)) {
            // 完了メッセージ
            $success = '完全削除しました';
            // 元のページ表示
            $page = $request->page !== '' ? $request->page : '';
            return redirect('/table/'.$tablename.'?page='.$page.'&success='.$success);
        } else {

        }
    }

    // softDeleteされた行を復活する=deleted_atをNULLにする
    public function restore(Request $request) {
        $tablename = $request->tablename;
        $id = $request->id;
        // 復活実行
        if ($this->tableservice->is_Restored($tablename, $id)) {
            // 完了メッセージ
            $success = '復活しました';
            // 復活した行の表示
            return redirect('/table/'.$tablename.'/'.$id.'/show'.'?success='.$success);
        } else {

        }
    }

    // 表示中のListを指定Excelシートに適合したCSVファイルでダウンロードする
    /**
     * Export List with csv
     * @return Symfony\Component\HttpFoundation\StreamedResponse
     */
    public function download(Request $request) {
        // 表示Listのダウンロード用CSVを取得する
        $downloadcsv = $this->tableservice->getDownloadCSV($request);
        // CSV出力処理
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

    // 指定Excelシートから出力したしたCSVファイルをアップロードする
    public function csvupload(Request $request) {
        // Upload画面から処理段階($csv)を得る
        $csvmode = $request->csvmode;
        if ($csvmode !== 'csvcancel') {
            // $csvmodeに合わせて処理する
            $uploadresult = $this->tableservice->csvUpload($request, $csvmode);
            // Table選択、検索表示のパラメータを取得する
            $params = $this->tableservice->getMenuParams($request);
            // Upload画面表示に必要なパラメータを準備する
            $params += $this->tableservice->getUploadParams($request, $uploadresult);
            return view('common/table')->with($params);    
        } elseif ($csvmode == 'csvcancel') {
            // strage/app/public/csv内の自分のファイル削除
            $this->tableservice->killMyfile();
            $tablename = $request->tablename;
            // リスト表示
            return redirect('/table/'.$tablename);
        }
    }
}
