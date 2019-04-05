<?php
$this->start('css');
echo $this->Html->css('ampfg_grain');
echo $this->Html->css('status');
$this->end();

$this->start('script');
echo $this->Html->script('grain');
echo $this->Html->script('order');
echo $this->Html->script('status');
echo $this->Html->script('warehouse');
$this->end();

if (!isset($replenishmentList)) {
    $replenishmentList = array();
}

if (isset($replenishmentList)) {

//    debug(($replenishmentList));
    echo $this->element('replenishment_grain', array(
        'data' => $replenishmentList,
        'class' => 'orderHeaderGrain',
        'params' => array('group' => 'approved'),
	'alias' => 'Replenishment'
    ));
}
?>
</div>