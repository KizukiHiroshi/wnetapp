<form id="table_search">
    <table class="table table-hover table-sm table-responsive">
        @foreach ($cardcolumnsprop as $columnname => $prop)
        @if ($columnname != 'id' && substr($columnname,-3)!='_id')
        <tr>           
            <th>
                {{ $prop['comment'] }}</th>
            <td>
            @if (substr($columnname,-10)=='_reference')
                <?php $foreignid = substr($columnname,0,-10).'_id'; ?>
                @include('layouts/components/select', [
                    'name'      => $foreignid,
                    'selects'   => $foreignselects[$columnname],
                    'readonly'  => $readonly,
                ])
            @else
                @switch($prop['type'])
                @case('string')
                    <div class="input-group">
                    <input type="text" name="{{ $columnname }}" value="">
                    </div>
                    @break
                @case('boolean')
                    <div class="input-group">
                        <input type="hidden" name="{{ $columnname }}" value="0">
                        <input type="checkbox" name="{{ $columnname }}" value="1">
                    </div>   
                    @break
                @case('date')
                    <div class="input-group">
                        <input type="date" name="{{ $columnname }}" value="" >
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
                        name="{{ $columnname }}" value="" >       
                    </div>
                @endswitch
            @endif
            </td>  
        </tr>
        @endif
        @endforeach
    </table>
</form>