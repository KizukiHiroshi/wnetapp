<?php

namespace app\Consts;

class DatabaseConst {

    // ソート条件がない場合に利用するソート順
    const GENERAL_SORT = '{"code" : "asc", "name" : "asc", "name_kana" : "asc", "date" : "asc"}';
    // 日付の最大値
    const MAX_DATE = '2049/12/31';
    // 日付の最小値
    const MIN_DATE = '2000/01/01';
}