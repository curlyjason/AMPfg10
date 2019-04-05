<?php
echo $this->Html->div('catalogPermissionForm', NULL);
echo $this->Session->flash();
echo $this->FgForm->create('User', array('class' => 'grainVersion'));
echo $this->FgForm->input('User.id', array('type' => 'hidden'));

$keys = array_flip($catalog_selected);

echo '<div class="ajaxPull ajaxEditPull" id="Catalog">';
		$this->FgHtml->setSelected($catalog_selected);// insure current access is checked
		echo '<p id="CatalogAccess" class="toggle">Catalog Group Access</p>';
		echo '<div class="CatalogAccess access">';
			$this->FgHtml->recursiveTree('checkbox', $accessibleCatalogs, $this->Session->read('Auth.User.CatalogRoots'));
		echo '</div>';
echo '</div>'; // close the catalog checkbox wrapper div

echo $this->FgForm->button('Cancel', array(
    'type' => 'button',
    'bind' => 'click.basicCancelButton'
));
echo $this->FgForm->button('Submit', array(
    'type' => 'submit',
    'class' => 'submitButton'
));
echo $this->FgForm->end();
echo '</div>';
?>