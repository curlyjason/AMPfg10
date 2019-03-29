<?php
//$this->FgHtml->ddd($replenishmentList);
$arrayIndex = array_keys($data);
$displayStatus = $data[$arrayIndex[0]]['Replenishment']['status'] . ' Replenishment';
$count = count($replenishmentList[$group]);
$toggler = Inflector::classify($displayStatus);
echo '<div>';
echo $this->FgHtml->tag('h3', "$displayStatus (<span class='count'>$count</span>)", array(
    'id' => $toggler, //"WatchReplenishment",
    'class' => 'grainDisplay toggle'
));
$index = 0;
foreach ($data as $fileIndex => $order) {
    echo $this->Warehouse->pullWrapper($order, $params, $index++, "$toggler", 'Replenishment');
}
echo '</div>';

?>