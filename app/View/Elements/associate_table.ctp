<?php
/* Abstract factory for creating data tables or,
 * alternative data-set representations (like a series of sections for 
 * phone screens that would be too small for a table).
 * 
 * $associate must be a helper of type AssociatesTableHelper
*/
$this->start('css');
	echo $this->associate->loadCss();
$this->end();

$this->start('script');
	echo $this->associate->loadJs();
$this->end();

$this->start('table');
	echo $this->associate->startTable();
	echo $this->associate->headerRow();
	echo $this->associate->dataRows();
	echo $this->associate->toolRow();
	echo $this->associate->endTable();
$this->end('table');