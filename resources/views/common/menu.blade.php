@extends('layouts.app')
<style>
    .pagination { font-size:10pt; }
    .pagination li { display:inline-block; }
    tr th a:link { color: black; }
    tr th a:visited { color: black; }
    tr th a:hover { color: black; }
    tr th a:active { color: black; }
</style>

<?php $title = '>menu'; ?>
@section('title', $title )

@section('menu')
<div class="col-md-2 d-flex justify-content-sm-center">
    @include('layouts/components/button', [
        'value' => 'テーブル管理',
        'href'  => '/table',
        'margin'=> 'm-2',
    ]) 
</div>
@endsection

@section('content')
<div class="col-md-10">
    @if (isset($mode))
    @else
        @include ('layouts/components/wnet2020_logo', ['size' => 360])
    @endif

</div>
@endsection
