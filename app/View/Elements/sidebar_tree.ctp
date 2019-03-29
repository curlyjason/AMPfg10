<?php

echo $this->fetch('sidebarTools');
echo $this->FgHtml->div('treeSelect', NULL);
$this->FgHtml->recursiveTree($controller . 'Select', $tree, $rootNodes);
echo $this->fetch($controller . 'Select'); //created by the FGHtml Helper
echo '</div>'; //end treeSelect div
?>