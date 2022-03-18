
<form id="table_upload" method="post" action="" enctype="multipart/form-data">
    @csrf
    <div class="m-2">
        <div>
            <p>{{ $tgtuploadfile }}を選択してください</p>
        </div>
        <div>
            <input type="file" name="upload_file" accept="{{ $tgtuploadfile }}"/>
        </div>
        @include('layouts/components/button', [
            'margin'    => 'm-2',
            'value'     => '送信内容確認',
            'color'     => 'info',
            'formaction'=> '/table/'.$tablename.'/csvupload_check',
        ])
        @include('layouts/components/button', [
            'margin'    => 'm-2',
            'value'     => '送信内容登録',
            'color'     => 'info',
            'formaction'=> '/table/'.$tablename.'/csvupload_action',
        ])
    </div>
</form>
