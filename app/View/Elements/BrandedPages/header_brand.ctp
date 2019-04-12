<?php
if ($variant === 'login') {
	
	$greeting = 'Login';
	
} else {
	
	$greeting = "Welcome: {$this->Session->read('Auth.User.name')}" 
				. " - Your role: {$this->Session->read('Auth.User.role')}";

}

	echo $this->Html->image($this->BrandedPages->logo());
	
	echo $this->Html->tag('h1', 
			$this->Html->link(
				"{$this->BrandedPages->brand()} - $greeting"
				, 'http://www.ampprinting.com'
			)
		);
