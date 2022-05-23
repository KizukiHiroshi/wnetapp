@extends('layouts.app')

<?php
$title = '>アラート';
?>
@section('title', $title )

@section('menu')
@endsection

@section('content')
<div style="text-align: center">
    <!-- 完了メッセージ -->
    <?php if(!isset($success)) {$success = '';} ?>
    @if ($success !== '')
    <div class="alert alert-success">
        <strong>{{ $success }}</strong>
    </div>
    @endif
    <!-- エラーメッセージ -->
    <?php if(!isset($errormsg)) {$errormsg = '';} ?>
    @if ($errormsg !== '')
    <div class="alert alert-danger">
        <strong>{{ $errormsg }}</strong>
    </div>
    @endif
    @include ('layouts/components/wnet2020_logo', ['size' => 360])
</div>
@endsection
