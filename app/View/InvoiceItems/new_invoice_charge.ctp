<?php 
$row = array($this->Invoice->makeChargeRow($new, $index));
echo "<table>";
echo $this->FgHtml->tableCells($row);
echo "</table>";
echo $this->FgHtml->para(null, json_encode($result));
?>