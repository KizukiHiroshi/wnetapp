<?php
if (!isset($type))  {$type ='submit';}
if (!isset($color)) {$color ='primary';}
if (!isset($margin)) {$margin ='m-1';}
if (!isset($size))  {$size ='sm';}
if (!isset($value)) {$value ='error';}
if (!isset($disabled)) {$disabled ='';}
$name = isset($name) ? 'name='.$name : '';
$form = isset($form) ? 'form='.$form : '';
$formmethod = isset($formmethod) ? 'formmethod='.$formmethod : '';
$formaction = isset($formaction) ? 'formaction='.$formaction : '';

?>
@if (isset($href))
<a href="{{ $href }}">
    <button type="button"
     class="btn btn-{{ $color }} btn-{{ $size }} {{ $margin }}" {{ $disabled }}>{{ $value }}</button>
</a>
@else
    @if (isset($buttonvalue))
    <input type="hidden" name="buttonvalue" value="{{ $buttonvalue }}" {{ $disabled }}>
    @endif
    <input type="{{$type}}" {{ $name }} {{ $form }} {{ $formmethod }} {{ $formaction }}
     class="btn btn-{{ $color }} btn-{{ $size }} {{ $margin }}" value="{{ $value }}" {{ $disabled }}>
@endif

