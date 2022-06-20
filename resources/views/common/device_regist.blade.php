@extends('layouts.app')

<?php $title = '>アクセス機器登録'; ?>
@section('title', $title )

@section('menu')
<div class="col-md-2 d-flex justify-content-sm-center">

</div>
@endsection

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
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
            <div class="card">
                <div class="card-header">{{ __('この機器でwNetにアクセスするのは初めてです') }}</div>

                <div class="card-body">
                    <form method="POST" action="/device/regist">
                        @csrf
                        <div class="form-group row">
                            <label for="name" class="col-md-4 col-form-label text-md-right">今使用している機器に<br>名前を付けてください</label>

                            <div class="col-md-6 mt-2">
                                <input id="name" type="text" class="form-control" name="name" value="{{ old('name') }}" autofocus>
                                @error('name')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                        </div>

                        <div class="form-group row mb-0">
                            <div class="col-md-8 offset-md-4">
                                <button type="submit" class="btn btn-primary">
                                    {{ __('アクセス機器登録') }}
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
