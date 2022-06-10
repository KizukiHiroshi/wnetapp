<?php

// ServiceではIlluminate\Http\Requestにアクセスしない
// Modelから取得したデータを整理する

declare(strict_types=1);
namespace App\Services\Table;

use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use App\Services\CommonService;

class ModelService {

    private $commonservice;
    public function __construct(CommonService $commonservice) {
        $this->commonservice = $commonservice;
    }

    /* modelindex:全てのモデルの一覧
    tablename => [         // テーブルの物理名
        'modelname' => '',            // モデル名        
        'modelzone' => '',            // モデルの分類名
        'tablecomment' => '',         // テーブルの和名
    ]
        'referencedcolumns' => '',    // 被参照カラム    
        'defaultsort' => '',          // ソート順
        'validationrule' => ''        // バリデーションルール
        は、
        $modelname::$referencedcolumns,    
        $modelname::$defaultsort,    
        $modelname::$validationrule,
        で、取得すること  
    */
    /* 表示カラムの一覧:$columnsprop
    ['columnname' =>   // 表示カラム名 ★参照カラムの場合はforeign_id_?????(referensdcolum)
        [
            'tablename' => '',  // 実テーブル名:参照カラムで参照テーブル名
            'type'      => '',  // 変数タイプ
            'length'    => '',  // 変数の長さ
            'comment'   => '',  // カラムの和名
            'notnull'   => '',  // NULL許可   
            'default'   => '',  // 初期値
            'realcolumn'=> '',  // 実カラム名:参照カラムで参照テーブル状のカラム名
            'sortcolumn'=> '',  // ソート時に使う実カラム名：'name'に対する'name_kana'
        ],
    ]
    */
    
    // columnspropのforeignをcard表示用に変更する
    public function arangeColumnspropToCard($columnsprop) {
        $cardcolumnsprop = [];
        $foreigncolumn = '';
        // $referencecolumns = $this->getReferenceColumns($columnsprop);
        foreach ($columnsprop AS $columnname => $prop) {
            if (strpos($columnname, '_id_') == false || substr($columnname, -3) =='_id' || substr($columnname, -7) =='_id_2nd') {
                $cardcolumnsprop[$columnname] = $prop;
                if (substr($columnname, -3) =='_id' || substr($columnname, -7) =='_id_2nd') {
                    $foreigncolumn = $columnname;
                    // foreign_idの後にreferenceを入れる
                    $prop['type'] = 'string';
                    $cardcolumnsprop[$foreigncolumn.'_reference'] = $prop;
                }
            } elseif (strpos($columnname, $foreigncolumn) !== false && strpos($columnname, '_id_') !== false) {
                if (intval($prop['length']) > 0) {
                    $length = intval($cardcolumnsprop[$foreigncolumn.'_reference']['length']);
                    $length +=intval($prop['length']);
                    $cardcolumnsprop[$foreigncolumn.'_reference']['length'] = strval($length);
                }
            }
        }
        return $cardcolumnsprop;
    }
    
    // 表示する行の空データを作る
    public function getEmptyRow($columnsprop) {
        $rawrow =[];
        foreach ($columnsprop AS $columnname => $value) {
            $rawrow[$columnname] = 'old('.$columnname.')';
        }
        $row = (object) $rawrow;
        return $row;
    }

    // uploadされたリストをiddictionary参照利用して登録可能な配列に替える
    public function arangeForm($tablename, $rawform, $foreginkeys, $iddictionary) {
        $form = [];
        // ★ Columnspropと比較して、数値にはnullを
        $columnnames = Schema::getColumnListing($tablename);
        foreach ($rawform as $key => $value) {
            if (in_array($key, $columnnames) && substr($key,-3) !== '_at') {
                $form[$key] = $value == '' ? null : $value;
            }
        }
        foreach ($foreginkeys as $foreginkey) {
            $foregintablename = Str::singular(Str::before($foreginkey,'?')).'_id';
            if (in_array($foregintablename, $columnnames)) {
                $form[$foregintablename] = $iddictionary[$foreginkey];
            }
        }
        return $form;
    }

    // requestをtableに登録可能な配列に替える
    public function getForm($request, $mode) {
        $form = [];
        $tablename = $request->tablename;
        $rawform = $request->all();
        $columnnames = Schema::getColumnListing($tablename);
        foreach ($rawform as $key => $value) {
            if ($mode == 'store' && $key == 'id') {
                // store時のidは除外
            } elseif (in_array($key, $columnnames) && substr($key,-3) !== '_at') {
                $form[$key] = $value;
            }
        }
        $form = $this->commonservice->addBytoForm($columnnames, $form, $mode);
        return $form;
    }

    // searchで入力されたbigin_,end_ヘッダー付検索条件を、
    // validationの対象にするために元のカラム名に戻す
    public function getFormforSearch($withhearder, $columnsprop, $searchinput) {
        $form = [];
        foreach ($columnsprop as $columnname => $value) {
            if (array_key_exists($withhearder.$columnname, $searchinput)) {
                $form[$columnname] = $searchinput[$withhearder.$columnname];
            }
        }
        return $form;
    }
}
