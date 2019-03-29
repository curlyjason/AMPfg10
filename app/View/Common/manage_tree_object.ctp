<?php

//=========================
// Common/manage_tree_object.ctp
//=========================
//
//============================================================
// SIDEBAR CONTEXT SWITCHER
//============================================================

if (preg_match('/^edit_user/', $this->request->params['action'])) { //if this test passes, we're on the user tree or grain
    if ($this->Session->read('Auth.User.role') == 'Admins Manager' || $this->Session->read('Auth.User.role') == 'Staff Manager') {
        $this->start('sidebarTools');
        echo $this->FgForm->button('New Customer', array(
            'type' => 'button',
            'id' => 'newCustomer',
			'bind' => 'click.newCustomer'
        ));
        $this->end();
    }

//} elseif ($this->request->params['action'] == 'edit_catalog') {
//    $this->extend('/AppAjax/edit_tree'); //this creates an editing version of tree
//    $this->start('script');
//    echo $this->FgHtml->script('formCatalog');
//    $this->end();
}

//============================================================
// SETUP SIDEBAR
//============================================================

if (!isset($vendorGrain)) {
    $this->start('sidebar');
    echo $this->element('sidebar_tree', array('controller' => $controller, 'tree' => $tree, 'rootNodes' => $rootNodes));
    $this->end(); //end sidebar block
}
////============================================================
// STYLE AND SCRIPT BLOCKS
//============================================================

$this->start('css');
echo $this->FgHtml->css('ajax');
echo $this->FgHtml->css('ampfg_forms');
$this->end();

$this->start('script');
echo $this->FgHtml->script('form');
$this->end();

//============================================================
// CONTEXT SWITCHER
//============================================================

if (isset($userEditFlag) && $userEditFlag) {
    $this->extend('/AppAjax/edit_tree'); //this creates an editing version of tree
    $this->start('script');
    echo $this->Html->script('formUser');
    $this->end();
} elseif (isset($catalogEditFlag) && $catalogEditFlag) {
    $this->extend('/AppAjax/edit_tree'); //this creates an editing version of tree
    $this->start('script');
    echo $this->Html->script('formCatalog');
    echo $this->Html->script('location');
    $this->end();
	$this->start('css');
	echo $this->Html->css('location');
	$this->end();
} elseif (isset($editGrain)) {
    $this->extend('/AppAjax/edit_grain'); //this create a User grain page
    $this->start('script');
    echo $this->Html->script('formUser');
    $this->end();
} elseif (isset($catalogGrain)) {
    $this->start('script'); //this create a Catalog grain page
    echo $this->Html->script('formCatalog');
    echo $this->Html->script('location');
    $this->end();
	$this->start('css');
	echo $this->Html->css('location');
	$this->end();
} elseif (isset($shopItems)) {
    $this->start('css'); //this creates a stubbed shopping page
    echo $this->Html->css('shopping');
    $this->end();
    echo $this->element('store');
} elseif (isset($vendorGrain)) { //this creates a vendor grain page
    $this->start('css');
        echo $this->Html->css('ampfg_grain');
    $this->end();

    $this->start('script');
        echo $this->Html->script('grain');
        echo $this->Html->script('formUser');
    $this->end();

    $this->start('sidebar');
    echo $this->FgForm->newRequestButton(array('text' => 'New Vendor'));
    $this->end();
    echo $this->element('address_vendor_display', array(
        'grain' => $vendorGrain,
        'editAccess' => ($this->Session->read('Auth.User.access') == 'Manager'),
        'heading' => "Vendor"
    ));
} else {
    //Using cake's debug message styling to provide instructional content to users
    echo $this->FgHtml->para('cake-debug', 'Select an item in the sidebar to work with here.');
}
?>
