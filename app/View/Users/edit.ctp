<div class="users form">
    <?php echo $this->Form->create('User'); ?>
    <fieldset>
<?php
        echo $this->Html->tag('div', null, array('class'=>'ajaxPull'));
// ============================= START FIELDS FOR AJAX TREE EDIT ADD FORM
?>
        <legend><?php echo __('Edit User'); ?></legend>
</div>
        <?php
        echo $this->Form->input('id');
        echo $this->Html->tag('div', null, array('class'=>'ajaxPull'));
// ============================= START FIELDS FOR AJAX TREE EDIT ADD FORM
        echo $this->Form->input('folder', array(
            'options' => array(
                        1 => 'Folder'
                    ),
            'value' => 0,
            'empty' => false,
            'type' => 'checkbox'
        ));
		echo 'HEY!';
        echo $this->Form->input('email');
        echo $this->Form->input('first_name');
        echo $this->Form->input('last_name');
        echo $this->Form->input('active', array(
            'options' => array(
                        0 => 'Inactive',
                        1 => 'Active'
                    ),
            'value' => 1,
            'empty' => false
        ));
        echo $this->Form->input('username');
        echo $this->Form->input('role', array('options' => $roles, 'empty' => true));
        echo $this->Form->input('parent_id', array('options' => $parent_ids));
		
		echo $this->Html->div('nonFolder', NULL);
			$this->FgHtml->setSelected($user_selected);// insure current access is checked
			echo '<p id="UserAccess" class="toggle">User Group Access</p>';
			echo '<div class="UserAccess access hide">';
				echo $this->FgHtml->recursiveTree('checkbox', $accessibleUsers, $this->Session->read('Auth.User.UserRoots'));
			echo '</div>';

			$this->FgHtml->setSelected($catalog_selected);// insure current access is checked
			echo '<p id="CatalogAccess" class="toggle">Catalog Group Access</p>';
			echo '<div class="CatalogAccess access hide">';
				$this->FgHtml->recursiveTree('checkbox', $accessibleCatalogs, $this->Session->read('Auth.User.CatalogRoots'));
			echo '</div>';
		echo '</div>';

	echo '</div>';
        ?>
    </fieldset>
    <?php echo $this->Form->end(__('Submit')); ?>
</div>
