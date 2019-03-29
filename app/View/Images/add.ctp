<div class="images form">
<?php echo $this->Form->create('Image', array(
    'type' => 'file'
)); ?>
	<fieldset>
		<legend><?php echo __('Add Image'); ?></legend>
	<?php
		echo $this->Form->input('img_file', array(
            'type' => 'file'
        ));
		echo $this->Form->input('mimetype');
		echo $this->Form->input('filesize');
		echo $this->Form->input('width');
		echo $this->Form->input('height');
		echo $this->Form->input('title');
		echo $this->Form->input('date');
		echo $this->Form->input('category');
		echo $this->Form->input('alt');
		echo $this->Form->input('upload');
	?>
	</fieldset>
<?php echo $this->Form->end(__('Submit')); ?>
</div>
<div class="actions">
	<h3><?php echo __('Actions'); ?></h3>
	<ul>

		<li><?php echo $this->Html->link(__('List Images'), array('action' => 'index')); ?></li>
	</ul>
</div>
