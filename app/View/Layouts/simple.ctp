<?php
$this->extend('base');
$this->start('contentCore');
    echo $this->fetch('content');
$this->end(); 
?>
