<div class="m-2">
<form id="modelzone" method="get">
    <table class="table table-hover table-sm table-responsive">
        <tr>
            {{-- テーブル選択 --}}
            <div class="d-flex justify-content-between ml-2 mt-1">
                <p class="mt-1 mb-0">テーブル</p>
                @include('layouts/components/button', [
                    'value'     => '表示',
                    'formaction'=> '/table/'.$tablename,
                ])
                @include('layouts/components/button', [
                    'value'     => '一括登録',
                    'color'     => 'info',
                    'formaction'=> '/table/csvupload/csvselect',
                ])
                @include('layouts/components/button', [
                    'value'     => '新規登録',
                    'color'     => 'warning',
                    'formaction'=> '/table/'.$tablename.'/create',
                ])
                @include('layouts/components/button', [
                    'value'     => '戻る',
                    'color'     => 'secondary',
                    'formaction'=> '/menu',
                ])
            </div>
        </tr>
        <tr>
            @include('layouts/components/select_withgroup', [
                'name'      => 'tablename',
                'selects'   => $modelselect,
                'selected'  => $selectedtable,
                'selectmark'  => '★選択中',
            ])
        </tr>
    </table>
</form>
</div>