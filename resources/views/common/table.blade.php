@extends('layouts.app')

<?php
$title = '>テーブル管理';
if ($tablecomment !== '') {$title .='>['.$tablecomment.']';}
if (isset($row)) {$title .='>行の';
    if ($mode == 'show') {$title .='表示';}
    if ($mode == 'edit') {$title .='編集';}
    if ($mode == 'create') {$title .='新規登録';}
} else {
    if (strpos($mode,'csv') !== false) {$title .='>一括登録';}
}
?>
@section('title', $title )

@section('menu')
<div class="col-md-3 border-bottom border-primary">
    <?php if(!isset($mode)) {$mode = '';} ?>
    @if ($modelselect)
        <!-- テーブル選択 -->
        <?php if(!isset($selectedtable)) {$selectedtable = '';} ?>
        @include('layouts/components/table_selectmodel', [
            'selects'   => $modelselect,
            'selected'  => $selectedtable,
        ])
        <!-- 検索条件入力 -->
        @if ($mode == 'list')
        @include('layouts/components/table_search', [
            'searchinput'       => $searchinput,
            'searcherrors'      => $searcherrors, 
            'cardcolumnsprop'   => $cardcolumnsprop,
            'foreignselects'    => $foreignselects,
        ])
        @endif
    @else
        @include ('layouts/components/wnet2020_logo', ['size' => 120])
    @endif
</div>
@endsection

@section('content')
<div class="col-md-9 border-bottom border-primary">
    <!-- 完了メッセージ -->
    <?php if(!isset($success)) {$success = '';} ?>
    @if ($success !== '')
    <div class="alert alert-success">
        <strong>{{ $success }}</strong>
    </div>
    @endif
    <!-- エラーメッセージ -->
    <?php if(!isset($danger)) {$danger = '';} ?>
    @if ($danger !== '')
    <div class="alert alert-danger">
        <strong>{{ $danger }}</strong>
    </div>
    @endif
    <!-- バリデーションからのメッセージ -->
    @if ($errors->any())
    <div class="alert alert-danger">
        <ul>
        @foreach ($errors->all() as $error)
            <li>{{ $error }}</li>
        @endforeach
        </ul>
    </div>
    @endif
    <!-- コンテンツ実体部分 -->
    @if (isset($mode))
        @if ($mode == 'list')
            <!-- リスト表示 -->
            @include ('layouts/components/table_list', [
                'tablename'     => $tablename,
                'rows'          => $rows,
                'columnsprop'   => $columnsprop,
                'withbutton'    => $withbutton,
            ])
        @elseif (isset($row))
            <!-- カード表示 -->
            @include ('layouts/components/table_card', [
                'mode'          => $mode,
                'tablename'     => $tablename,
                'row'           => $row,
                'cardcolumnsprop'   => $cardcolumnsprop,
            ])
        @elseif (strpos($mode,'csv') !== false)
            <!-- CSVアップロード画面 -->
            @include ('layouts/components/table_upload', [
                'mode'          => $mode,
                'tablename'     => $tablename,
                'csverrors'     => $csverrors,
            ])
        @endif
    @else
        <!-- コンテンツが無い場合はロゴ表示 -->
        @include ('layouts/components/wnet2020_logo', ['size' => 360])
    @endif
</div>
@endsection
