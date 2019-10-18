<?php
echo $this->Flash->render();
echo $this->Html->tag('li',$this->Form->button($this->request->data['Label']['name'], array('bind' => 'click.editLabel')) 
		. $this->Html->image('icon-remove', array('bind' => 'click.removeLabel'))
		. $this->Html->image('print', array('class' => 'print', 'bind' => 'click.printLabel')),
		array('id' => "li-{$this->request->data['Label']['id']}"));
