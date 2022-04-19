<table class="table table-hover table-sm table-responsive">
    <tr>
    <td width="25%">
        @include('layouts/components/button', [
            'value'     => '検索実行',
        ])
    </td>
    <td><div class="pt-2">検索条件</div></td>
    <td>
        <div class="pt-2">
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
    <?php $oldvalue = array_key_exists($columnname, $oldinput) ? $oldinput[$columnname] : '';?>
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
                <input type="text" name="{{ $columnname }}" value="{{ $oldvalue }}">
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
                    <input type="date" name="{{ $columnname }}" value="{{ $oldvalue }}" >
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
                    name="{{ $columnname }}" value="{{ $oldvalue }}" >       
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
