<?php
echo $this->start('script');
echo $this->FgHtml->script('documents.js');
echo $this->end('script');

echo $this->FgHtml->div('referencesAddress hide', NULL, array('bind' => 'validate.validateReferencesAddress'));
	echo '<fieldset id="optionalFields">';
	
		echo $this->FgForm->input('Order.reference_approval', array('type' => 'hidden'));
		echo $this->FgForm->input('Order.order_reference', array('label' => 'Order Reference #', 'maxlength' => 28));
		echo $this->FgForm->input('Order.note', array('label' => 'Order Note'));
		echo $this->element('Doc/document_table', array('hideDoneButton' => TRUE));
		
	echo '</fieldset>';	
	echo $this->element('next_reference');
echo '</div>';
