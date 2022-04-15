<div class="m-2">
<form method="get" id="modelzone">
    <table class="table table-hover table-sm table-responsive">
        <tr>
            {{-- テーブル選択 --}}
            <div class="d-flex justify-content-between ml-2 mt-1">
                <p class="mt-1 mb-0">テーブル</p>
                @include('layouts/components/button', [
                    'value'     => '選択',
                    'formaction'=> '/table/'.$tablename,
                ])
                @include('layouts/components/button', [
                    'value'     => '一括登録',
                    'color'     => 'info',
                    'formaction'=> '/table/csvupload/csvselect',
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
                'selects'   => $modelselects,
                'selected'  => $selectedtable,
                'selectmark'  => '★選択中',
            ])
        </tr>
        <tr>
            {{-- ボタン --}}
            @if ($selectedtable != '')
            @csrf
            <div class="d-flex justify-content-between">
                @include('layouts/components/button', [
                    'form'      => 'table_search',
                    'value'     => '検索実行',
                    'formmethod'=> 'post',
                    'formaction'=> '/table/'.$tablename,
                ])
                @include('layouts/components/button', [
                    'value'     => '新規登録',
                    'color'     => 'warning',
                    'formmethod'=> 'get',
                    'formaction'=> '/table/'.$tablename.'/create',
                ])
            </div>
            @endif
        </tr>
    </table>
</form>
{{-- 検索条件 --}}
@if ($selectedtable != '')
<div class="m-2">
    @include('layouts/components/table_search', [
        'cardcolumnsprop'  => $columnsprop,
    ])
</div>
@endif
</div>