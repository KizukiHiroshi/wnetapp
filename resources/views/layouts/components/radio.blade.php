<?php
if (!isset($name)) {$name = 'radioname';}
if (!isset($inline)) {$inline = '';}
if ($inline !== '') {$inline =' form-check-inline';}
if (!isset($margin)) {$margin = '';}
$buttoncnt = 1;
?>
@foreach ($buttons as $button)
    <div class="form-check {{ $inline }} {{ $margin }}">
        <?php
        if (!isset($button['checked'])) {$button['checked'] = '';}
        $checked = ($button['checked'] == '') ? '' : 'checked';
        if (!isset($button['disabled'])) {$button['disabled'] = '';}
        $disabled = ($button['disabled'] == '') ? '' : 'disabled';
        if (!isset($button['id'])) {$button['id'] ='id'.strval($buttoncnt);}
        $buttoncnt += 1;
        if (!isset($button['value'])) {$button['value'] = $button['id'];}
        if (!isset($button['label'])) {$button['label'] = $button['id'];}
        ?>
        <input class="form-check-input" type="radio" name="{{ $name }}" id="{{ $button['id'] }}" 
            value="{{ $button['value'] }}" {{ $checked }} {{ $disabled }}>
        @if ($button['label'] != '')
        <label class="form-check-label" for="{{ $button['id'] }}">
            {{ $button['label'] }}
        </label>
        @endif
    </div>
@endforeach
