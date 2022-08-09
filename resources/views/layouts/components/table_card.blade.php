<form id="table_card" action="" method="">
    @csrf
    {{-- ボタン --}}
    @if ($mode == 'create')
        @include('layouts/components/button', [
            'value'     => '登録実行',
            'color'     => 'warning',
            'margin'    => 'm-2',
            'formmethod'=> 'post',
            'formaction'=> '/table/'.$tablename.'/store',
        ])
    @elseif ($mode == 'show')
        @include('layouts/components/button', [
            'value'     => '編集',
            'color'     => 'success',
            'margin'    => 'm-2',
            'formmethod'=> 'get',
            'href'      => '/table/'.$tablename.'/'.$row->id.'/edit',
        ])
        @if (!isset($row->deleted_at))
            @include('layouts/components/button_alert', [
                'value'     => '削除',
                'color'     => 'danger',
                'margin'    => 'm-2',
                'formmethod'=> 'get',
                'alert'     => 'delete_alert',
                'href'      => '/table/'.$tablename.'/'.$row->id.'/delete',
            ])
        @else
            @include('layouts/components/button_alert', [
                'value'     => '完全削除',
                'color'     => 'danger',
                'margin'    => 'm-2',
                'formmethod'=> 'get',
                'alert'     => 'delete_alert',
                'href'      => '/table/'.$tablename.'/'.$row->id.'/forcedelete',       
            ])
            @include('layouts/components/button', [
                'value'     => '復活',
                'color'     => 'warning',
                'formmethod'=> 'get',
                'href'      => '/table/'.$tablename.'/'.$row->id.'/restore',       
            ])
        @endif
    @elseif ($mode == 'edit')
        @include('layouts/components/button', [
            'value'     => '更新',
            'color'     => 'success',
            'margin'    => 'm-2',
            'formmethod'=> 'post',
            'formaction'=> '/table/'.$tablename.'/'.$row->id.'/update',
        ])
        @include('layouts/components/button', [
            'value'     => '新規として登録',
            'color'     => 'warning',
            'margin'    => 'm-2',
            'formmethod'=> 'post',
            'formaction'=> '/table/'.$tablename.'/store',
        ])
    @endif
    <?php if ($mode == 'show' || $mode == 'create') {
        $href = '/table/'.$tablename.'?tablename='.$tablename.'&page='.$page;
    } else {
        $href = '/table/'.$tablename.'/'.$row->id.'/show';
    } ?>
    @include('layouts/components/button', [
        'value'     => '戻る',
        'color'     => 'secondary',
        'margin'    => 'm-2',
        'formmethod'=> 'get',
        'href'      => $href,
    ])
    <input type="hidden" name="page" value={{ $page }}>
    <input type="hidden" name="id" value={{ $row->id }}>
    <table class="table table-striped table-hover table-sm table-responsive">
        @foreach ($cardcolumnsprop as $columnname => $prop)
        @if ($columnname !== 'id' && substr($columnname,-3) !== '_id' && substr($columnname,-7) !== '_id_2nd')
        <?php
        // システム制御カラムはリードオンリーにする
            if (substr($columnname, -3) == '_at' || substr($columnname, -3) == '_by') {
                $readonly = 'readonly="readonly"';
            } else {
                $readonly ='';
            }
        // mode==showでは全てリードオンリーにする 
        if ($mode == 'show') { $readonly ='readonly="readonly"';} 
        // 作成日・更新者等の情報は小さな文字にする
        if (substr($columnname, -3) == '_at' || substr($columnname, -3) == '_on' || substr($columnname, -3) == '_by') 
            {$is_small = true;} else {$is_small = false;}
        ?>
        <tr>           
            <th width="10%">
            @if($is_small) <small> @endif
                {{ $prop['comment'] }}
            @if($is_small) </small> @endif
            </th>
            <td>
            @if($is_small) <small> @endif
            @if (substr($columnname,-13) == 'opt_reference')
                <?php $foreignid = substr($columnname,0,-10); ?>
                <?php $selectname = str_replace('_2nd','', $columnname); ?>
                @include('layouts/components/select', [
                    'name'      => $foreignid,
                    'selects'   => $optionselects[$selectname],
                    'selected'  => $row->$foreignid,
                    'readonly'  => $readonly,
                ])
            @elseif (substr($columnname,-10) == '_reference')
                <?php $foreignid = substr($columnname,0,-10); ?>
                <?php $selectname = str_replace('_2nd','', $columnname); ?>
                @include('layouts/components/select', [
                    'name'      => $foreignid,
                    'selects'   => $foreignselects[$selectname],
                    'selected'  => $row->$foreignid,
                    'readonly'  => $readonly,
                ])
            @else
                @switch($prop['type'])
                @case('string')
                    <div class="input-group">
                    @if ($prop['length'] < 40)
                        <input type="text" size="{{ $prop['length'] }}" 
                            name="{{ $columnname }}" value="{{ $mode=='create' ? old($columnname) : $row->$columnname }}" {{ $readonly }}>
                    @else
                        <?php $rowcnt = min(intval($prop['length'] / 40 + 1), 8); ?>
                        <textarea cols="40" rows="{{ $rowcnt }}" 
                            name="{{ $columnname }}" {{ $readonly }}>{{ $mode=='create' ? old($columnname) : $row->$columnname }}</textarea>
                    @endif   
                    </div>
                    @break
                @case('boolean')
                    <div class="input-group">
                        <?php $checked = $row->$columnname == '1' ? 'checked' : '';?>
                        <?php $disabled = $readonly !== '' ? 'disabled="disabled"' : ''; ?>
                        <input type="hidden" name="{{ $columnname }}" value="0">
                        @include('layouts/components/checkbox', [
                            'name'      => $columnname,
                            'value'     => '1',
                            'label'     => '',
                            'checked'   => $checked,
                            'disabled'  => $disabled,
                        ])
                    </div>   
                    @break
                @case('date')
                    <div class="input-group">
                        <input type="date" name="{{ $columnname }}" value="{{ $mode=='create' ? old($columnname) : $row->$columnname }}" {{ $readonly }}>
                    </div>    
                    @break
                @default
                    <div class="input-group">
                    <?php // 数値は右揃えにする
                        if (strpos($prop['type'],'int') !== false || $prop['type'] == 'decimal') {
                            $style = 'style="text-align:right"';
                        } else {
                            $style ='';
                        }
                    ?>
                    <input {{ $style }} type="text" 
                        name="{{ $columnname }}" value="{{ $mode=='create' ? old($columnname) : $row->$columnname }}" {{ $readonly }}>       
                    </div>
                @endswitch
            @endif
            @if($is_small) </small> @endif
            </td>  
        </tr>
        @endif
        @endforeach
    </table>
</form>