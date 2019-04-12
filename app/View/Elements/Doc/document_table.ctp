<?php
	/**
	 * Prepare vars for table helper
	 */
	$hideDoneButton = (isset($hideDoneButton)) ? $hideDoneButton : FALSE;
	if($hideDoneButton){
		$done = '';
		$openForm = '';
		$closeForm = '';
	} else {
		$done = $this->Form->button('Done', array('type' => 'submit', 'class' => 'docDoneButton regular', 'bind' => 'click.docDone'));
		$openForm = $this->Form->create('Document', array('type' => 'file', 'method' => 'post', 'controller' => 'documents', 'action' => 'save'));
		$closeForm = '</form>';
	}
	$headers = array('Document', 'Tool');
	$rows = array();
	$rows[] = array(
		$done
			. $this->Form->input('Order.id', array('type' => 'hidden'))
			. $this->Form->input('Order.order_number', array('type' => 'hidden')),
		array($this->Form->button('New', array('bind' => 'click.newDocInput', 'class' => 'green regular', 'type' => 'button')), array('id' => 'doc-tools'))
	);
	// table vars finished
	
	echo $openForm;
	echo $this->Html->tag('table', NULL, array('id' => 'Documents', 'change' => 'false'));
	echo $this->Html->tableHeaders($headers);
	if (!empty($this->request->data['Document'])) {
		$c = count($this->request->data['Document']);
		$i = 0;
		while ($i < $c) {
			echo($this->element('Doc/new_doc', array('index' => $i++)));
		}
	}
	echo $this->Html->tableCells($rows);
	echo '</table>';
	echo $closeForm;
