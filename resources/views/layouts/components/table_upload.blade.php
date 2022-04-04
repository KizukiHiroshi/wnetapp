
<form id="table_upload" method="post" action="" enctype="multipart/form-data">
    @csrf
    <div class="m-2">
        <div>
            <p>{{ $tgtuploadfile }}を選択してください</p>
        </div>
        <div>
            <input type="file" name="upload_file" accept="{{ $tgtuploadfile }}"/>
        </div>
        <?php if ($mode == 'upload_check') {
            $disabled_checkbutton = null;
            $disabled_actionbutton = 'disabled';
        } elseif ($mode == 'upload_action')  {
            $disabled_checkbutton = 'disabled';
            $disabled_actionbutton = null;
        } ?>
        @include('layouts/components/button', [
                'margin'    => 'm-2',
                'value'     => '送信内容確認',
                'color'     => 'info',
                'disabled'  => $disabled_checkbutton,
                'formaction'=> '/table/'.$tablename.'/csvupload_check',
            ])
        @include('layouts/components/button', [
                'margin'    => 'm-2',
                'value'     => '送信内容登録',
                'color'     => 'info',
                'disabled'  => $disabled_actionbutton,
                'formaction'=> '/table/'.$tablename.'/csvupload_action',
            ])
    </div>
</form>
