<?php  //使い方
  // 'name'         => 'shellcd'    //selectの名前
  // 'selects'      => [内部の値 => ['group' => グループ名, 'value' => 表示名]],   //配列本体
  // 'selected'     => $selected,   //選択された内部の値
  // 'selectmark'   => $selectmark, //選択された項目に付ける目印
  // 'withnoselect' => 'before',    //項目'選択しない'の表示=>'before','after' or ''
  // 'size'         => 'w-100 '     //大きさ指定
  // 'readonly'     => '' or 'readonly="readonly"' //大きさ指定
  if (!isset($name)){$name = '';}
  if (!isset($selects)){$selects = [];}
  if (!isset($selected)){$selected = '';}
  if (!isset($selectmark)){$selectmark = '';}
  if (!isset($withnoselect)){$withnoselect = '';}
  if (!isset($size)){$size = '';}
  if (!isset($readonly)){$readonly = '';}
  $disabled = $readonly !== '' ? 'disabled' : '';
?>

<div>
    <select name="{{ $name }}" required class="form-control form-control-sm m-2 {{ $size }}">
        @if ($withnoselect == 'before')
        <option value="">選択しない</option>
        @endif
        <?php $tempgroup = 'dammytempgroup'; // ダミーのグループ ?>
        @foreach ($selects as $key => $selectvalue)
        @if ($selectvalue['group'] !== $tempgroup)   {{-- 新しいグループ名を表示 --}}
        <optgroup label={{ $selectvalue['group'] }}>
        <?php $tempgroup = $selectvalue['group']; ?>
        @endif
        <?php $selectable = $key == $selected ? 'selected' : $disabled; ?>
        <?php $mark = $key == $selected ? $selectmark : ''; ?>
        <option value={{ $key }} {{ $selectable }}>{{ $selectvalue['value'] }} {{ $mark }}</option>
        @endforeach
        @if ($withnoselect === 'after')
        <option value="">選択しない</option>
        @endif
    </select>
</div>


