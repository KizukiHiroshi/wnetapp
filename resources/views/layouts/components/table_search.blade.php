<table class="table table-hover table-sm table-responsive">
    <tr>
    <td width="25%">
        @include('layouts/components/button', [
            'value'     => '検索実行',
        ])
    </td>
    <td><div class="pt-1">検索条件</div></td>
    <td>
        <div class="pt-1">
        @include('layouts/components/radio', [
            'name'      => 'search_sum',
            'buttons'   => [
                [
                    'id'        => 'no_sum',
                    'label'     => '計',
                    'checked'   => 'checked'
                ],
            ],
        ])
        </div>           
    </td>
    </tr>
    @foreach ($cardcolumnsprop as $columnname => $prop)
    @if ($columnname != 'id' && substr($columnname,-3)!='_id' && substr($columnname,-7)!='_id_2nd')
    <?php
    $oldvalue = array_key_exists($columnname, $oldinput) ? $oldinput[$columnname] : '';
    $biginvalue = array_key_exists('bigin_'.$columnname, $oldinput) ? $oldinput['bigin_'.$columnname] : '';
    $endvalue = array_key_exists('end_'.$columnname, $oldinput) ? $oldinput['end_'.$columnname] : '';
    ?>
    <tr>           
        <th>{{ $prop['comment'] }}</th>
        <td>
        @if (substr($columnname,-10)=='_reference')
            <?php $foreignid = substr($columnname,0,-10); ?>
            @include('layouts/components/select', [
                'name'      => $foreignid,
                'selects'   => $foreignselects[$columnname],
                'selected'  => $oldvalue,
                'withnoselect' => 'before',
                'required'  => 'false'
                ])
        @else
            @switch($prop['type'])
            @case('string')
                <div class="input-group">
                <input type="text" name="{{ $columnname }}" value="{{ $oldvalue }}" class="h-75">
                </div>
                @break
            @case('boolean')
                <div class="input-group">
                    <?php $checked = $oldvalue == 1 ? 'checked' : '';?>
                    @include('layouts/components/checkbox', [
                        'name'      => $columnname,
                        'value'     => '1',
                        'label'     => '',
                        'checked'   => $checked,
                    ])
                </div>   
                @break
            @case('date')
                <div class="input-group">
                <input type="date" name="{{ 'bigin_'.$columnname }}" value="{{ $biginvalue }}" class="w-50">
                <div class="pl-2 pr-2">～</div>
                <input type="date" name="{{ 'end_'.$columnname }}" value="{{ $endvalue }}" class="w-50">
                </div>    
                @break
            @default
                <div class="input-group">
                <?php // 数値は右揃えにする
                    if (strpos($prop['type'],'int') !== false || $prop['type'] == 'decimal') {
                        $style = 'text-right';
                        $wide = 'w-25';
                    } else {
                        $style ='';
                        $wide = 'w-50';
                    }
                ?>
                <input type="text" name="{{ 'bigin_'.$columnname }}" value="{{ $biginvalue }}" class="h-75 {{ $wide }} {{ $style }}">
                <div class="pl-2 pr-2">～</div>
                <input type="text" name="{{ 'end_'.$columnname }}" value="{{ $endvalue }}" class="h-75 {{ $wide }} {{ $style }}">
                @if ( $columnname == 'deleted_at')
                    <?php $trashed = array_key_exists('trashed', $oldinput) ? $oldinput['trashed'] : 'no';?>
                    <div>
                    @include('layouts/components/radio', [
                        'name'      => 'trashed',
                        'inline'    => 'inline',
                        'margin'    => 'mt-2',
                        'buttons'   => [
                            ['id'=>'no',    'label'=>'通常 ',   'checked'=> ($trashed == 'no' ? 'checked' : '') ],
                            ['id'=>'with',  'label'=>'含 ',     'checked'=> ($trashed == 'with' ? 'checked' : '')],
                            ['id'=>'only',  'label'=>'済のみ ', 'checked'=> ($trashed == 'only' ? 'checked' : '')],
                        ],
                    ])
                    </div>              
                @endif
                </div>
            @endswitch
        @endif
        </td>
        <td>
            @include('layouts/components/radio', [
                'name'      => 'search_sum',
                'label'     => '',
                'buttons'   => [
                    [
                        'id'    => $columnname.'_sum',
                        'label' => '',
                    ],
                ],
            ])               
        </td>
    </tr>
    @endif
    @endforeach
</table>
