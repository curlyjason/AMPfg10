<?php
$this->start('css');
echo $this->FgHtml->css('ampfg_grain');
echo $this->FgHtml->css('status');
$this->end();

$this->start('script');
echo $this->FgHtml->script('grain');
echo $this->FgHtml->script('order');
echo $this->FgHtml->script('status');
echo $this->FgHtml->script('warehouse');
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