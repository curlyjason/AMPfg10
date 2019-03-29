<?php 
$this->start('css');
echo $this->Html->css('simple_form');
$this->end();
echo $this->element('user_password_change_form'); 
?>