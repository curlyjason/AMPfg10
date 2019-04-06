<?php 
$row = array($this->Invoice->makeChargeRow($new, $index));
echo "<table>";
echo $this->Html->tableCells($row);
echo "</table>";
echo $this->Html->para(null, json_encode($result));
?>