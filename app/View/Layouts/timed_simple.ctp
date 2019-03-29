<?php
    $this->extend('simple');
echo $this->element('install_timer', array(
    'load' => true,
    'timerParams' => $timerParams
));
echo $this->fetch('content');
?>