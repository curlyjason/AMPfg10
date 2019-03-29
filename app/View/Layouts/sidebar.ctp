<?php

//this is a layout
$this->extend('base');

$this->start('script');
echo $this->FgHtml->script('trees');
$this->end();

$this->start('contentCore');

echo $this->FgHtml->div('sidebar', NULL);
echo $this->FgHtml->div('hide overlay', NULL);
echo $this->FgHtml->para('overlayWarning', 'click to refresh sidebar');
echo '</div>'; //closing div overlay
echo $this->fetch('sidebar');
echo '</div>'; //closing div sidebar

echo $this->FgHtml->div('view', NULL);
echo $this->Session->flash();
echo $this->Session->flash('auth');
//1. Common/manage_tree_object (fetch of editTree)
echo $this->fetch('editTree'); // in case we're editing a tree
//echo $this->fetch('editTree'); // in case we're editing grain
echo $this->fetch('content'); // whatever else there may be
echo $this->element('install_timer', array(
    'load' => true,
    'timerParams' => $timerParams
));
echo '</div>'; //closing div view

$this->end(); //end of contentCore block
?>
