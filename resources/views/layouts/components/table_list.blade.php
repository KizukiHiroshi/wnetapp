
<table class="table table-striped table-hover table-sm table-responsive m-0">
    {{-- 制御部 --}}
    @if ($rows)
    <tr>
    <div class="d-flex justify-content-end ml-2 mt-0">
        <div class="p-0">{{ $rows->appends(['tablename' => $tablename])->links() }}</div>
        <div class="p-3">全{{ $rows->total() }}件</div>
        @if ($withbutton) 
        <?php $href = '/table/'.$tablename.'/download_csv'; ?>
        <div class="pt-0 mr-2">
        <form id="table_download" action="{{ $href }}" method="get">
        @include('layouts/components/button', [
            'margin'    => 'mt-0 p-1',
            'value'     => $withdownload['value']]) 
        </form>
        </div>
        @endif
     </div>
    </tr>
    @endif
    {{-- 表題部 --}}
    <tr>
    @if ($withbutton) <th></th> @endif
    @foreach ($columnsprop as $columnname => $prop)
        <?php $sortcolumn = $prop['tablename'].'.'.$prop['sortcolumn'] ?>
        @include ('layouts/components/table_cell', [
            'tag'           => 'th',
            'name'          => $columnname,
            'type'          => $prop['type'],
            'tablename'     => $tablename,
            'sortcolumn'    => $sortcolumn,
            'value'         => $prop['comment']
        ])
    @endforeach
    </tr>
    {{-- リスト部 --}}
    @if ($rows)
    @foreach($rows AS $row)
    <tr>
        @if ($withbutton) 
        <?php $href = '/table/'.$tablename.'/'.$row[$withbutton['buttonvalue']].'/show'; ?>
        <td>
            <form id="table_edit" action="{{ $href }}" method="get">
            @include('layouts/components/button', [
                'margin'    => 'pt-0 pb-0 m-0',
                'value'     => $withbutton['value']
            ]) 
            </form>
        </td>
        @endif
        @foreach ($columnsprop as $columnname => $prop)
            @include ('layouts/components/table_cell', [
                'tag'   => 'td',
                'name'  => $columnname,
                'type'  => $prop['type'],
                'value' => $row->$columnname,
            ])
        @endforeach
    </tr>
    @endforeach
    @endif
</table>

