<?php   //使い方
    //@controller
    // $numset = array(
    //      'name'  => $name,   //任意で唯一の名前
    //      'step'  => '5',     //変化の単位
    //      'min'   => '0',     //最小値
    //      'max'   => '100',   //最大値
    //      );


    if (!isset($step)) {$step = '1';}
    if (!isset($min)) {$min = '0';}
    if (!isset($max)) {$max = '100';}

?>

<input type="button" class="btn btn-outline-primary col-2" name ="num_minus" value ="ー" onclick="decrement<?php echo $this->escape($name); ?>val()"/>
<input type="button" class="btn btn-outline-primary col-2" name ="num_plus" value ="＋" onclick="increment<?php echo $this->escape($name); ?>val()"/>

<script>
function decrement<?php echo $this->escape($name); ?>val() {
var <?php echo $this->escape($name); ?>val=document.getElementById("<?php echo $this->escape($name); ?>");
<?php echo $this->escape($name); ?>val.value=(<?php echo $this->escape($name); ?>val.value==""?0:
parseInt(<?php echo $this->escape($name); ?>val.value)-<?php echo $this->escape($step); ?><<?php echo $this->escape($min); ?>?<?php echo $this->escape($min); ?>:
parseInt(<?php echo $this->escape($name); ?>val.value)-<?php echo $this->escape($step); ?>)}
function increment<?php echo $this->escape($name); ?>val() {
var <?php echo $this->escape($name); ?>val=document.getElementById("<?php echo $this->escape($name); ?>");
<?php echo $this->escape($name); ?>val.value=(<?php echo $this->escape($name); ?>val.value==""?0:
parseInt(<?php echo $this->escape($name); ?>val.value)+<?php echo $this->escape($step); ?>><?php echo $this->escape($max); ?>?<?php echo $this->escape($max); ?>:
parseInt(<?php echo $this->escape($name); ?>val.value)+<?php echo $this->escape($step); ?>)}
</script>