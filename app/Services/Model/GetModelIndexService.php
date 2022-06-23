<?php

declare(strict_types=1);
namespace App\Services\Model;

use Illuminate\Support\Str;

class GetModelIndexService {

    public function __construct() {
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
    public function getModelIndex() {
        $modelindex = []; // 返すモデルリスト
        // 拡張子を着けてディレクトリ名でヒットしないようにする
        // Models直下のモデル検索する
        $modeldirs = glob(base_path('app/Models').'/*');
        foreach($modeldirs as $modeldir) {
            if (strpos($modeldir, '.php')) {
                $modelindex = $this->addModels($modelindex, $modeldir);
            } else {
                $indirmodelindex = glob($modeldir . '/*');
                foreach($indirmodelindex as $indirmodel) {
                    if (strpos($indirmodel, '.php')) {
                        $modelindex = $this->addModels($modelindex, $indirmodel);
                    }
                }
            }
        }
        $modelzone  = array_column($modelindex, 'modelzone');
        $tablecomment  = array_column($modelindex, 'tablecomment');
        array_multisort($modelzone, SORT_ASC, $tablecomment, SORT_ASC, $modelindex);               
        return $modelindex;
    }

    private function addModels($modelindex, $modelname) {
        // Modelnameに余分な文字を削除する
        $modelname = $this->organizeModelname($modelname);
        // table名に余分な文字を削除する
        $tablename = $this->ModelnameToTablename($modelname);
        $modelindex[$tablename] = [
            'modelname' => $modelname,
            'modelzone' => $modelname::$modelzone,
            'tablecomment' => $modelname::$tablecomment,
        ];
        return $modelindex;
    }
    private function organizeModelname($modelname) {
        // Modelnameに余分な文字を削除する
        $modelname = substr($modelname,strpos($modelname,'Models'));
        $modelname = 'App/'.$modelname;
        $modelname = str_replace('.php', '', $modelname);
        $modelname = str_replace('/', '\\', $modelname);
        return $modelname;
    }    
    private function ModelnameToTablename($modelname) {
        // table名に余分な文字を削除する
        $tablename = Str::afterLast($modelname, '\\');
        $tablename = Str::plural(Str::lower($tablename));
        return $tablename;
    }
}
