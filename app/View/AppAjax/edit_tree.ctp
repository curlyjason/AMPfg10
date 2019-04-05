<?php
//This page determines the edit or view only output pattern
//Based upon the $lock indicating that someone else is editing the requested node
//$lock will either be 0 or the id of the user editing
//if $lock is 0, editing is allowed

$this->start('editTree');//start of editTree block

echo $this->Session->flash();
echo $this->Html->div('treeEdit', NULL);//start of treeEdit div
if (isset($editTree)) {
    
    $treeType = ($lock) ? 'plain' : $controller.'Edit';
    if(!$lock){
        $this->start('script');
        echo $this->Html->script(array('editTree', 'jquery.form'));
        $this->end();
    }
    $this->FgHtml->recursiveTree($treeType, $editTree, $rootNodes);
    $this->append($controller.'Edit', '<p id="result"></p>');
    echo $this->fetch($treeType);
}
echo $this->element('tree_edit_form', array('renderNode'));
echo $this->element('edit_tree_tools');
echo $this->element('ajax_saving_notice');
echo '</div>'; //end of treeEdit div

$this->end();//end of editTree block
?>

