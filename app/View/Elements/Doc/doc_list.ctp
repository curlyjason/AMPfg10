<div class="docList">
	<?php
		if (empty($this->request->data['Document'])) {
			echo $this->Html->tag('h2', 'Documents - none uploaded');
		}	else {
			echo $this->Html->tag('h2', 'Documents');
	?>
			<dl>
	<?php
			foreach ($this->request->data['Document'] as $document) {
				echo $this->Html->tag('dt', $document['title']);
				echo $this->Html->tag('dd', $this->FgHtml->markdown($document['instructions']));
			}
	?>
			</dl>
	<?php
		}
	?>
</div>
