<?php
/**
 * $this->associate is a helper of type AssociateTableHelper
 * It can accept an iterator which returns Entity objects as its constructor argument
 * $customers is an object of type CustomerCollection, a sub-class of EntityCollection
 */

$states = $item_states = array('active', 'inactive');

//loop on the state
foreach ($states as $state) {
	echo $this->Html->tag('div', NULL, array('class' => $state . ' Customers'));
	echo $this->Html->tag('h1', Inflector::humanize($state) . ' Customers');
	
	//loop on each customer
	foreach ($customers->filter($state) as $customer) {
		echo $this->Html->tag('div', NULL, array('class' => 'Customer'));
		echo $this->Html->tag('h2', $customer->username);
		
		//processes the customer's Items once for each possible state
		foreach ($item_states as $item_state) {
			echo $this->Html->tag('div', NULL, array('class' => $item_state . ' Items'));
			
			//put the title in the view block, controlled (later) by total row count for the view block
			$this->start('table');
				echo $this->Html->tag('h4', Inflector::humanize($item_state) . ' Items');
			$this->end('table');
			
			// Initialize the helper class with a new set of Entities
			$this->associate->setCollection($customer->ItemCollection->filter($item_state));
			// the element uses the helper class and sends output into the 'table' view block
			$this->element('associate_table'); 
			
			//output the table ONLY if there are rows
			if($this->associate->getRowCount() > 0){
				echo $this->fetch('table');
			}
			
			//clear the table view block
			$this->assign('table', '');	
			
			echo '</div>'; //close items div
		}
		echo '</div>'; //close Customer div
	}
	echo '</div>'; //close Customers Div
}
