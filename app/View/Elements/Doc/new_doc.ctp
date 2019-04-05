<?php
// If there is data to populate the form
if (isset($index)) {
	$this->request->data['Document']["t$index"] = $this->request->data['Document'][$index];
	// show the Edit label
	// hide the inputs
	// show the file link and instructions
	$editLabelClass = 'toggle';
	$editDivClass = "editDoct$index hide";
	$path = 'doc/' . $this->request->data['Document'][$index]['dir'] . '/' . $this->request->data['Document'][$index]['img_file'];
	$display = $this->Html->link(
				$this->request->data['Document'][$index]['title'], 
				"sendFile/$path", 
				array('title' => "Open {$this->request->data['Document'][$index]['title']}"))
			. $this->Html->para('Inst', $this->FgHtml->markdown($this->request->data['Document'][$index]['instructions']));
	
// if there is no data to populate the form
} else {
	// This will allow random row creation/deletion with no id conflicts
	$index = str_replace('.', '', microtime(TRUE));
	
	// hide the edit link
	// show the inputs
	// there is no link or instructions
	$editLabelClass = 'hide';
	$editDivClass = "editDoct$index";
	$display = '';
	
}

$index = "t$index";

?>
<tr id="row<?php echo $index; ?>">
	<td class="document-row">

		<?php
			// Inputs: id, img_file, title, instructions

		echo $this->Html->div($editDivClass, NULL);
			echo $this->FgForm->input("Document.$index.id", array('type' => 'hidden'));
			echo $this->FgForm->input("Document.$index.dir", array('type' => 'hidden'));
			echo $this->FgForm->input("Document.$index.user_id", array('type' => 'hidden'));
			echo $this->FgForm->input("Document.$index.order_id", array('type' => 'hidden'));
			echo $this->FgForm->input("Document.$index.img_file", array(
				'type' => 'file',
				'bind' => 'change.captureName',
				'required' => FALSE,
				));
			echo $this->FgForm->input("Document.$index.title", array('bind' => 'change.indicateChange'));
			echo $this->FgForm->input("Document.$index.instructions", array(
				'type' => 'textarea',
				'bind' => 'change.indicateChange',
				'style' => 'width: 71%;',
				));
		echo '</div>';
		echo $display;
		
		?>
	</td> 
	<td class="invoiceRemove">
		<?php echo $this->Html->para($editLabelClass, 'Edit', array('id' => "editDoc$index")); ?>
		<span id="remove-<?php echo $index; ?>" class="remove">
			<?php echo $this->Html->image('icon-remove.gif', array('alt' => 'Remove', 'bind' => 'click.deleteRow')); ?>
		</span>
	</td>
</tr>