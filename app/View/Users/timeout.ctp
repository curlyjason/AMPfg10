<?php
$this->extend('login');

$this->start('timeout');
echo $this->fgHtml->tag('h2','Your session has timed out.', array('class' => 'timeout'));
$this->end();
?>