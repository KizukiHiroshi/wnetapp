<?php
if (!isset($name)) {$name = 'radioname';}
if (!isset($inline)) {$inline = '';}
if ($inline !== '') {$inline =' form-check-inline';}
$buttoncnt = 1;
?>
@foreach ($buttons as $button)
<?php
$checked = (!isset($button['checked'])||$button['checked']=='') ? '' : 'checked';
$disabled = (!isset($button['disabled'])||$button['disabled']=='') ? '' : 'disabled';
if (!isset($button['id'])) {$button['id'] ='id'.strval($buttoncnt);}
$buttoncnt += 1;
if (!isset($button['value'])) {$button['value'] = $button['id'];}
if (!isset($button['label'])) {$button['label'] = $button['id'];}
?>
<div class="form-check {{ $inline }}">
    <input class="form-check-input" type="radio" name="{{ $name }}" id="{{ $button['id'] }}" 
        value="{{ $button['value'] }}" {{ $checked }} {{ $disabled }}>
    @if ($button['label'] != '')
    <label class="form-check-label" for="{{ $button['id'] }}">
        {{ $button['label'] }}
    </label>
    @endif
</div>
@endforeach