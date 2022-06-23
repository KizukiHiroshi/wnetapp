<?php
session_start();
if(isset($_POST['height'])) {
   $_SESSION['screen_height'] = intval($_POST['height']);
}


if(!isset($_SESSION['screen_height'])) {
?>
<html>
<head>
<script>
function getSize() {
document.getElementById('inp_height').value=screen.height;
document.getElementById('form_size').submit();
}
</script>
</head>
<body onload='getSize()'>
<form method='post' id='form_size'>
<input type='hidden' name='height' id='inp_height'/>
</form>
</body>
</html>

<?php
}

?>
