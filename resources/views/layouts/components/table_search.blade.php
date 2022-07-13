<form id="table_search" method="post" action="/table/{{ $tablename }}">
@csrf
<div class="m-2">
    @if ($searcherrors)
    <div class="text-danger">
        @foreach($searcherrors as $columnname => $error)
            {{ $error }}<br>
        @endforeach
    </div>
    @endif
    @if ($cardcolumnsprop)
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
            @if ($columnname !== 'id' 
                && substr($columnname,-3) !== '_id' 
                && substr($columnname,-7) !== '_id_2nd' 
                && substr($columnname,-4) !== '_opt')
            <?php
            $realcolumnname = substr($columnname, -10) == '_reference' ? substr($columnname,0,-10) : $columnname;
            $searchvalue = array_key_exists($realcolumnname, $searchinput) ? $searchinput[$realcolumnname] : '';
            $biginvalue = array_key_exists('bigin_'.$realcolumnname, $searchinput) ? $searchinput['bigin_'.$realcolumnname] : '';
            $endvalue = array_key_exists('end_'.$realcolumnname, $searchinput) ? $searchinput['end_'.$realcolumnname] : '';
            ?>
            <tr>           
                <th>{{ $prop['comment'] }}</th>
                <td>
                @if (substr($columnname, -13) == '_id_reference')
                <?php $foreignid = substr($columnname, 0, -10); ?>
                <?php $selectname = str_replace('_2nd','', $columnname); ?>
                    @include('layouts/components/select', [
                        'name'      => 'search_'.$foreignid,
                        'selects'   => $foreignselects[$selectname],
                        'selected'  => $searchvalue,
                        'withnoselect' => 'before',
                        'required'  => 'false'
                        ])
                @elseif (substr($columnname, -14) == '_opt_reference')
                <?php $optionid = substr($columnname, 0, -10); ?>
                <?php $selectname = $columnname; ?>
                    @include('layouts/components/select', [
                        'name'      => 'search_'.$optionid,
                        'selects'   => $optionselects[$selectname],
                        'selected'  => $searchvalue,
                        'withnoselect' => 'before',
                        'required'  => 'false'
                        ])
                @else
                    @switch($prop['type'])
                    @case('string')
                        <div class="input-group">
                        <input type="text" name="{{ 'search_'.$columnname }}" value="{{ $searchvalue }}" class="h-75">
                        </div>
                        @break
                    @case('boolean')
                        <div class="input-group">
                            <?php $checked = $searchvalue == 1 ? 'checked' : '';?>
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
                        <input type="date" name="{{ 'search_'.'bigin_'.$columnname }}" style="font-size:0.75em" value="{{ $biginvalue }}" class="w-30">
                        <div class="pl-2 pr-2">～</div>
                        <input type="date" name="{{ 'search_'.'end_'.$columnname }}" style="font-size:0.75em" value="{{ $endvalue }}" class="w-30">
                        </div>    
                        @break
                    @case('datetime')
                        <div class="input-group">
                        <input type="date" name="{{ 'search_'.'bigin_'.$columnname }}" style="font-size:0.75em" value="{{ $biginvalue }}" class="w-30">
                        <div class="pl-2 pr-2">～</div>
                        <input type="date" name="{{ 'search_'.'end_'.$columnname }}" style="font-size:0.75em" value="{{ $endvalue }}" class="w-30">
                        </div>    
                        @if ($columnname == 'deleted_at')
                            <?php $trashed = array_key_exists('trashed', $searchinput) ? $searchinput['trashed'] : 'no';?>
                            <div>
                            @include('layouts/components/radio', [
                                'name'      => 'search_'.'trashed',
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
                        <input type="text" name="{{ 'search_'.'bigin_'.$columnname }}" value="{{ $biginvalue }}" class="h-75 {{ $wide }} {{ $style }}">
                        <div class="pl-2 pr-2">～</div>
                        <input type="text" name="{{ 'search_'.'end_'.$columnname }}" value="{{ $endvalue }}" class="h-75 {{ $wide }} {{ $style }}">
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
    @endif
</div>
</form>