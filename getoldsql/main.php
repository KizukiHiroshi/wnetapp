<?php

require 'getold.php';

// 取得済みSQLの最新日時を確認する
$lastcreated = getLastCreated();
// 旧DBのSQLを取得する
$oldsqls = getOldSql($lastcreated);
// oldsqlに登録する
insertOldsql($oldsqls);



// // タスク開始時の最大IDを取得する
// $maxid = getMaxID();
// // 旧DBトレースのID最大値までの内容を取得する
// $oldtracerows = getOldTraceRows($maxid);
// // SQL文をtrandbに保存する
// saveTrandb($oldtracerows);
// // 旧DBトレースのID最大値までを削除する
// deleteOldtrace($maxid);