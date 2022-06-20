<?php  //使い方
  // 'name'         => $name        //selectの名前
  // 'selects'      => $selects,    //配列本体 $key=内部の値,$value=表示する名前
  // 'selected'     => $selected,   //選択された内部の値
  // 'selectmark'   => $selectmark, //選択された項目に付ける目印
  // 'withnoselect' => 'before',    //項目'選択しない'の表示=>'before','after' or ''
  // 'size'         => 'w-100 '     //大きさ指定
  // 'readonly'     => '' or 'readonly="readonly"'
   
  if (!isset($name)){$name = '';}
  if (!isset($selects)){$selects = [];}
  if (!isset($selected)){$selected = '';}
  if (!isset($selectmark)){$selectmark = '';}
  if (!isset($withnoselect)){$withnoselect = '';}
  if (!isset($size)){$size = '';}
  if (!isset($readonly)){$readonly = '';}
  $disabled = $readonly !== '' ? 'disabled' : '';
  $required = $withnoselect == '' ? 'required' : '';
?>

<div>
    <select name="{{ $name }}" {{ $required }} class="form-control form-control-sm" {{ $size }}">
        @if ($withnoselect === 'before')
        <option value="">選択しない</option>
        @endif
        @foreach ($selects as $key => $value)
        <?php $selectable = ($key == $selected || $value == $selected)? 'selected' : ''; ?>
        <?php $mark = $key == $selected ? $selectmark : ''; ?>
        <option value={{ $key }} {{ $selectable }} {{ $disabled }}>{{ $value }}{{ $mark }}</option>
        @endforeach
        @if ($withnoselect === 'after')
        <option value="">選択しない</option>
        @endif   
    </select>
</div>


