<?php
$this->start('css');
    echo $this->FgHtml->css('ampfg_grain');
    echo $this->FgHtml->css('ampfg_forms');
$this->end();

$this->start('script');
    echo $this->FgHtml->script('grain');
    echo $this->FgHtml->script('form');
    echo $this->FgHtml->script('formUser');
$this->end();

    echo $this->element('replenishment_header_grain', array(
        'grain' => $editGrain,
        'editAccess' => ($this->Session->read('Auth.User.access') == 'Manager' || $this->Session->read('Auth.User.id') == $grainId),
        'heading' => "Vendor"
    ));
?>