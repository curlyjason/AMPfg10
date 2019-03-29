<?php
//$this->FgHtml->ddd($pullList);
$arrayIndex = array_keys($data);
$displayStatus = $data[$arrayIndex[0]]['Order']['status'];
$count = count($pullList[$group]);
echo '<div>';
echo $this->FgHtml->tag('h3', "$displayStatus (<span class='count'>$count</span>)", array(
    'id' => "Watch$displayStatus",
    'class' => 'grainDisplay toggle'
));
$index = 0;
foreach ($data as $fileIndex => $order) {
    echo $this->Warehouse->pullWrapper($order, $params, $index++, "Watch$displayStatus");
}
echo '</div>';

?>