<?php
$toChildUrl = '';
$newSiblingUrl = '';
$newChildUrl = '';
$editItemUrl = '';
$deleteItemUrl = '';
$listItemUrl = '';
$itemType = $this->Html->tag('span', 'item', array('class' => 'itemType'));
?>

<div id="treeEditTools" class="toolPallet all">
    <p class='label'></p>
    <p class='close'>X</p>
    <?php
	$test = $this->request->params['action'] . $this->request->params['controller'];
    echo $this->FgHtml->link('Demote to child', $toChildUrl, array(
        'class' => 'edit_toChild'
    ));
    echo $this->FgHtml->link('Create a new sibling', $newSiblingUrl, array(
        'class' => 'edit_newSibling'
    ));
    echo $this->FgHtml->link('Create a new child', $newChildUrl, array(
        'class' => 'edit_newChild'
    ));
	if (stristr($test, 'catalog')) {
		echo $this->FgHtml->link("Edit this $itemType", $editItemUrl, array(
			'class' => 'edit_saveEditForm',
			'escape' => FALSE
		));
		echo $this->FgHtml->link("Inactivate this $itemType", $deleteItemUrl, array(
			'class' => 'edit_saveEditForm',
			'escape' => FALSE
		));
		echo $this->FgHtml->link('Inactivate associated ITEM & linked catalog elements', $deleteItemUrl, array(
			'class' => 'edit_deleteItem'
		));
		if ($this->Session->read('Auth.User.group') == 'Admins') {
			echo $this->FgHtml->link('List Item history', $listItemUrl, array('class' => 'list_itemHistory'));
		}		
	} elseif (stristr($test, 'user')) {
		echo $this->FgHtml->link('Edit this user', $editItemUrl, array(
			'class' => 'edit_saveEditForm'
		));
		echo $this->FgHtml->link('Inactivate this user', $deleteItemUrl, array(
			'class' => 'edit_deleteItem'
		));
	} else {
		echo 'unknown action discovered, please contact developers.';
	}
    ?>
</div>