@extends('layouts.app')

<?php $title = '>alert'; ?>
@section('title', $title )

@section('menu')
@endsection

@section('content')
<div style="text-align: center">
    <!-- 完了メッセージ -->
    <?php if(!isset($success)){$success = '';} ?>
    @if ($success !== '')
    <div class="alert alert-success">
        <strong>{{ $success }}</strong>
    </div>
    @endif
    <!-- エラーメッセージ -->
    <?php if(!isset($danger)){$danger = '';} ?>
    @if ($danger !== '')
    <div class="alert alert-danger">
        <strong>{{ $danger }}</strong>
    </div>
    @endif
    @include ('layouts/components/wnet2020_logo', ['size' => 360])
</div>
@endsection
