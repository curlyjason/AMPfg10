<!--Element header.ctp-->
<?php

$this->start('header');
echo $this->Html->div('msg', '', array(
    'id' => 'msg'
));
?>

<!-- branded header -->
	<?= $this->BrandedPages->header('general'); ?>
<!-- END branded header -->

<?php
if ($this->layout != 'simple' && $this->layout != 'base') {
	echo $this->element('budget_status');
}
$this->end();

$this->start('AccountTools');
	if($this->Session->read('Auth.User.id')) {
		$logAction = 'logout';
	} else {
		$logAction = 'login';
	}
    echo '<p>'.
	    $this->Html->link(Inflector::classify($logAction),
			array('id' => $logAction, 'controller' => 'users', 'action' => $logAction),
			array('')
	    ).
	 '</p>';    
if ($this->layout != 'base' && $this->layout != 'simple') {
    echo '<p>'.
	    $this->Html->link('Reset Password',
			array('controller' => 'users', 'action' => 'resetPassword'),
			array('')
	    ).
	 '</p>';    
    echo '<p>'.
	    $this->Html->link('Edit my account information',array(
	    'controller' => 'users',
	    'action' => 'edit_userGrain',
	    $this->Session->read('Auth.User.id'),
	    $this->FgHtml->secureHash($this->Session->read('Auth.User.id')),
	    '?' => array('ancestors' => $this->FgHtml->editAccountUrl($this->Session->read('Auth.User.ancestor_list'))))).
	 '</p>';
    echo '<p>'.
	    $this->Html->link('Make this my home page<span></span>',
			array(''),
			array('id' => 'homePref', 'controller' => $this->request->controller, 'action' => $this->request->action, 'escape' => false)
	    ).
	 '</p>';    
}
$this->end();
?>
<!--END Element header.ctp-->
