<?php
//$chars = 3;
$chars = strlen($_POST[0]);
$date = date('r', time());
echo "A notification $chars characters long was received on $date";