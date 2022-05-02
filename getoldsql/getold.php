<?php

// 新DBへのコネクション
function newpdo() {
    $serverName = "mysql:host=ls-4f0794d2cc2d15c5ad409bd5be51c8928d1bcec0.csl92juws7gy.ap-northeast-1.rds.amazonaws.com;
        dbname=wnetdb_test";
    $username = "dbmasteruser";
    $pwd = ":wWoUbfG18Zb]d(h%BV0lj2NDt<.V?u}";
    $newpdo = new PDO($serverName, $username, $pwd);
    return $newpdo;
}

// 旧DBへのコネクション
function oldConn() {
    $serverName = "211.135.254.209,1433\WNET";
    $uid = "sa";
    $pwd = "wise_login_pwd";
    // $dbname = "sqltrace";
    $dbname = "wisedb";
    $dsn = "sqlsrv:server=" . $serverName . ";database=" . $dbname;
    $oldconn = new PDO($dsn, $uid, $pwd);
    return $oldconn;
}

// 取得済みSQLの最新日時を確認する
function getLastCreated() {
    $tsql = "SELECT MAX(created_at) AS lastcreated
        FROM wnetdb_test.oldsqls";
    // TransDBへのコネクション
    $newpdo = newpdo();
    $stmt = $newpdo->query($tsql);
    $lastcreated = '';
    foreach ($stmt as $row) {
        $lastcreated = $row["lastcreated"];
    }
    if ($lastcreated == '') {
        $lastcreated = '2022/04/28 00:00:00';
    }
    return $lastcreated;
}

// SQLの履歴を取得する
// https://note.gosyujin.com/2019/05/21/sqlserver-query-parameter/ 参照
function getOldSql($lastcreated) {
    $tsql = "
    WITH XMLNAMESPACES( 
        DEFAULT 'http://schemas.microsoft.com/sqlserver/2004/07/showplan'
    ) 
    SELECT
        item.query( 
        '
            <parameters>
                {
                    for \$i in ColumnReference
                    return <parameter>{string(\$i/@Column)},{string(\$i/@ParameterCompiledValue)}</parameter>
                }
            </parameters>
        '
        ) AS param
        , text 
        , last_execution_time 
        FROM
        sys.dm_exec_query_stats 
        CROSS APPLY sys.dm_exec_query_plan(plan_handle) 
        CROSS APPLY sys.dm_exec_sql_text(sql_handle)
        OUTER APPLY query_plan.nodes( 
          '/ShowPlanXML/BatchSequence/Batch/Statements/StmtSimple/QueryPlan/ParameterList'
        ) AS T(item) 
        WHERE
        last_execution_time >= :lastcreated 
        ORDER BY
        last_execution_time
        ";

    // 旧DBへのコネクション
    $oldconn = oldConn();
    $stmt = $oldconn->prepare($tsql);
    $stmt->bindParam( ':lastcreated', $lastcreated, PDO::PARAM_STR);
    $stmt->execute();
    $oldsqls = $stmt->fetchall();
    // 旧DBへのコネクション終了
    $oldconn = null;
    return $oldsqls;
}

// oldsqlに登録する
function insertOldsql($oldtracerows) {
    foreach($oldtracerows AS $row) {
        $sqltext = $row["text"];
        $sqltext_value = $row["param"];
        if (is_necessaryraw($sqltext) == false) {   // 不要な行を示す単語の有無をチェック
            $sqltype = 'WASTE';
        } elseif (strpos($sqltext, 'TRIGGER') !== false) {
            $sqltype = 'TRIGGER';
        } elseif (strpos($sqltext, 'PROCEDURE ') !== false) {
            $sqltype = 'PROCEDURE';
        } elseif (strpos($sqltext, 'UPDATE ') !== false || strpos($sqltext, 'update ') !== false ) {
            $sqltype = 'UPDATE';
        } elseif (strpos($sqltext, 'INSERT ') !== false || strpos($sqltext, 'insert ') !== false ) {
            $sqltype = 'INSERT';
        } elseif (strpos($sqltext, 'SELECT ') !== false || strpos($sqltext, 'select ') !== false ) {
            $sqltype = 'SELECT';
        } elseif (strpos($sqltext, 'DELETE ') !== false || strpos($sqltext, 'delete ') !== false ) {
            $sqltype = 'DELETE';
        }
        $created_at = $row["last_execution_time"];
        if ($sqltype=='INSERT' || $sqltype=='UPDATE' || $sqltype=='DELETE' || $sqltype=='PROCEDURE') {
            // newDBへのコネクション
            $newpdo = newpdo();
            $stmt = $newpdo->prepare("INSERT INTO wnetdb_test.oldsqls (
                sqltype, sqltext, sqltext_value, created_at
            ) VALUES (
                :sqltype, :sqltext, :sqltext_value, :created_at
            )");            
            $stmt->bindParam( ':sqltype', $sqltype, PDO::PARAM_STR);
            $stmt->bindParam( ':sqltext', $sqltext, PDO::PARAM_STR);
            $stmt->bindParam( ':sqltext_value', $sqltext_value, PDO::PARAM_STR);
            $stmt->bindParam( ':created_at', $created_at, PDO::PARAM_STR);
            $res = $stmt->execute();
            // newDBへのコネクション終了
            $newpdo = null;
        }
    }
}

function is_necessaryraw($sqltext) {
    $wastewords = [
        'xp_logininfo',
        'sp_ssis_',
        'MntShopStock',
        'sp_verify_',
        'salesmnt',
	    'stockmnt',
	    'dm_exec_query_stats',
	    'maintplan',
	    'msdb',
	    'sp_configure',
	    '[２９：仮発注リスト]',
        '[９９：業務許可]',

    ];
    foreach ($wastewords as $wasteword) {
        if (strpos($sqltext, $wasteword) !== false) {
            return false;
        }
    }
    return true;
}