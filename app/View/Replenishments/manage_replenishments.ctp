<?php
$this->start('css');
    echo $this->Html->css('ampfg_grain');
    echo $this->Html->css('ampfg_forms');
$this->end();

$this->start('script');
    echo $this->Html->script('grain');
    echo $this->Html->script('form');
    echo $this->Html->script('formUser');
$this->end();

    echo $this->element('replenishment_header_grain', array(
        'grain' => $editGrain,
        'editAccess' => ($this->Session->read('Auth.User.access') == 'Manager' || $this->Session->read('Auth.User.id') == $grainId),
        'heading' => "Vendor"
    ));
?>