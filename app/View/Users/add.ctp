<div class="users form">
    <?php echo $this->Form->create('User'); ?>
    <fieldset>
        <?php
        // ============================= START FIELDS FOR AJAX TREE EDIT ADD FORM
        echo $this->Html->tag('div', null, array('class' => 'ajaxPull ajaxEditPull'));
        echo $this->FgHtml->tag('legend', __('Add User'), array(
            'id' => 'treeFormLegend'
        ));
        ?>
        <?php
        echo $this->Form->input('active', array(
            'options' => array(
                0 => 'Inactive',
                1 => 'Active'
            ),
            'type' => 'radio',
            'legend' => false,
            'value' => 1,
            'empty' => false
        ));
        echo $this->Form->input('folder', array(
            'options' => array(
                1 => 'Folder'
            ),
            'value' => 0,
            'empty' => false,
            'type' => 'checkbox',
            'legend' => FALSE
        ));
        echo $this->Form->input('first_name', array('label' => 'First Name'));
        echo $this->Form->input('last_name', array('label' => 'Last Name'));
        echo $this->Form->input('username', array('label' => 'Email'));
        echo '</div>';
        // ============================= END FIELDS FOR AJAX TREE EDIT ADD FORM
        echo $this->Form->input('parent_id', array('options' => $parent_ids, 'empty' => true));
        // ============================= OPEN ADVANCED DIV
        echo $this->Html->para('advanced', 'Advanced');
        echo $this->Html->tag('div', null, array('class' => 'advanced'));

        // ============================= START MORE FIELDS FOR AJAX TREE EDIT ADD FORM
        echo $this->Html->tag('div', null, array('class' => 'ajaxPull ajaxEditPull'));
        echo $this->Form->input('role', array('options' => $roles, 'empty' => true));
        echo '</div>'; //close for PULL and EDITPULL
        // ============================= END FIELDS FOR AJAX TREE EDIT ADD FORM

        echo $this->Form->input('password');

        echo '</div>'; // end of div.advanced
        // ============================= CLOSE ADVANCED DIV
        // ============================= START MORE FIELDS FOR AJAX TREE EDIT ADD FORM
        echo $this->Html->tag('div', null, array('class' => 'ajaxPull ajaxEditPull nonFolder'));

			$this->FgHtml->setSelected($user_selected); // insure current access is checked
			echo '<p id="UserAccess" class="toggle">User Group Access</p>';
			echo '<div class="UserAccess access hide">';
			echo $this->FgHtml->recursiveTree('checkbox', $accessibleUsers, $this->Session->read('Auth.User.UserRoots'));
			echo '</div>';


			$this->FgHtml->setSelected($catalog_selected); // insure current access is checked
			echo '<p id="CatalogAccess" class="toggle">Catalog Group Access</p>';
			echo '<div class="CatalogAccess access hide">';
			$this->FgHtml->recursiveTree('checkbox', $accessibleCatalogs, $this->Session->read('Auth.User.CatalogRoots'));
			echo '</div>';
		echo '</div>';
        // ============================= END FIELDS FOR AJAX TREE EDIT ADD FORM
        ?>
    </fieldset>
    <?php echo $this->Form->end(__('Submit')); ?>
</div>