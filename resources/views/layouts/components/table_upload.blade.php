<?php
if ($mode=='csvcheck'||$mode=='csvselect')  {
    $disabled_checkbutton = null;
    $disabled_savebutton = 'disabled';
} elseif ($mode == 'csvsave')  {
    $disabled_checkbutton = 'disabled';
    $disabled_savebutton = null;
}
?>
<form id="table_upload" method="post" action="" enctype="multipart/form-data">
    @csrf
    <input type="hidden" name="tablename" value="{{ $tablename }}">
    <div class="m-2">
        <div>
            <p>{{ $tgtuploadfile }}を選択してください</p>
        </div>
        <div class="d-flex">
            @if ($mode=='csvselect' || $mode=='csvcheck')
            <div>
                <input type="file" name="upload_file" accept="{{ $tgtuploadfile }}"/>
            </div>
            @elseif ($mode=='csvsave')
            <div>
                <p>{{ $tgtuploadfile }}がアップロードされました　</p>
            </div>
            @endif
            <div class="mt-2">
                @include('layouts/components/checkbox', [
                    'name'      => 'is_insertonly',
                    'value'     => '1',
                    'label'     => '新規のみ',
                    'checked'   => '',
                ])
            </div>
            <div class="mt-2 pl-5">
                @include('layouts/components/checkbox', [
                    'name'      => 'is_allowforeigninsert',
                    'value'     => '1',
                    'label'     => '参照元の更新を許可する',
                    'checked'   => '',
                ])
            </div>
        </div>
    </div>
    <div>
        @include('layouts/components/button', [
            'margin'    => 'm-2',
            'value'     => '1.送信内容確認',
            'color'     => 'info',
            'disabled'  => $disabled_checkbutton,
            'formaction'=> '/table/csvupload/csvcheck',
        ])
        @include('layouts/components/button', [
            'margin'    => 'm-2',
            'value'     => '2.送信内容登録',
            'color'     => 'info',
            'disabled'  => $disabled_savebutton,
            'formaction'=> '/table/csvupload/csvsave',
        ])
        @include('layouts/components/button', [
            'value'     => '戻る',
            'color'     => 'secondary',
            'formaction'=> '/table/csvupload/csvcancel',
        ])
    </div>
    <div>
        @foreach($csverrors as $csverror)
            {{ $csverror }}<br>
        @endforeach
    </div>
</form>
