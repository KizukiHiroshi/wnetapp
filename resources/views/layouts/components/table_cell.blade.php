@if ($name!='id' && substr($name,-3)!='_id')   {{-- idは表示しない --}}
    <?php 
    // 数値は右揃えにする
    if (strpos($type,'int') !== false || $type == 'decimal') {
        $stylecontent = 'text-align:right';
        $style = 'style='.$stylecontent;
    } else {
        $style ='';
    }
    // 作成日・更新者等の情報は小さな文字にする★未完
    if (substr($columnname, -3)=='_at' || substr($columnname, -3)=='_on' || substr($columnname, -3)=='_by') 
        {$is_small = true;} else {$is_small = false;}
    ?>

    <{{ $tag }} {{ $style }}>
        @if($is_small) <small> @endif
        @if($tag == 'th')   {{-- タイトル部にソート機能をつける --}}
        <a href="/table/{{ $tablename }}?newsort={{ $sortcolumn }}">{{ $value }}</a>
        @else
        {{ $value }}
        @endif
        @if($is_small) </small> @endif
    </{{ $tag }}>
@endif
