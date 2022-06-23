<?php
declare(strict_types=1);
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Usecases\Table\TableCase;
use App\Usecases\Table\ListCase;
use App\Usecases\Table\CardCase;
use App\Usecases\Table\ExcuteCase;
use Symfony\Component\HttpFoundation\StreamedResponse;

class TableController extends Controller
{

    private $tablecase;
    private $listcase;
    private $cardcase;
    private $excutecase;
    public function __construct(
        TableCase $tablecase,
        ListCase $listcase,
        CardCase $cardcase,
        ExcuteCase $excutecase) {
            $this->tablecase = $tablecase;
            $this->listcase = $listcase;
            $this->cardcase = $cardcase;
            $this->excutecase = $excutecase;
    }

    // (GET) http://wnet2020.com/table/{tablename}　・・・　一覧表示。index()    
    public function index(Request $request) {
        // request内容の変更に応じて既存のセッションを消す
        $this->tablecase->sessionOptimize($request);
        // 表示用のパラメータを取得する       
        $params = $this->listcase->getParams($request);
        // ■■■■
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
        $this->tablecase->sessionOptimize($request);
        // 表示用のパラメータを取得する
        $params = $this->cardcase->getParams($request, $mode);
        return view('common/table')->with($params);
    }

    // (POST) http://wnet2020.com/table/{tablename}　・・・　追加。store()
    public function store(Request $request) {
        $sqlmode = 'store';
        $storedid = $this->excutecase->Excute($request, $sqlmode);
        if ($storedid) {
            $tablename = $request->tablename;
            // 完了メッセージ
            $success = '登録しました';
            // 登録された行の表示
            return redirect('/table/'.$tablename.'/'.$storedid.'/show/?success='.$success);
        } else {
            // 失敗メッセージ
            $danger = '登録に失敗しました。';
            return view('common/alert', compact('danger'));
        }
    }

    // (PUT) http://wnet2020.com/table/{tablename}/{id}　・・・　更新。update()
    public function update(Request $request) {
        $sqlmode = 'update';
        $updatedid = $this->excutecase->Excute($request, $sqlmode);
        if ($updatedid) {
            // 完了メッセージ
            $success = '更新しました';
            // 更新された行の表示
            $tablename = $request->tablename;
            return redirect('/table/'.$tablename.'/'.$updatedid.'/show?success='.$success);
        } else {
            // 失敗メッセージ
            $danger = '更新に失敗しました。';
            return view('common/alert', compact('danger'));
        }
    }

    // (DELETE) http://wnet2020.com/table/{tablename}/{id}　・・・　削除。destroy()
    public function delete(Request $request) {
        // 削除更新(softDelete)実行
        if ($this->tablecase->is_Deleted($request)) {
            // 完了メッセージ
            $success = '削除しました';
            // 元のページ表示
            $tablename = $request->tablename;
            $page = $request->page !== '' ? $request->page : '';
            return redirect('/table/'.$tablename.'?page='.$page.'&success='.$success);
        } else {
            // 失敗メッセージ
            $danger = '削除に失敗しました。';
            return view('common/alert', compact('danger'));
        }
    }

    // softDeleteされた行を完全削除する
    public function forcedelete(Request $request) {
        // 完全削除実行
        if ($this->tablecase->is_forceDeleted($request)) {
            // 完了メッセージ
            $success = '完全削除しました';
            // 元のページ表示
            $tablename = $request->tablename;
            $page = $request->page !== '' ? $request->page : '';
            return redirect('/table/'.$tablename.'?page='.$page.'&success='.$success);
        } else {
            // 失敗メッセージ
            $danger = '完全に失敗しました。';
            return view('common/alert', compact('danger'));
        }
    }

    // softDeleteされた行を復活する=deleted_atをNULLにする
    public function restore(Request $request) {
        // 復活実行
        if ($this->tablecase->is_Restored($request)) {
            // 完了メッセージ
            $success = '復活しました';
            $tablename = $request->tablename;
            $id = $request->id;
            // 復活した行の表示
            return redirect('/table/'.$tablename.'/'.$id.'/show'.'?success='.$success);
        } else {
            // 失敗メッセージ
            $danger = '復活に失敗しました。';
            return view('common/alert', compact('danger'));
        }
    }

    // 表示中のListを指定Excelシートに適合したCSVファイルでダウンロードする
    /**
     * Export List with csv
     * @return Symfony\Component\HttpFoundation\StreamedResponse
     */
    public function download(Request $request) {
        // 表示Listのダウンロード用CSVを取得する
        $downloadcsv = $this->tablecase->getDownloadCSV($request);
        // CSV出力処理
        $response = new StreamedResponse (function() use ($downloadcsv) {
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

    // 指定Excelシートから出力したしたCSVファイルをアップロードする ★★未整理
    public function csvupload(Request $request) {
        // Upload画面から処理段階($csv)を得る
        $csvmode = $request->csvmode;
        if ($csvmode == NULL) { $csvmode = 'csvselect'; }
        if ($csvmode == 'csvcancel') {
            // strage/app/public/csv内の自分のファイル削除
            $this->csvuploadcase->killMyfile();
            $tablename = $request->tablename;
            // ■■■■リスト表示
            return redirect('/table/'.$tablename);
        }
        // Table選択、検索表示のパラメータを取得する
        $params = $this->csvuploadcase->getParams($request, $csvmode);
        // ■■■■CSVアップロード画面表示
        return view('common/table')->with($params);
    }
}
