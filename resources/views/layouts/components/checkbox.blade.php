<?php
if (!isset($name)) {$name = 'checkbox';}
if (!isset($checked)) {$checked = '';} if ($checked !== '') {$checked = 'checked';}
if (!isset($disabled)) {$disabled = '';} if ($disabled !== '') {$disabled = 'disabled';}
if (!isset($id)) {$id ='checkboxid';}
if (!isset($value)) {$value = $id;}
if (!isset($label)) {$label = $id;}
?>
<div class="form-check">
    <input class="form-check-input" type="checkbox" name="{{ $name }}" id="{{ $id }}" 
        value="{{ $value }}" {{ $checked }} {{ $disabled }}>
    @if ($label !== '')
    <label class="form-check-label" for="{{$id}}">
        {{ $label }}
    </label>
    @endif
</div>