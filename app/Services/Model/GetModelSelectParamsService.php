<?php

declare(strict_types=1);
namespace App\Services\Model;

class GetModelSelectParamsService {

    public function __construct() {
    }

    /* getModelselect:モデル選択用のmodelzone,tablecommentのグループセレクト配列
    tablename => [         // テーブルの物理名
        'group'     => modelzone        // テーブルの属するゾーン
        'value'     => tablecomment,    // テーブル和名
    ] 
    */
    public function getModelselectParams($request, $modelindex) {
        $tablename = $request->tablename;
        // モデル選択に渡す現在のテーブル名
        $selectedtable = $tablename;
        $modelselect = []; // 返すグループセレクト配列名
        foreach($modelindex as $key => $model) {
            $modelselect[$key] = [
                'group' => $model['modelzone'],
                'value' => $model['tablecomment'],
            ];
        }
        $params = [
            'selectedtable' => $selectedtable,
            'modelselect'   => $modelselect,
        ];
        return $params;
    }
}
