@extends('layouts.app')
<style>
    .pagination { font-size:10pt; }
    .pagination li { display:inline-block; }
    tr th a:link { color: black; }
    tr th a:visited { color: black; }
    tr th a:hover { color: black; }
    tr th a:active { color: black; }
</style>

<?php
$title = '>テーブル管理';
if ($tablecomment!='') {$title .='>['.$tablecomment.']';}
if (isset($row)) {$title .='>行の';
    if ($mode == 'show') {$title .='表示';}
    if ($mode == 'edit') {$title .='編集';}
    if ($mode == 'create') {$title .='新規登録';}
} else {
    if ($mode == 'upload') {$title .='>一括登録';}
}
?>
@section('title', $title )

@section('menu')
<div class="col-md-2 border-bottom border-primary">
    @if ($modelselects)
        <?php if(!isset($selectedtable)) {$selectedtable = '';} ?>
        @include('layouts/components/table_selectmodel', [
            'selects'   => $modelselects,
            'selected'  => $selectedtable,
        ])
    @else
        @include ('layouts/components/wnet2020_logo', ['size' => 120])
    @endif
</div>
@endsection

@section('content')
<div class="col-md-10 border-bottom border-primary">
    <?php if(!isset($success)) {$success = '';} ?>
    @if ($success!='')
    <div class="alert alert-success">
        <strong>{{ $success }}</strong>
    </div>
    @endif
    <?php if(!isset($errormsg)) {$errormsg = '';} ?>
    @if ($errormsg!='')
    <div class="alert alert-danger">
        <strong>{{ $errormsg }}</strong>
    </div>
    @endif
    @if ($errors->any())
    <div class="alert alert-danger">
        <ul>
        @foreach ($errors->all() as $error)
            <li>{{ $error }}</li>
        @endforeach
        </ul>
    </div>
    @endif
    @if (isset($mode))
        @if ($mode=='list')
            @include ('layouts/components/table_list', [
                'tablename'     => $tablename,
                'rows'          => $rows,
                'columnsprop'   => $columnsprop,
                'withbutton'    => $withbutton,
            ])
        @elseif (isset($row))
            @include ('layouts/components/table_card', [
                'mode'          => $mode,
                'tablename'     => $tablename,
                'row'           => $row,
                'cardcolumnsprop'   => $cardcolumnsprop,
            ])
        @elseif ($mode=='upload_check'||$mode=='upload_action')
            @include ('layouts/components/table_upload', [
                'mode'          => $mode,
                'tablename'     => $tablename,
            ])
        @endif
    @else
        @include ('layouts/components/wnet2020_logo', ['size' => 360])
    @endif
</div>
@endsection
