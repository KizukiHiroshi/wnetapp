<div class="m-2">
<form id="modelzone" method="get">
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
                'selects'   => $modelselects,
                'selected'  => $selectedtable,
                'selectmark'  => '★選択中',
            ])
        </tr>
    </table>
</form>
{{-- 検索条件 --}}
@if ($selectedtable != '')
<form id="table_search" method="post" action="/table/{{ $tablename }}">
@csrf
    <div class="m-2">
        @include('layouts/components/table_search', [
            'cardcolumnsprop'   => $cardcolumnsprop,
            'foreignselects'    => $foreignselects,
        ])
    </div>
</form>
@endif
</div>